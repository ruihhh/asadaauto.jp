<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $inquiries = Inquiry::latest()->paginate(20);
        $unreadCount = Inquiry::unread()->count();

        return view('admin.inquiries.index', compact('inquiries', 'unreadCount'));
    }

    public function show(Inquiry $inquiry): View
    {
        if (is_null($inquiry->read_at)) {
            $inquiry->update(['read_at' => now()]);
        }

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'お問い合わせを削除しました。');
    }
}
