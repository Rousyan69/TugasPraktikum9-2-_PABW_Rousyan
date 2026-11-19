<?php

namespace Modules\RuasJalan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\RuasJalan\Http\Requests\RuasRequest;
use Modules\RuasJalan\Models\RuasJalan;

class RuasController extends Controller
{
    public function index(Request $r)
    {
        $q = mb_substr((string) $r->query('q', ''), 0, 100);

        return Inertia::render('RuasJalan::index', ['rows' => RuasJalan::when($q, fn ($b) => $b->where('nama', 'like', "%{$q}%"))->latest('id')->paginate(5)->withQueryString(), 'q' => $q]);
    }

    public function store(RuasRequest $r)
    {
        RuasJalan::create($r->validated());

        return back()->with('success', 'Ruas jalan ditambahkan.');
    }

    public function update(RuasRequest $r, RuasJalan $ruas)
    {
        $ruas->update($r->validated());

        return back()->with('success', 'Ruas jalan diperbarui.');
    }

    public function destroy(RuasJalan $ruas)
    {
        if ($ruas->laporan()->exists()) {
            return back()->withErrors(['hapus' => 'Ruas masih digunakan laporan. Hapus laporannya terlebih dahulu.']);
        } $ruas->delete();

        return back()->with('success','Ruas jalan dihapus.');
    }
}
