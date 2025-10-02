<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function create()
    {
        return view('admin.announcements.create');
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
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Annuncio creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $announcement->load(['creator', 'viewedByUsers']);
        $totalUsers = User::role('standard')->count();
        $viewedCount = $announcement->viewedByUsers()->count();

        return view('admin.announcements.show', compact('announcement', 'totalUsers', 'viewedCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
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
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);

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
        $announcements = $user->unreadAnnouncements()->get();

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
}
