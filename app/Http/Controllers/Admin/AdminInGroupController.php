<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InGroup;
use Illuminate\Http\Request;

class AdminInGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $groups = InGroup::with('campaign:id,name')
            ->withCount('users')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.in-groups.index', compact('groups', 'search'));
    }

    public function destroy(InGroup $inGroup)
    {
        $inGroup->delete();

        return redirect()->route('admin.in-groups.index')
            ->with('success', 'Inbound group deleted.');
    }
}
