<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        $users = User::where('active', true) // Só mostra usuários ativos
            ->orderBy($sort, $direction)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'is_admin' => $user->is_admin,
                    'active' => $user->active
                ];
            });

        return Inertia::render('Admin/Users/UserList', [
            'users' => $users,
            'filters' => [
                'sort' => $sort,
                'direction' => $direction,
            ]
        ]);
    }

    public function inactive(Request $request)
    {
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        $users = User::where('active', false)
            ->orderBy($sort, $direction)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'is_admin' => $user->is_admin,
                    'active' => $user->active
                ];
            });

        return Inertia::render('Admin/Users/InactiveUserList', [
            'users' => $users,
            'filters' => [
                'sort' => $sort,
                'direction' => $direction,
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/CreateUser');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo'] = $path;
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'photo' => $validated['photo'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Usuário criado com sucesso!');
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'sometimes|boolean',
            'photo' => 'sometimes|image|max:2048',
        ]);
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $validated['is_admin'] ?? false,
        ];
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $updateData['photo'] = $path;
        }
        $user->update($updateData);

        return redirect()->route('admin.users.index');
    }


    public function uploadPhoto(Request $request, User $user)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;
            $user->save();

            return redirect()->back()->with('success', 'Foto atualizada com sucesso!');
        }

        return redirect()->back()->with('error', 'Nenhuma foto foi enviada!');
    }

    public function destroy(User $user)
    {
        try {
            $user->update(['active' => false]);

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuário inativado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao inativar usuário: ' . $e->getMessage());
        }
    }

    // Novo método para reativar usuário
    public function restore(User $user)
    {
        try {
            $user->update(['active' => true]);
            
            return redirect()->route('admin.users.inactive')
                ->with('success', 'Usuário reativado com sucesso!');
                
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao reativar usuário: '.$e->getMessage());
        }
    }
}
