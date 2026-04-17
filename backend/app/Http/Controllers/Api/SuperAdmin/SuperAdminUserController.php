<?php
namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('tenants')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:central.users,email',
            'password' => 'required|string|min:8',
            'is_superadmin' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        // Optional: attach to specific tenants here if provided via request
        
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with('tenants')->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('central.users')->ignore($user->id)],
            'is_superadmin' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
