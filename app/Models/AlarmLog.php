<?php
// app/Models/AlarmLog.php (tambahkan kolom baru)

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlarmLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'session_id',
        'trigger_source',
        'target_type',
        'target_id',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'event_time',
        'details',
        'remaining_seconds'
    ];
    
    protected $casts = [
        'event_time' => 'datetime',
        'details' => 'array',
        'old_data' => 'array',
        'new_data' => 'array'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function record($data)
    {
        return self::create(array_merge([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_time' => now(),
        ], $data));
    }
}