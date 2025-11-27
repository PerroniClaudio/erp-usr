<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FileObjectSector;
use Illuminate\Validation\Rule;

class FileObjectSectorController extends Controller
{
    //

    public function index()
    {
        //

        $sectors = FileObjectSector::query()->latest()->get();

        return view('admin.files.sectors.index', compact('sectors'));
    }

    public function create()
    {
        //

        return view('admin.files.sectors.create');
    }

    public function show(FileObjectSector $sector)
    {
        //

        return view('admin.files.sectors.show', compact('sector'));
    }

    public function store(Request $request)
    {
        //

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'acronym' => [
                'nullable',
                'string',
                'max:4',
                Rule::unique('file_object_sectors')->whereNull('deleted_at'),
            ],
            'color' => 'nullable|string|max:7',
        ]);

        FileObjectSector::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'acronym' => $request->input('acronym'),
            'color' => $request->input('color'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.sectors.index')->with('success', 'Settore File creato con successo.');
    }

    public function update(Request $request, FileObjectSector $sector)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'acronym' => [
                'nullable',
                'string',
                'max:4',
                Rule::unique('file_object_sectors')->ignore($sector->id)->whereNull('deleted_at'),
            ],
            'color' => 'nullable|string|max:7',
        ]);

        $sector->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'acronym' => $request->input('acronym'),
            'color' => $request->input('color'),
        ]);

        return redirect()->route('admin.sectors.index')->with('success', 'Settore File aggiornato con successo.');
    }

    public function destroy(FileObjectSector $sector)
    {
        //
        $sector->delete();

        return redirect()->route('admin.sectors.index')->with('success', 'Settore File eliminato con successo.');

    }
}
