<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Incident extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'location',
        'images',
        'status',
        'reported_at',
        'resolved_at',
        'resolution_notes',
        'alarm_session_id', // Tambahkan jika ada
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'images' => 'array',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========== SCOPES ==========
    
    /**
     * Scope untuk incident active
     */
    public function scopeActive($query)
    {
        $now = now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);
        
        Log::debug('Active scope check:', [
            'now' => $now->toDateTimeString(),
            '24_hours_ago' => $twentyFourHoursAgo->toDateTimeString()
        ]);
        
        return $query->where('status', 'ACTIVE')
            ->where('reported_at', '>=', $twentyFourHoursAgo);
    }

    /**
     * Scope untuk incident resolved
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'RESOLVED');
    }

    /**
     * Scope untuk false alarm
     */
    public function scopeFalseAlarm($query)
    {
        return $query->where('status', 'FALSE_ALARM');
    }

    /**
     * Scope untuk incident berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========== STATIC METHODS ==========
    
    /**
     * Check if there is any active incident
     */
    public static function hasActive(): bool
    {
        return self::where('status', 'ACTIVE')->exists();
    }

    /**
     * Check if there is any active incident for specific user
     */
    public static function hasActiveForUser($userId): bool
    {
        return self::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->exists();
    }

    /**
     * Check if there is any pending incident
     */
    public static function hasPending(): bool
    {
        return self::where('status', 'PENDING')->exists();
    }

    /**
     * Get active incident for current user
     */
    public static function getActiveForUser($userId = null)
    {
        $query = self::where('status', 'ACTIVE');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->first();
    }

    /**
     * Get all active incidents
     */
    public static function getAllActive()
    {
        return self::where('status', 'ACTIVE')->get();
    }

    /**
     * Count active incidents
     */
    public static function countActive(): int
    {
        return self::where('status', 'ACTIVE')->count();
    }

    // ========== IMAGE METHODS ==========
    
    /**
     * Get image URLs as array
     */
    public function getImageUrlsAttribute()
    {
        if (!$this->images) {
            return [];
        }

        return collect($this->images)->map(function ($image) {
            return asset('storage/incidents/' . $image);
        })->toArray();
    }

    /**
     * Store images for incident
     */
    public function storeImages(array $files)
    {
        $storedImages = [];
        
        foreach ($files as $file) {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/incidents', $fileName);
            $storedImages[] = $fileName;
        }
        
        $this->images = $storedImages;
        $this->save();
        
        return $storedImages;
    }

    /**
     * Delete all images for incident
     */
    public function deleteImages()
    {
        if ($this->images) {
            foreach ($this->images as $image) {
                $path = storage_path('app/public/incidents/' . $image);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
        $this->images = null;
        $this->save();
    }

    // ========== STATUS CHECK METHODS ==========
    
    /**
     * Check if incident is pending (active)
     */
    public function isPending(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if incident is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'RESOLVED';
    }

    /**
     * Check if incident is false alarm
     */
    public function isFalseAlarm(): bool
    {
        return $this->status === 'FALSE_ALARM';
    }

    /**
     * Check if incident is still active (not expired)
     */
    public function isStillActive(): bool
    {
        if ($this->status !== 'ACTIVE') {
            return false;
        }
        
        $twentyFourHoursAgo = now()->subHours(24);
        return $this->reported_at >= $twentyFourHoursAgo;
    }

    // ========== LABEL METHODS ==========
    
    /**
     * Get incident type label
     */
    public function getTypeLabel(): string
    {
        $labels = [
            'KEBAKARAN' => 'Kebakaran',
            'PENCURIAN' => 'Pencurian',
            'GEMPA_BUMI' => 'Gempa Bumi',
            'BANJIR' => 'Banjir',
            'KECELAKAAN' => 'Kecelakaan',
            'PENYERANGAN' => 'Penyerangan',
            'GANGGUAN_KEAMANAN' => 'Gangguan Keamanan',
            'LAINNYA' => 'Lainnya',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'ACTIVE' => 'Active/Pending',
            'RESOLVED' => 'Selesai',
            'FALSE_ALARM' => 'False Alarm',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class untuk frontend
     */
    public function getStatusBadgeClass(): string
    {
        $classes = [
            'ACTIVE' => 'status-pending',
            'RESOLVED' => 'status-resolved',
            'FALSE_ALARM' => 'status-false',
        ];

        return $classes[$this->status] ?? 'status-pending';
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClass(): string
    {
        $classes = [
            'KEBAKARAN' => 'bg-red-50 text-red-700',
            'PENCURIAN' => 'bg-purple-50 text-purple-700',
            'GEMPA_BUMI' => 'bg-orange-50 text-orange-700',
            'BANJIR' => 'bg-blue-50 text-blue-700',
            'KECELAKAAN' => 'bg-yellow-50 text-yellow-700',
            'PENYERANGAN' => 'bg-red-100 text-red-800',
            'GANGGUAN_KEAMANAN' => 'bg-indigo-50 text-indigo-700',
            'LAINNYA' => 'bg-gray-50 text-gray-700',
        ];

        return $classes[$this->type] ?? 'bg-gray-50 text-gray-700';
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        $icons = [
            'KEBAKARAN' => 'fa-fire',
            'PENCURIAN' => 'fa-user-secret',
            'GEMPA_BUMI' => 'fa-mountain',
            'BANJIR' => 'fa-water',
            'KECELAKAAN' => 'fa-car-crash',
            'PENYERANGAN' => 'fa-fist-raised',
            'GANGGUAN_KEAMANAN' => 'fa-shield-alt',
            'LAINNYA' => 'fa-ellipsis-h',
        ];

        return $icons[$this->type] ?? 'fa-exclamation-triangle';
    }

    /**
     * Get status icon
     */
    public function getStatusIcon(): string
    {
        $icons = [
            'ACTIVE' => 'fa-clock',
            'RESOLVED' => 'fa-check-circle',
            'FALSE_ALARM' => 'fa-times-circle',
        ];

        return $icons[$this->status] ?? 'fa-clock';
    }

    // ========== FORMATTING METHODS ==========
    
    /**
     * Get formatted reported date
     */
    public function getFormattedReportedAt(): string
    {
        if (!$this->reported_at) {
            return '-';
        }
        
        return $this->reported_at->format('d M Y H:i:s');
    }

    /**
     * Get formatted resolved date
     */
    public function getFormattedResolvedAt(): string
    {
        if (!$this->resolved_at) {
            return '-';
        }
        
        return $this->resolved_at->format('d M Y H:i:s');
    }

    /**
     * Get time ago for reported date
     */
    public function getReportedTimeAgo(): string
    {
        if (!$this->reported_at) {
            return '-';
        }
        
        return $this->reported_at->diffForHumans();
    }

    // ========== ACTION METHODS ==========
    
    /**
     * Resolve incident
     */
    public function resolve(string $notes = null): bool
    {
        $this->status = 'RESOLVED';
        $this->resolved_at = now();
        $this->resolution_notes = $notes;
        
        return $this->save();
    }

    /**
     * Mark as false alarm
     */
    public function markAsFalseAlarm(string $notes = null): bool
    {
        $this->status = 'FALSE_ALARM';
        $this->resolved_at = now();
        $this->resolution_notes = $notes;
        
        return $this->save();
    }

    /**
     * Reactivate incident
     */
    public function reactivate(): bool
    {
        $this->status = 'ACTIVE';
        $this->resolved_at = null;
        
        return $this->save();
    }

    // ========== BOOT METHODS ==========
    
    protected static function boot()
    {
        parent::boot();
        
        // Set default reported_at saat create
        static::creating(function ($incident) {
            if (!$incident->reported_at) {
                $incident->reported_at = now();
            }
            if (!$incident->status) {
                $incident->status = 'ACTIVE';
            }
        });
    }
}