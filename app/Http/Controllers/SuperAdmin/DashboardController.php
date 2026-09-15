<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_schools' => School::count(),
            'active_schools' => School::where('status', 'active')->count(),
            'suspended_schools' => School::where('status', 'suspended')->count(),
            'total_principals' => User::where('role', 'principal')->count(),
        ];

        $recentSchools = School::latest()->take(5)->get();

        return view('super-admin.dashboard.index', compact('stats', 'recentSchools'));
    }
}
