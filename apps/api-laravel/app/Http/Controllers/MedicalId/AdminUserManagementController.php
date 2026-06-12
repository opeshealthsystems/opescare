<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserManagementController extends Controller
{
    private function actorId(): string
    {
        return Auth::id() ?? session('auth_email', 'system');
    }

    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $query = User::with('role');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('name')->paginate(25)->withQueryString();
        $roles = Role::orderBy('name')->get();

        $stats = [
            'total'     => User::count(),
            'active'    => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'pending'   => User::where('status', 'pending')->count(),
        ];

        return view('portals.admin.users.index', compact('users', 'roles', 'stats'));
    }

    public function show(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = User::with(['role', 'facilityRoleAssignments', 'primaryFacility'])->findOrFail($id);

        return view('portals.admin.users.show', compact('user'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $roleId = $validated['role_id'];

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status'   => 'active',
        ]);

        $user->role_id = $roleId;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'status'  => 'required|string',
        ]);

        $user->name   = $validated['name'];
        $user->email  = $validated['email'];
        $user->status = $validated['status'];
        $user->role_id = $validated['role_id'];
        $user->save();

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function suspend(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = User::findOrFail($id);
        $user->status = 'suspended';
        $user->save();

        return redirect()->back()->with('success', 'User suspended.');
    }

    public function activate(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();

        return redirect()->back()->with('success', 'User activated.');
    }

    public function resetPassword(Request $request, string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully.');
    }

    public function destroy(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminCount = User::where('role_id', $superAdminRole->id)->count();
            if ($superAdminCount <= 1 && $user->role_id === $superAdminRole->id) {
                return redirect()->back()->with('error', 'Cannot delete the last super admin.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
