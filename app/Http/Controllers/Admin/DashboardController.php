<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => Car::count(),
            'available' => Car::where('status', 'available')->count(),
            'reserved' => Car::where('status', 'reserved')->count(),
            'sold' => Car::where('status', 'sold')->count(),
            'featured' => Car::where('featured', true)->count(),
        ];

        $inquiryStats = [
            'total' => Inquiry::count(),
            'unread' => Inquiry::unread()->count(),
            'this_month' => Inquiry::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentInquiries = Inquiry::latest()->limit(5)->get();
        $recentCars = Car::latest()->limit(5)->get();

        return view('admin.dashboard.index', compact('stats', 'inquiryStats', 'recentInquiries', 'recentCars'));
    }
}
