<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{
    public function index(){
        $users = User::all();
        return view("home", compact('users'));
    }

    // public function showusers(){
    //     return();
    // }

    public function createAdminPage(){
        return view("createAdminPage");
    }

    public function storeAdmin(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Redirect or return response
        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function loginAdmin(){
        return view("loginAdmin");
    }

    public function login(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to log the user in
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication passed...
            return redirect()->intended('/home');
        }

        // Authentication failed...
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/loginAdmin');
    }

    public function usersPage(){
        $users = User::all();
        return view("pages.usersPage", compact('users'));
    }

    public function archives(){
        return view("pages.archives");
    }

    public function logs(){
        return view("pages.logs");
    }

    public function sidebar(){
        return view("sidebar");
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('usersPage')->with('success', 'User deleted successfully');
    }

    public function updateRole(Request $request, User $user)
    {
        // Validate the request
        $request->validate([
            'role' => 'required|in:super_admin,admin,streamer', // Update according to your roles
        ]);

        // Check if the authenticated user is a super admin
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }

        // Update the user's role
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'User role updated successfully.');
    }
}
