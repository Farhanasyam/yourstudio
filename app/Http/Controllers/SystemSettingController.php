<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    public function index()
    {
        // Group settings by their group
        $settings = SystemSetting::all()->groupBy('group');
        
        return view('pages.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if ($setting) {
                // Handle boolean values from checkboxes
                if ($setting->type === 'boolean') {
                    $value = $value === 'on' || $value === '1' || $value === true;
                }
                
                // Handle number values
                if ($setting->type === 'number') {
                    $value = is_numeric($value) ? $value : 0;
                }
                
                $setting->value = $value;
                $setting->save();
                
                // Clear cache for this setting
                Cache::forget('setting.' . $key);
            }
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan');
    }

    public function getValue($key)
    {
        $setting = SystemSetting::where('key', $key)
            ->where('is_public', true)
            ->first();
            
        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }
        
        return response()->json([
            'key' => $setting->key,
            'value' => $setting->value,
            'type' => $setting->type
        ]);
    }
}
