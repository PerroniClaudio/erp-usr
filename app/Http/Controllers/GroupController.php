<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $allGroups = Group::all();

        return view('admin.personnel.groups.index', [
            'groups' => $allGroups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        return view('admin.personnel.groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = Group::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);


        return redirect()->route('groups.index')->with('success', 'Gruppo creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group) {
        //

        return view('admin.personnel.groups.edit', [
            'group' => $group,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group) {
        //
    }

    public function associateUsers(Request $request, Group $group) {

        $users = json_decode($request->input('users'), true);

        $group->users()->syncWithoutDetaching($users);

        return response()->json([
            'message' => 'Utenti associati al gruppo con successo.',
            'users' => $group->users()->get(),
        ]);
    }

    public function dissociateUsers(Group $group, User $user) {

        $group->users()->detach($user->id);

        return redirect()->route('groups.edit', $group->id)->with('success', 'Utenti dissociati dal gruppo con successo.');
    }

    public function getUsers(Group $group) {
        $users = $group->users()->get();

        return response()->json($users);
    }

    public function availableUsers(Group $group, Request $request) {
        $query = User::whereDoesntHave('groups', function ($query) use ($group) {
            $query->where('groups.id', $group->id);
        });

        if ($request->has('q') && !is_null($request->input('q'))) {
            $search = $request->input('q');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $availableUsers = $query->get();

        return response()->json([
            "users" => $availableUsers
        ]);
    }
}
