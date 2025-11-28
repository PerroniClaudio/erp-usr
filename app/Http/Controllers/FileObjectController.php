<?php

namespace App\Http\Controllers;

use App\Models\FileObject;
use App\Models\FileObjectSector;
use App\Models\Protocol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileObjectController extends Controller
{
    //

    public function index() {

        return view('admin.files.index');

    }

    public function viewFolder($folderHash) {

        $relativePath = trim(base64_decode($folderHash), '/');
        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $currentBasePath = $relativePath ? "{$baseRoot}/{$relativePath}" : $baseRoot;

        $objects = FileObject::whereLike('storage_path', "{$currentBasePath}/%")
            ->with(['sector', 'protocol'])
            ->get();
        $files = collect([]);
        $folders = collect([]);
        foreach($objects as $object) {
            $relativeToCurrent = ltrim(\Illuminate\Support\Str::after($object->storage_path, "{$currentBasePath}/"), '/');
            if ($relativeToCurrent === '') {
                continue;
            }

            $pathParts = explode('/', $relativeToCurrent);

            if (count($pathParts) === 1) {
                $childRelativePath = $relativePath ? "{$relativePath}/{$pathParts[0]}" : $pathParts[0];
                $object->relative_path = $childRelativePath;
                if ($object->isFolder()) {
                    $folders->push($object);
                } else {
                    $files->push($object);
                }
            }
        }

      

        $sectors = FileObjectSector::all();
        $protocols = Protocol::all();
       
        return view('admin.files.folder', [
            'files' => $files,
            'folders' => $folders,
            'sectors' => $sectors,
            'protocols' => $protocols,
            'folder_steps' => $relativePath === '' ? [] : explode('/', $relativePath),
        ]);

    }

    public function uploadFile(Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240', // 10 MB default; adjust as needed
            'file_object_sector_id' => 'required|exists:file_object_sectors,id',
            'protocol_id' => 'required|exists:protocols,id',
            'current_folder_path' => 'nullable|string',
            'is_public' => 'nullable|boolean',
            'valid_at' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $currentFolder = trim($request->input('current_folder_path', ''), '/');
        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $basePath = $currentFolder ? "{$baseRoot}/{$currentFolder}" : $baseRoot;

        // Salva il file su S3 (configurato come default disk) mantenendo una struttura ordinata
        $storedPath = $file->store($basePath);

        $sector = FileObjectSector::findOrFail($request->input('file_object_sector_id'));
        $protocol = Protocol::findOrFail($request->input('protocol_id'));
        $validAt = $request->input('valid_at') ? Carbon::parse($request->input('valid_at')) : Carbon::today();
        [$protocolNumber, $protocolSequence, $protocolYear] = $protocol->generateNumberForSector($sector, $validAt);

        FileObject::create([
            'user_id' => Auth::id(),
            'file_object_sector_id' => $sector->id,
            'protocol_id' => $protocol->id,
            'logical_key' => Str::uuid(),
            'version' => 1,
            'type' => 'file',
            'name' => $file->getClientOriginalName(),
            'uploaded_name' => $file->getClientOriginalName(),
            'document_type' => null,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $storedPath,
            'is_public' => (bool) $request->input('is_public', false),
            'uploaded_by_system' => false,
            'processed_at' => null,
            'expires_at' => null,
            'valid_at' => $validAt,
            'protocol_number' => $protocolNumber,
            'protocol_sequence' => $protocolSequence,
            'protocol_year' => $protocolYear,
        ]);

        return back()->with('success', __('files.files_upload_success'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|string|max:255',
            'current_folder_path' => 'nullable|string',
        ]);

        $currentFolder = trim($request->input('current_folder_path', ''), '/');
        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $basePath = $currentFolder ? "{$baseRoot}/{$currentFolder}" : $baseRoot;

        $folderSlug = Str::slug($request->input('folder_name')) ?: Str::random(8);
        $folderPath = "{$basePath}/{$folderSlug}";

        // Crea directory su storage (S3) per chiarezza del path
        Storage::makeDirectory($folderPath);

        FileObject::create([
            'user_id' => Auth::id(),
            'file_object_sector_id' => null,
            'logical_key' => Str::uuid(),
            'version' => 1,
            'type' => 'folder',
            'name' => $request->input('folder_name'),
            'uploaded_name' => $request->input('folder_name'),
            'document_type' => null,
            'mime_type' => null,
            'file_size' => 0,
            'storage_path' => $folderPath,
            'is_public' => false,
            'uploaded_by_system' => false,
            'processed_at' => null,
            'expires_at' => null,
        ]);

        return back()->with('success', __('files.files_create_folder_success'));
    }

}
