<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LaporanJalan\Models\LaporanJalan;
use Modules\Pendaftaran\Models\Daftar;
use Modules\RuasJalan\Models\RuasJalan;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with record counts per module.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'totalRuas' => RuasJalan::count(),
            'totalLaporan' => LaporanJalan::when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))->count(),
            'totalPendaftar' => Daftar::count(),
            'totalUsers' => $request->user()->isAdmin() ? User::count() : null,
        ]);
    }
}
