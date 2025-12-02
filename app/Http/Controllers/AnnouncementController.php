<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FileObject;
use App\Models\FileObjectSector;
use App\Models\Protocol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        [$selectedAttachments, $sectors, $protocols] = $this->prepareAttachmentFormData($request);

        return view('admin.announcements.create', [
            'selectedAttachments' => $selectedAttachments,
            'sectors' => $sectors,
            'protocols' => $protocols,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'attachment_file_ids' => 'array',
            'attachment_file_ids.*' => 'integer|exists:file_objects,id',
            'new_attachment' => 'nullable|file|max:10240',
            'new_attachment_file_object_sector_id' => 'required_with:new_attachment|exists:file_object_sectors,id',
            'new_attachment_protocol_id' => 'required_with:new_attachment|exists:protocols,id',
            'new_attachment_valid_at' => 'nullable|date',
            'new_attachment_is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $announcement = Announcement::create($validated);

        $this->syncAnnouncementAttachments($request, $announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Annuncio creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $announcement->load(['creator', 'viewedByUsers', 'attachments']);
        $totalUsers = User::role('standard')->count();
        $viewedCount = $announcement->viewedByUsers()->count();

        return view('admin.announcements.show', compact('announcement', 'totalUsers', 'viewedCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Announcement $announcement)
    {
        $announcement->load('attachments');
        [$selectedAttachments, $sectors, $protocols] = $this->prepareAttachmentFormData($request, $announcement);

        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'selectedAttachments' => $selectedAttachments,
            'sectors' => $sectors,
            'protocols' => $protocols,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'attachment_file_ids' => 'array',
            'attachment_file_ids.*' => 'integer|exists:file_objects,id',
            'new_attachment' => 'nullable|file|max:10240',
            'new_attachment_file_object_sector_id' => 'required_with:new_attachment|exists:file_object_sectors,id',
            'new_attachment_protocol_id' => 'required_with:new_attachment|exists:protocols,id',
            'new_attachment_valid_at' => 'nullable|date',
            'new_attachment_is_public' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);
        $this->syncAnnouncementAttachments($request, $announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Annuncio aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Annuncio eliminato con successo.');
    }

    /**
     * Get unread announcements for the current user
     */
    public function unread()
    {
        $user = Auth::user();
        $announcements = $user->unreadAnnouncements()
            ->with('attachments')
            ->get()
            ->map(function (Announcement $announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_at' => $announcement->created_at,
                    'attachments' => $announcement->attachments->map(fn (FileObject $file) => [
                        'id' => $file->id,
                        'name' => $file->name,
                        'mime_type' => $file->mime_type,
                        'download_url' => route('files.download', $file),
                    ])->values(),
                ];
            })
            ->values();

        return response()->json($announcements);
    }

    /**
     * Mark an announcement as read for the current user
     */
    public function markAsRead(Announcement $announcement)
    {
        $user = Auth::user();

        if (! $announcement->isViewedBy($user)) {
            $announcement->viewedByUsers()->attach($user->id);
        }

        return response()->json(['success' => true]);
    }

    private function prepareAttachmentFormData(Request $request, ?Announcement $announcement = null): array
    {
        $sectors = FileObjectSector::all();
        $protocols = Protocol::all();
        $existingIds = $announcement ? $announcement->attachments->pluck('id')->all() : [];
        $selectedIds = collect(session()->getOldInput('attachment_file_ids', $existingIds))
            ->filter()
            ->unique()
            ->values();

        $selectedAttachments = $selectedIds->isEmpty()
            ? collect([])
            : FileObject::whereIn('id', $selectedIds)->get();

        return [$selectedAttachments, $sectors, $protocols];
    }

    private function syncAnnouncementAttachments(Request $request, Announcement $announcement): void
    {
        $attachmentIds = collect($request->input('attachment_file_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($request->hasFile('new_attachment')) {
            $attachmentIds->push($this->storeNewAttachmentFile($request));
        }

        $validAttachmentIds = $attachmentIds->isEmpty()
            ? []
            : FileObject::query()
                ->whereIn('id', $attachmentIds)
                ->where('type', 'file')
                ->pluck('id')
                ->all();

        $announcement->attachments()->sync($validAttachmentIds);
    }

    private function storeNewAttachmentFile(Request $request): int
    {
        $file = $request->file('new_attachment');
        $sector = FileObjectSector::findOrFail($request->input('new_attachment_file_object_sector_id'));
        $protocol = Protocol::findOrFail($request->input('new_attachment_protocol_id'));
        $validAt = $request->input('new_attachment_valid_at')
            ? Carbon::parse($request->input('new_attachment_valid_at'))
            : Carbon::today();

        [$protocolNumber, $protocolSequence, $protocolYear] = $protocol->generateNumberForSector($sector, $validAt);

        $baseRoot = app()->environment('local') ? 'dev/files' : 'files';
        $storedPath = $file->store("{$baseRoot}/attachments");

        $fileObject = FileObject::create([
            'user_id' => $request->user()->id,
            'file_object_sector_id' => $sector->id,
            'protocol_id' => $protocol->id,
            'protocol_number' => $protocolNumber,
            'protocol_sequence' => $protocolSequence,
            'protocol_year' => $protocolYear,
            'logical_key' => Str::uuid(),
            'version' => 1,
            'type' => 'file',
            'name' => $file->getClientOriginalName(),
            'uploaded_name' => $file->getClientOriginalName(),
            'document_type' => null,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $storedPath,
            'is_public' => (bool) $request->input('new_attachment_is_public', true),
            'uploaded_by_system' => false,
            'processed_at' => null,
            'expires_at' => null,
            'valid_at' => $validAt,
        ]);

        return $fileObject->id;
    }
}
