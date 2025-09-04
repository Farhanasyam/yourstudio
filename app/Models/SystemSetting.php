<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public'
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($setting->value) ? (float) $setting->value : $default;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    public static function set($key, $value, $type = 'text')
    {
        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}