<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\SchoolSetting;

class InstallerController extends Controller
{
    public function index()
    {
        // Check if already installed
        $setting = SchoolSetting::first();
        if ($setting && $setting->is_installed) {
            return redirect('/')->with('error', 'System already installed.');
        }

        return view('installer.index');
    }

    public function install(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string',
            'school_email' => 'nullable|email',
            'admin_username' => 'required|string|unique:users,username',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // Seed roles
            $roles = ['Admin', 'Teacher', 'Accountant', 'Student'];
            foreach ($roles as $roleName) {
                Role::firstOrCreate(['role_name' => $roleName]);
            }

            // Create school settings
            SchoolSetting::create([
                'school_name' => $request->school_name,
                'school_address' => $request->school_address,
                'school_phone' => $request->school_phone,
                'school_email' => $request->school_email,
                'is_installed' => true,
            ]);

            // Create admin user
            $adminRole = Role::where('role_name', 'Admin')->first();
            User::create([
                'username' => $request->admin_username,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role_id' => $adminRole->id,
            ]);

            DB::commit();
            return redirect('/admin/login.php')->with('success', 'Installation completed. Please login as admin.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Installation failed: ' . $e->getMessage());
        }
    }
}