<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Pendaftaran\Models\Daftar;

function regressionPendaftar(array $overrides = []): array
{
    return [
        'nama' => 'Budi Santoso', 'asal_sekolah' => 'SMA Negeri 1',
        'prodi_tujuan' => 'Informatika', 'email' => 'budi@example.com',
        'no_hp' => '081234567890', ...$overrides,
    ];
}

test('ordinary users cannot mutate or read administrative resources', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user);
    $this->get('/admin/users/create')->assertForbidden();
    $this->get("/admin/users/{$other->id}/edit")->assertForbidden();
    $this->post('/admin/users', [])->assertForbidden();
    $this->put("/admin/users/{$other->id}", [])->assertForbidden();
    $this->delete("/admin/users/{$other->id}")->assertForbidden();
    expect(User::count())->toBe(2);
});

test('unauthenticated json requests cannot access any registration endpoint', function () {
    $record = Daftar::factory()->create();
    $this->getJson('/pendaftaran/data')->assertUnauthorized();
    $this->getJson("/pendaftaran/{$record->id}/edit")->assertUnauthorized();
    $this->postJson('/pendaftaran', [])->assertUnauthorized();
    $this->putJson("/pendaftaran/{$record->id}", [])->assertUnauthorized();
    $this->deleteJson("/pendaftaran/{$record->id}")->assertUnauthorized();
});

test('admin can update another user while keeping or replacing a password then delete them', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();
    $oldHash = $other->password;
    $this->actingAs($admin);
    $input = ['name' => 'Nama Baru', 'email' => $other->email, 'role' => 'admin', 'password' => ''];
    $this->put("/admin/users/{$other->id}", $input)->assertSessionHasNoErrors()->assertRedirect('/admin/users');
    expect($other->fresh()->password)->toBe($oldHash);
    expect($other->fresh()->isAdmin())->toBeTrue();
    $this->put("/admin/users/{$other->id}", [...$input, 'password' => 'password-baru', 'password_confirmation' => 'password-baru'])
        ->assertSessionHasNoErrors();
    expect(Hash::check('password-baru', $other->fresh()->password))->toBeTrue();
    $this->delete("/admin/users/{$other->id}")->assertSessionHas('success');
    $this->assertDatabaseMissing('users', ['id' => $other->id]);
});

test('user validation rejects duplicate email bad role and mismatched passwords', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Duplikat', 'email' => $admin->email, 'role' => 'superadmin',
        'password' => 'password', 'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['email', 'role', 'password']);
});

test('administrative search role filter and pagination preserve query parameters', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(12)->create(['name' => 'Mahasiswa']);
    $this->actingAs($admin)->get('/admin/users?search=Mahasiswa&role=user')
        ->assertInertia(fn (Assert $page) => $page->component('UserManagement::index', false)
            ->has('users.data', 10)->where('users.total', 12)
            ->where('filters.search', 'Mahasiswa')->where('filters.role', 'user')
            ->where('users.next_page_url', fn ($url) => str_contains($url, 'search=Mahasiswa') && str_contains($url, 'role=user')));
});

test('dashboard exposes user totals only to admin', function () {
    Daftar::factory()->count(2)->create();
    $this->actingAs(User::factory()->create())->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->component('dashboard')->where('totalPendaftar', 2)->where('totalUsers', null));
    $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->component('dashboard')->where('totalUsers', 2));
});

test('edit without replacement keeps the uploaded file and original filename', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $this->postJson('/pendaftaran', regressionPendaftar(['berkas' => UploadedFile::fake()->image('asli.jpg')]))->assertOk();
    $record = Daftar::firstOrFail();
    $path = $record->berkas;
    $this->postJson("/pendaftaran/{$record->id}", [...regressionPendaftar(), '_method' => 'PUT', 'prodi_tujuan' => 'Teknik Elektro'])->assertOk();
    expect($record->fresh()->berkas)->toBe($path);
    expect($record->fresh()->nama_berkas)->toBe('asli.jpg');
    Storage::disk('public')->assertExists($path);
    $this->getJson("/pendaftaran/{$record->id}/edit")->assertJsonPath('no_hp', '081234567890')
        ->assertJsonPath('berkas_url', asset('storage/'.$path));
});

test('registration validates file size duplicate email and telephone without creating files', function () {
    Storage::fake('public');
    $record = Daftar::factory()->create();
    $this->actingAs(User::factory()->create())->postJson('/pendaftaran', regressionPendaftar([
        'email' => $record->email, 'no_hp' => 'abcd',
        'berkas' => UploadedFile::fake()->create('besar.pdf', 2049, 'application/pdf'),
    ]))->assertUnprocessable()->assertJsonValidationErrors(['email', 'no_hp', 'berkas']);
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

test('png jpg jpeg and pdf uploads are accepted', function (string $extension) {
    Storage::fake('public');
    $file = $extension === 'pdf'
        ? UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')
        : UploadedFile::fake()->image('bukti.'.$extension);
    $this->actingAs(User::factory()->create())->postJson('/pendaftaran', regressionPendaftar(['berkas' => $file]))->assertOk();
    Storage::disk('public')->assertExists(Daftar::firstOrFail()->berkas);
})->with(['png', 'jpg', 'jpeg', 'pdf']);

test('demo seeding creates usable accounts without fake two factor challenges', function () {
    $this->seed();
    foreach (['admin@example.com', 'user@example.com'] as $email) {
        $user = User::where('email', $email)->firstOrFail();
        expect(Hash::check('password', $user->password))->toBeTrue();
        expect($user->two_factor_secret)->toBeNull();
    }
    $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs(User::where('email', 'admin@example.com')->firstOrFail());
});
