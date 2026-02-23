<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class SettingController extends Controller
{
    public function index()
    {
        $setting = GeneralSetting::first();
        return view('backend.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'currency_name' => ['required', 'string', 'max:20'],
            'currency_icon' => ['required', 'string', 'max:10'],
        ]);

        GeneralSetting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => $request->site_name,
                'contact_email' => $request->contact_email,
                'address' => $request->address,
                'base_currency_name' => $request->currency_name,
                'base_currency_icon' => $request->currency_icon,
                'currency_name' => $request->currency_name,
                'currency_icon' => $request->currency_icon,
                'currency_rate' => 1.0, // Internal rate is always 1 for the System/Base currency
            ]
        );

        app('toastr')->success('Settings updated successfully!');
        return redirect()->back();
    }
}
