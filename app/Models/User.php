<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'unique_id',
        'pin',
    ];

    protected $hidden = [
        'password',
        'pin',
    ];

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Generate unique_id 2 digit yang unik jika belum ada
            if (empty($user->unique_id)) {
                do {
                    $uniqueId = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
                } while (self::where('unique_id', $uniqueId)->exists());
                
                $user->unique_id = $uniqueId;
            }

            // Generate PIN random 6 digit jika belum ada
            if (empty($user->pin)) {
                $user->pin = sprintf("%06d", mt_rand(1, 999999));
            }
        });
    }
}