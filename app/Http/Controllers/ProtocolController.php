<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProtocolController extends Controller
{
    public function index()
    {
        $protocols = Protocol::query()->latest()->get();

        return view('admin.files.protocols.index', compact('protocols'));
    }

    public function create()
    {
        return view('admin.files.protocols.create');
    }

    public function show(Protocol $protocol)
    {
        return view('admin.files.protocols.show', compact('protocol'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => [
                'required',
                'string',
                'max:10',
                Rule::unique('protocols')->whereNull('deleted_at'),
            ],
            'counter' => 'nullable|integer|min:1',
        ]);

        Protocol::create([
            'name' => $request->input('name'),
            'acronym' => $request->input('acronym'),
            'counter' => $request->input('counter', 1),
            'counter_year' => now()->year,
        ]);

        return redirect()->route('admin.protocols.index')->with('success', __('files.protocols_create_success'));
    }

    public function update(Request $request, Protocol $protocol)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => [
                'required',
                'string',
                'max:10',
                Rule::unique('protocols')->ignore($protocol->id)->whereNull('deleted_at'),
            ],
            'counter' => 'nullable|integer|min:1',
            'counter_year' => 'nullable|integer|min:1900|max:3000',
        ]);

        $protocol->update([
            'name' => $request->input('name'),
            'acronym' => $request->input('acronym'),
            'counter' => $request->input('counter', 1),
            'counter_year' => $request->input('counter_year') ?: now()->year,
        ]);

        return redirect()->route('admin.protocols.index')->with('success', __('files.protocols_update_success'));
    }

    public function destroy(Protocol $protocol)
    {
        $protocol->delete();

        return redirect()->route('admin.protocols.index')->with('success', __('files.protocols_delete_success'));
    }
}
