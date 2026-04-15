<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'setting_name',
        'setting_value',
        'description',
        'type',
    ];

    /**
     * Get setting value by name
     */
    public static function getSetting($name, $default = null)
    {
        $setting = self::where('setting_name', $name)->first();
        
        if (!$setting) {
            return $default;
        }

        // Convert value based on type
        if ($setting->type === 'boolean') {
            return filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($setting->type === 'integer') {
            return (int) $setting->setting_value;
        } elseif ($setting->type === 'json') {
            return json_decode($setting->setting_value, true);
        }

        return $setting->setting_value;
    }

    /**
     * Set setting value by name
     */
    public static function setSetting($name, $value, $type = 'string', $description = null)
    {
        if ($type === 'boolean') {
            $value = $value ? 'true' : 'false';
        } elseif ($type === 'json') {
            $value = json_encode($value);
        } else {
            $value = (string) $value;
        }

        return self::updateOrCreate(
            ['setting_name' => $name],
            [
                'setting_value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
