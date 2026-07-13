<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CidGroup;
use Illuminate\Http\Request;

class AdminCidGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $groups = CidGroup::withCount('cidNumbers')
            ->with('campaign:id,name')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cid-groups.index', compact('groups', 'search'));
    }

    public function destroy(CidGroup $cidGroup)
    {
        $cidGroup->delete();

        return redirect()->route('admin.cid-groups.index')
            ->with('success', 'CID group deleted.');
    }
}
