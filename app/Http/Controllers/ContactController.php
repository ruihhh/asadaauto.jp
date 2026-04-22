<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquiry;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $stock_no = $request->query('stock_no');
        $car = null;
        if ($stock_no) {
            $car = Car::where('stock_no', $stock_no)->first();
        }

        return view('contact.index', compact('stock_no', 'car'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'stock_no' => 'nullable|string|exists:cars,stock_no',
            'message' => 'required|string|max:2000',
        ]);

        Inquiry::create($validated);

        $adminEmail = config('mail.from.address', 'admin@example.com');
        try {
            Mail::to($adminEmail)->send(new ContactInquiry($validated));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail send failed: ' . $e->getMessage());
        }

        return redirect()->route('contact.thanks');
    }

    public function thanks(): View
    {
        return view('contact.thanks');
    }
}
