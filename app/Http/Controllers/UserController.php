<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Display a paginated listing of the users.
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    // Show the form for creating a new user.
    public function create()
    {
        return view('users.create');
    }

    // Store a newly created user in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'login' => 'sometimes|required|unique:users,login',
            'password' => 'sometimes|required',
            'role' => 'sometimes|required',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

   
    // Show the form for editing the specified user.
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    // Update the specified user in storage.
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'login' => 'sometimes|required|unique:users,login,' . $id,
            'password' => 'sometimes',
            'role' => 'sometimes|required',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        if (!$validated['password']) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.edit', $user->id)->with('success', 'Пользователь обновлен.');
    }

    // Remove the specified user from storage.
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->role == 'admin') {
            return redirect()->route('users.index')->with('error', 'Нельзя удалить администратора.');
        }
        $user->delete();

        return redirect()->back()->with('success', 'Пользователь удален.');
    }
}
