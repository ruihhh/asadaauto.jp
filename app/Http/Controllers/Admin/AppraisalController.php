<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppraisalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppraisalController extends Controller
{
    public function index(): View
    {
        $appraisals = AppraisalRequest::latest()->paginate(20);
        $unreadCount = AppraisalRequest::unread()->count();

        return view('admin.appraisals.index', compact('appraisals', 'unreadCount'));
    }

    public function show(AppraisalRequest $appraisal): View
    {
        if (is_null($appraisal->read_at)) {
            $appraisal->update(['read_at' => now()]);
        }

        return view('admin.appraisals.show', compact('appraisal'));
    }

    public function destroy(AppraisalRequest $appraisal): RedirectResponse
    {
        $appraisal->delete();

        return redirect()->route('admin.appraisals.index')
            ->with('success', '査定依頼を削除しました。');
    }
}
