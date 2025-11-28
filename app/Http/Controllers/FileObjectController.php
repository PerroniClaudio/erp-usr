<?php

namespace App\Http\Controllers;

use App\Models\FileObject;
use App\Models\FileObjectSector;
use App\Models\Protocol;
use App\Models\User;
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
    public function search(Request $request) {
        $user = $request->user();

        if (! $user->hasRole('admin')) {
            abort(403);
        }

        $query = trim((string) $request->input('q', ''));
        $results = null;

        if ($query !== '') {
            $results = FileObject::search($query)->paginate(25);
            $results->getCollection()->loadMissing(['sector', 'protocol']);
        }

        return view('admin.files.search', [
            'results' => $results,
            'query' => $query,
        ]);
    }

    public function viewFolder($folderHash) {

        $relativePath = trim(base64_decode($folderHash), '/');
        $relativePath = $this->enforceUserFolder($relativePath, auth()->user());
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

        $aliases = [
            'public' => 'File pubblici',
            'personnel' => 'File dipendenti',
            'attachments' => 'Allegati',
        ];

        $folderSteps = $relativePath === '' ? [] : explode('/', $relativePath);
        $personnelNames = [];

        if (count($folderSteps) >= 2 && $folderSteps[0] === 'personnel') {
            $userId = $folderSteps[1];
            $user = User::find($userId);
            if ($user) {
                $personnelNames[$userId] = $user->name;
            }
        }
       
        return view('admin.files.folder', [
            'files' => $files,
            'folders' => $folders,
            'sectors' => $sectors,
            'protocols' => $protocols,
            'folder_steps' => $folderSteps,
            'folder_aliases' => $aliases,
            'personnel_names' => $personnelNames,
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

        $currentUser = $request->user();
        $file = $request->file('file');
        $currentFolder = $this->enforceUserFolder($request->input('current_folder_path', ''), $currentUser);
        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $basePath = $currentFolder ? "{$baseRoot}/{$currentFolder}" : $baseRoot;

        // Salva il file su S3 (configurato come default disk) mantenendo una struttura ordinata
        $storedPath = $file->store($basePath);

        $sector = FileObjectSector::findOrFail($request->input('file_object_sector_id'));
        $protocol = Protocol::findOrFail($request->input('protocol_id'));
        $validAt = $request->input('valid_at') ? Carbon::parse($request->input('valid_at')) : Carbon::today();
        [$protocolNumber, $protocolSequence, $protocolYear] = $protocol->generateNumberForSector($sector, $validAt);

        FileObject::create([
            'user_id' => $currentUser->id,
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

        $currentUser = $request->user();
        $currentFolder = $this->enforceUserFolder($request->input('current_folder_path', ''), $currentUser);
        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $basePath = $currentFolder ? "{$baseRoot}/{$currentFolder}" : $baseRoot;

        $folderSlug = Str::slug($request->input('folder_name')) ?: Str::random(8);
        $folderPath = "{$basePath}/{$folderSlug}";

        // Crea directory su storage (S3) per chiarezza del path
        Storage::makeDirectory($folderPath);

        FileObject::create([
            'user_id' => $currentUser->id,
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

    public function download(Request $request, FileObject $fileObject)
    {
        $user = $request->user();
        $this->authorizeDownload($user, $fileObject);

        if ($fileObject->isFolder()) {
            abort(404);
        }

        $disk = Storage::disk(config('filesystems.default'));
        if (! $disk->exists($fileObject->storage_path)) {
            abort(404);
        }

        $downloadName = $fileObject->name ?: basename($fileObject->storage_path);

        return $disk->download($fileObject->storage_path, $downloadName);
    }

    public function destroy(Request $request, FileObject $fileObject)
    {
        $user = $request->user();
        $this->authorizeDelete($user, $fileObject);

        if ($fileObject->isFolder()) {
            abort(400, 'Folder deletion is not supported.');
        }

        $disk = Storage::disk(config('filesystems.default'));
        if ($disk->exists($fileObject->storage_path)) {
            $disk->delete($fileObject->storage_path);
        }

        $fileObject->delete();

        return back()->with('success', __('files.files_delete_success'));
    }

    public function versions(Request $request, FileObject $fileObject)
    {
        $user = $request->user();
        $this->authorizeDownload($user, $fileObject);

        if ($fileObject->isFolder()) {
            abort(404);
        }

        $versions = FileObject::where('logical_key', $fileObject->logical_key)
            ->orderByDesc('version')
            ->with(['user'])
            ->get();

        $canUpload = $this->canUploadVersion($user, $fileObject);

        return view('admin.files.versioning', [
            'file' => $fileObject,
            'versions' => $versions,
            'canUpload' => $canUpload,
        ]);
    }

    public function uploadVersion(Request $request, FileObject $fileObject)
    {
        $user = $request->user();
        $this->authorizeVersionUpload($user, $fileObject);

        if ($fileObject->isFolder()) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $latest = FileObject::where('logical_key', $fileObject->logical_key)
            ->orderByDesc('version')
            ->firstOrFail();

        $file = $request->file('file');
        $folderPath = trim(dirname($latest->storage_path), '/');
        $storedPath = $folderPath && $folderPath !== '.' ? $file->store($folderPath) : $file->store('');

        $sectorId = $latest->file_object_sector_id;
        $protocolId = $latest->protocol_id;
        $validAt = $latest->valid_at ?? Carbon::today();

        $protocolNumber = null;
        $protocolSequence = null;
        $protocolYear = null;

        if ($protocolId && $sectorId) {
            $sector = FileObjectSector::find($sectorId);
            $protocol = Protocol::find($protocolId);

            if ($sector && $protocol) {
                [$protocolNumber, $protocolSequence, $protocolYear] = $protocol->generateNumberForSector(
                    $sector,
                    $validAt instanceof Carbon ? $validAt : Carbon::parse($validAt)
                );
            }
        }

        FileObject::create([
            'user_id' => $user->id,
            'file_object_sector_id' => $sectorId,
            'protocol_id' => $protocolId,
            'protocol_number' => $protocolNumber,
            'protocol_sequence' => $protocolSequence,
            'protocol_year' => $protocolYear,
            'logical_key' => $fileObject->logical_key,
            'version' => $latest->version + 1,
            'type' => 'file',
            'name' => $file->getClientOriginalName(),
            'uploaded_name' => $file->getClientOriginalName(),
            'document_type' => $latest->document_type,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $storedPath,
            'is_public' => $latest->is_public,
            'uploaded_by_system' => false,
            'processed_at' => null,
            'expires_at' => null,
            'valid_at' => $validAt,
        ]);

        return back()->with('success', __('files.files_version_upload_success'));
    }

    private function authorizeDownload(User $user, FileObject $fileObject): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if ($fileObject->user_id === $user->id) {
            return;
        }

        if ($fileObject->is_public) {
            return;
        }

        abort(403);
    }

    private function authorizeDelete(User $user, FileObject $fileObject): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if ($fileObject->user_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function authorizeVersionUpload(User $user, FileObject $fileObject): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if ($fileObject->user_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function canUploadVersion(User $user, FileObject $fileObject): bool
    {
        return $user->hasRole('admin') || $fileObject->user_id === $user->id;
    }

    private function enforceUserFolder(?string $relativePath, User $user): string
    {
        $cleanPath = trim((string) $relativePath, '/');

        if ($user->hasRole('admin')) {
            return $cleanPath;
        }

        $userBase = "personnel/{$user->id}";

        if ($cleanPath === '') {
            return $userBase;
        }

        if (! Str::startsWith($cleanPath, $userBase)) {
            abort(403);
        }

        return $cleanPath;
    }

}
