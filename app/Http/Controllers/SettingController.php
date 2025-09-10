<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Show the credentials form
     */
    public function index()
    {
        // Angalia kama mtumiaji ame-login na ana role ya 'admin'
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // Kama si admin, mpeleke kwenye ukurasa mwingine na umpe ujumbe wa kosa.
            return redirect('/home')->with('error', 'Huruhusiwi kufikia ukurasa huu!');
        }

        $user = Auth::user();
        return view('dashboard.setting', compact('user'));
    }

    /**
     * Update admin credentials
     */
    public function update(Request $request)
    {
        // Angalia kama mtumiaji ame-login na ana role ya 'admin'
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // Kama si admin, mpeleke kwenye ukurasa mwingine.
            return redirect('/home')->with('error', 'Huruhusiwi kufanya mabadiliko haya!');
        }

        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Credentials updated successfully.');
    }
}