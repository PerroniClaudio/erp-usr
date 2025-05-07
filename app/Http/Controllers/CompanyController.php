<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $allCompanies = Company::all();

        return view('admin.personnel.companies.index', [
            'companies' => $allCompanies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
        return view('admin.personnel.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Azienda creata con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company) {
        //

        return view('admin.personnel.companies.edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Azienda aggiornata con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company) {
        //


        if ($company->users()->exists()) {
            return redirect()->route('companies.index')->with('error', 'Impossibile eliminare l\'azienda perché ha utenti associati.');
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Azienda eliminata con successo.');
    }

    public function associateUsers(Request $request, Company $company) {
        //
        $users = json_decode($request->input('users'), true);

        $company->users()->syncWithoutDetaching($users);

        return response()->json([
            'message' => 'Utenti associati con successo all\'azienda.',
            'users' => $company->users()->get(),
        ]);
    }

    public function dissociateUsers(Company $company, User $user) {
        //
        $company->users()->detach($user->id);

        return response()->json([
            'message' => 'Utente dissociato con successo dall\'azienda.',
            'users' => $company->users()->get(),
        ]);
    }
    public function getUsers(Company $company) {
        //
        $users = $company->users()->get();

        return response()->json([
            'users' => $users,
        ]);
    }
    public function availableUsers(Company $company, Request $request) {
        //

        $excludedUserIds = $company->users()->get();

        $excludedUserIds = $excludedUserIds->pluck('id')->toArray();

        $users = User::whereNotIn('id', $excludedUserIds)
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('email', 'like', '%' . $request->input('q') . '%');
            })
            ->get();
        return response()->json([
            'users' => $users,
        ]);
    }
}
