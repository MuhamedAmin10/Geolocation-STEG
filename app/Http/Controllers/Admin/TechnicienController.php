<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TechnicienController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-techniciens');

        $techniciens = Technicien::query()
            ->with('user')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15);

        return view('admin.techniciens.index', compact('techniciens'));
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-techniciens');

        return view('admin.techniciens.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-techniciens');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],

            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:255'],
            'zone_intervention' => ['nullable', 'string', 'max:255'],
            'competences' => ['nullable', 'string'],
            'disponible' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'Technicien',
                'active' => (bool) ($data['active'] ?? true),
            ]);

            $technicien = Technicien::query()->create([
                'user_id' => $user->id,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'zone_intervention' => $data['zone_intervention'] ?? null,
                'competences' => $data['competences'] ?? null,
                'disponible' => (bool) ($data['disponible'] ?? true),
            ]);

            return redirect()
                ->route('admin.techniciens.edit', $technicien)
                ->with('status', 'Technicien créé.');
        });
    }

    public function edit(Request $request, Technicien $technicien)
    {
        Gate::authorize('manage-techniciens');

        $technicien->load('user');

        return view('admin.techniciens.edit', compact('technicien'));
    }

    public function update(Request $request, Technicien $technicien)
    {
        Gate::authorize('manage-techniciens');

        $technicien->load('user');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($technicien->user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],

            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:255'],
            'zone_intervention' => ['nullable', 'string', 'max:255'],
            'competences' => ['nullable', 'string'],
            'disponible' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $technicien) {
            $technicien->user->name = $data['name'];
            $technicien->user->email = $data['email'];
            $technicien->user->role = 'Technicien';
            $technicien->user->active = (bool) ($data['active'] ?? false);

            if (!empty($data['password'])) {
                $technicien->user->password = Hash::make($data['password']);
            }

            $technicien->user->save();

            $technicien->nom = $data['nom'];
            $technicien->prenom = $data['prenom'];
            $technicien->telephone = $data['telephone'];
            $technicien->zone_intervention = $data['zone_intervention'] ?? null;
            $technicien->competences = $data['competences'] ?? null;
            $technicien->disponible = (bool) ($data['disponible'] ?? false);
            $technicien->save();
        });

        return back()->with('status', 'Technicien mis à jour.');
    }

    public function destroy(Request $request, Technicien $technicien)
    {
        Gate::authorize('manage-techniciens');

        $technicien->load('user');

        DB::transaction(function () use ($technicien) {
            $technicien->user->active = false;
            $technicien->user->save();

            $technicien->disponible = false;
            $technicien->save();
        });

        return redirect()
            ->route('admin.techniciens.index')
            ->with('status', 'Technicien désactivé.');
    }
}
