<?php

namespace App\Http\Controllers;

use App\Mail\BuyAppraisalMail;
use App\Models\AppraisalRequest;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BuyController extends Controller
{
    public function index(): View
    {
        $makes = Car::publicInventory()
            ->select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        return view('buy.index', compact('makes'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'make'       => 'required|string|max:100',
            'model'      => 'required|string|max:100',
            'grade'      => 'nullable|string|max:100',
            'model_year' => 'required|integer|min:1980|max:' . (date('Y') + 1),
            'mileage'    => 'required|integer|min:0|max:9999999',
            'color'      => 'nullable|string|max:60',
            'condition'  => 'required|in:good,normal,damaged',
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:20',
            'zip'        => 'nullable|string|max:10',
            'message'    => 'nullable|string|max:2000',
        ]);

        AppraisalRequest::create($validated);

        $adminEmail = config('mail.from.address', 'admin@example.com');
        try {
            Mail::to($adminEmail)->send(new BuyAppraisalMail($validated));
        } catch (\Exception $e) {
            Log::error('BuyAppraisalMail send failed: ' . $e->getMessage());
        }

        return redirect()->route('buy.thanks');
    }

    public function thanks(): View
    {
        return view('buy.thanks');
    }
}
