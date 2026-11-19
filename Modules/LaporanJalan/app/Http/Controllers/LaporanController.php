<?php

namespace Modules\LaporanJalan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\LaporanJalan\Http\Requests\LaporanRequest;
use Modules\LaporanJalan\Models\LaporanJalan;
use Modules\RuasJalan\Models\RuasJalan;

class LaporanController extends Controller
{
    private function access(Request $r, LaporanJalan $laporan, bool $write = false): void
    {
        abort_unless($r->user()->isAdmin() || ($laporan->user_id === $r->user()->id && (! $write || $laporan->status === 'Diajukan')), 403);
    }

    public function index()
    {
        return Inertia::render('LaporanJalan::index', ['ruas' => RuasJalan::orderBy('nama')->get(['id', 'nama'])]);
    }

    public function data(Request $r)
    {
        return response()->json(LaporanJalan::with(['ruas:id,nama', 'pelapor:id,name'])->when(! $r->user()->isAdmin(), fn ($q) => $q->where('user_id', $r->user()->id))->latest('id')->get());
    }

    public function foto(Request $r, LaporanJalan $laporan)
    {
        $this->access($r, $laporan);
        abort_unless(Storage::disk('local')->exists($laporan->foto), 404);

        return response()->file(Storage::disk('local')->path($laporan->foto), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function store(LaporanRequest $r)
    {
        $data = $r->safe()->except('foto');
        $data['user_id'] = $r->user()->id;
        $data['status'] = $r->user()->isAdmin() ? $data['status'] : 'Diajukan';
        $path = $r->file('foto')->store('roadwatch', 'local');
        try {
            LaporanJalan::create([...$data, 'foto' => $path]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return response()->json(['message' => 'Laporan ditambahkan.'], 201);
    }

    public function update(LaporanRequest $r, LaporanJalan $laporan)
    {
        $this->access($r, $laporan, true);
        $data = $r->safe()->except('foto');
        $old = $laporan->foto;
        $new = $r->hasFile('foto') ? $r->file('foto')->store('roadwatch', 'local') : null;
        if ($new) {
            $data['foto'] = $new;
        }
        try {
            $laporan->update($data);
        } catch (\Throwable $e) {
            if ($new) {
                Storage::disk('local')->delete($new);
            }
            throw $e;
        }
        if ($new) {
            Storage::disk('local')->delete($old);
        }

        return response()->json(['message' => 'Laporan diperbarui.']);
    }

    public function destroy(Request $r, LaporanJalan $laporan)
    {
        $this->access($r, $laporan, true);
        $path = $laporan->foto;
        $laporan->delete();
        Storage::disk('local')->delete($path);

        return response()->json(['message' => 'Laporan dihapus.']);
    }
}
