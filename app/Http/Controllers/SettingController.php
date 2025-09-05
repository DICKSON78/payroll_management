<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first() ?? ['company_name' => 'Default Company', 'currency' => 'TZS', 'tax_rate' => 30.00, 'payroll_cycle' => 'Monthly', 'company_logo' => null];
        return view('dashboard.setting', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0',
            'payroll_cycle' => 'required|in:Monthly,Bi-Weekly,Weekly',
            'currency' => 'required|string|max:10',
            'company_logo' => 'nullable|image|mimes:jpg,png|max:2048',
        ]);

        $settings = Setting::firstOrCreate([]);
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('public/logos');
            $validated['company_logo'] = str_replace('public/', '', $path);
        }
        $settings->update($validated);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
