<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccessRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // List all users - only if user has read_access on users module
    public function index()
    {
        $access = AccessRight::where('user_id', auth()->id())
            ->where('module_name', 'users')
            ->first();

        if (!$access || $access->read_access == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::select('id', 'name', 'email', 'is_active')->get();
        return response()->json($users);
    }

    // Create new user - requires write_access
    public function store(Request $request)
    {
        $access = AccessRight::where('user_id', auth()->id())
            ->where('module_name', 'users')
            ->first();

        if (!$access || $access->write_access == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user_master',
            'password' => 'required|string|min:6',
            'access' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active,
            'created_by' => auth()->user()->id,
        ]);

       // Store method snippet (inside foreach for access rights)
if ($request->filled('access')) {
    foreach ($request->access as $module => $rights) {
        // Allowed modules only for security (optional)
        $allowedModules = ['users', 'timesheet'];

        if (in_array($module, $allowedModules)) {
            AccessRight::create([
                'user_id' => $user->id,
                'module_name' => $module,
                'read_access' => isset($rights['read']) ? 1 : 0,
                'write_access' => isset($rights['write']) ? 1 : 0,
                'update_access' => isset($rights['update']) ? 1 : 0,
                'delete_access' => isset($rights['delete']) ? 1 : 0,
            ]);
        }
    }
}


        return response()->json(['message' => 'User created', 'user' => $user]);
    }

    // Show single user - allow if read_access
    public function show(User $user)
    {
        $access = AccessRight::where('user_id', auth()->id())
            ->where('module_name', 'users')
            ->first();

        if (!$access || $access->read_access == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    // Update user - requires update_access
    public function update(Request $request, User $user)
    {
        $access = AccessRight::where('user_id', auth()->id())
            ->where('module_name', 'users')
            ->first();

        if (!$access || $access->update_access == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('user_master')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'is_active' => 'required|boolean',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_active = $request->is_active;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    // Delete user - requires delete_access
    public function destroy(User $user)
    {
        $access = AccessRight::where('user_id', auth()->id())
            ->where('module_name', 'users')
            ->first();

        if (!$access || $access->delete_access == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    // New API endpoint: Get current user's access rights
    public function getAccessRights()
    {
        $userId = auth()->id();
        $accessRights = AccessRight::where('user_id', $userId)->get()->keyBy('module_name');

        $result = [];
        foreach ($accessRights as $module => $access) {
            $result[$module] = [
                'read' => $access->read_access,
                'write' => $access->write_access,
                'update' => $access->update_access,
                'delete' => $access->delete_access,
            ];
        }

        return response()->json($result);
    }
}
