<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoadWatchSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\LaporanJalan\Models\LaporanJalan;
use Modules\RuasJalan\Models\RuasJalan;
use Tests\TestCase;

class RoadWatchTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoad(): array
    {
        Storage::fake('local');
        $this->seed(RoadWatchSeeder::class);

        return [User::where('role', 'admin')->first(), User::where('role', 'warga')->first()];
    }

    public function test_seed_and_authenticated_pages_and_role_boundaries(): void
    {
        [$admin, $warga] = $this->seedRoad();
        $this->assertDatabaseCount('ruas_jalans', 5);
        $this->assertDatabaseCount('laporan_jalans', 5);
        $this->seed(RoadWatchSeeder::class);
        $this->assertDatabaseCount('laporan_jalans', 5);
        foreach (['/ruas-jalan', '/laporan-jalan', '/laporan-jalan/data'] as $url) {
            $this->get($url)->assertRedirect('/login');
            $this->actingAs($warga)->get($url)->assertOk();
            auth()->forgetGuards();
        }
        $this->actingAs($warga)->get('/admin/users')->assertForbidden();
        $this->post('/ruas-jalan', [])->assertForbidden();
        $this->actingAs($admin)->post('/ruas-jalan', [])->assertSessionHasErrors(['nama', 'lokasi', 'panjang', 'lebar', 'kondisi']);
        $data = ['nama' => 'Jalan Baru', 'lokasi' => 'RW 9', 'panjang' => 200, 'lebar' => 4, 'kondisi' => 'Baik'];
        $this->post('/ruas-jalan', $data)->assertRedirect();
        $road = RuasJalan::where('nama', 'Jalan Baru')->first();
        $this->put('/ruas-jalan/'.$road->id, [...$data, 'nama' => 'Jalan Edit'])->assertRedirect();
        $this->assertDatabaseHas('ruas_jalans', ['id' => $road->id, 'nama' => 'Jalan Edit']);
        $this->delete('/ruas-jalan/'.$road->id)->assertRedirect();
        $this->assertDatabaseMissing('ruas_jalans', ['id' => $road->id]);
        $used = RuasJalan::first();
        $this->delete('/ruas-jalan/'.$used->id)->assertSessionHasErrors('hapus');
    }

    public function test_ajax_upload_replacement_ownership_and_deletion(): void
    {
        [$admin,$warga] = $this->seedRoad();
        $other = User::factory()->create(['role' => 'warga']);
        $this->actingAs($warga)->postJson('/laporan-jalan', [])->assertUnprocessable()->assertJsonValidationErrors(['judul', 'foto']);
        $data = ['ruas_jalan_id' => RuasJalan::first()->id, 'judul' => 'Lubang baru', 'deskripsi' => 'Lubang di dekat sekolah', 'tanggal' => today()->toDateString()];
        $this->postJson('/laporan-jalan', [...$data, 'status' => 'Selesai', 'foto' => UploadedFile::fake()->image('foto.png')])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->postJson('/laporan-jalan', [...$data, 'foto' => UploadedFile::fake()->create('evil.txt', 1, 'text/plain')])->assertUnprocessable()->assertJsonValidationErrors('foto');
        $this->postJson('/laporan-jalan', [...$data, 'foto' => UploadedFile::fake()->image('foto.png')])->assertCreated();
        $row = LaporanJalan::latest('id')->first();
        $old = $row->foto;
        Storage::disk('local')->assertExists($old);
        $this->get('/laporan-jalan/'.$row->id.'/foto')->assertOk();
        $this->actingAs($other)->getJson('/laporan-jalan/data')->assertExactJson([]);
        $this->get('/laporan-jalan/'.$row->id.'/foto')->assertForbidden();
        $this->putJson('/laporan-jalan/'.$row->id, $data)->assertForbidden();
        $this->deleteJson('/laporan-jalan/'.$row->id)->assertForbidden();
        $this->actingAs($warga)->post('/laporan-jalan/'.$row->id, [...$data, '_method' => 'PUT', 'foto' => UploadedFile::fake()->image('baru.jpg')], ['Accept' => 'application/json'])->assertOk();
        Storage::disk('local')->assertMissing($old);
        $new = $row->fresh()->foto;
        Storage::disk('local')->assertExists($new);
        $this->actingAs($admin)->putJson('/laporan-jalan/'.$row->id, [...$data, 'status' => 'Diproses'])->assertOk();
        $this->actingAs($warga)->deleteJson('/laporan-jalan/'.$row->id)->assertForbidden();
        $this->actingAs($admin)->deleteJson('/laporan-jalan/'.$row->id)->assertOk();
        Storage::disk('local')->assertMissing($new);
        $this->assertDatabaseMissing('laporan_jalans', ['id' => $row->id]);
    }

    public function test_registration_cannot_escalate_role(): void
    {
        $this->post('/register', ['name' => 'Warga Baru', 'email' => 'baru@example.test', 'password' => 'RoadWatch123!', 'password_confirmation' => 'RoadWatch123!', 'role' => 'admin'])->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'baru@example.test', 'role' => 'warga']);
    }

    public function test_deleting_account_preserves_reports_for_admin(): void
    {
        [$admin,$warga] = $this->seedRoad();
        $this->actingAs($admin)->delete('/admin/users/'.$warga->id)->assertRedirect();
        $this->assertDatabaseCount('laporan_jalans', 5);
        $this->assertDatabaseMissing('users', ['id' => $warga->id]);
        $this->getJson('/laporan-jalan/data')->assertOk()->assertJsonCount(5);
        $this->assertNull(LaporanJalan::first()->user_id);
    }

    public function test_ajax_rejects_missing_csrf_and_accepts_session_token(): void
    {
        [, $warga] = $this->seedRoad();
        $route = $this->app['router']->getRoutes()->getByName('laporan-jalan.store');
        $route->middleware(AlwaysCheckCsrf::class);
        $this->actingAs($warga)->postJson('/laporan-jalan', [])->assertStatus(419);
        $this->withSession(['_token' => 'roadwatch-csrf-token'])
            ->withHeader('X-CSRF-TOKEN', 'roadwatch-csrf-token')
            ->postJson('/laporan-jalan', [])->assertUnprocessable();
    }
}

class AlwaysCheckCsrf extends ValidateCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
