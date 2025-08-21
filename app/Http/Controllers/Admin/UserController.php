<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('projects')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('admin.users.form', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'projects' => 'nullable|array',
            'can_upload' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'can_upload' => $request->has('can_upload'), // ✅ checkbox handling
        ]);

        if ($request->projects) {
            $user->projects()->sync($request->projects);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $projects = Project::all();
        return view('admin.users.form', compact('user', 'projects'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'nullable|string|min:6',
            'projects' => 'nullable|array',
            'can_upload' => 'sometimes|boolean',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->can_upload = $request->has('can_upload');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->projects()->sync($request->projects ?? []);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }


        public function destroy(User $user)
        {
            $user->projects()->detach();
            $user->delete();

            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
        }
    }
