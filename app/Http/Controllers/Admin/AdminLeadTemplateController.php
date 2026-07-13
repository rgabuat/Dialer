<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadTemplate;
use Illuminate\Http\Request;

class AdminLeadTemplateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $templates = LeadTemplate::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->withCount('campaigns')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.lead-templates.index', compact('templates', 'search'));
    }

    public function destroy(LeadTemplate $leadTemplate)
    {
        $leadTemplate->delete();

        return redirect()->route('admin.lead-templates.index')
            ->with('success', 'Template deleted.');
    }
}
