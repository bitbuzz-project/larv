<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    use HasFactory;

    protected $fillable = [
        'apoL_a01_code',
        'default_name',
        'note',
        'prof',
        'groupe',
        'class',
        'info',
        'Semestre',
        'status',
        'reclamation_type',
        'category',
        'priority',
        'admin_comment',
        'session_type',
        'result_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for status
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REJECTED = 'rejected';

    // Constants for type
    const TYPE_NOTES = 'notes';
    const TYPE_CORRECTION = 'correction';
    const TYPE_AUTRE = 'autre';

    // Constants for priority
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'apoL_a01_code', 'apoL_a01_code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('reclamation_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_RESOLVED => 'Résolue',
            self::STATUS_REJECTED => 'Rejetée',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_NOTES => 'Notes',
            self::TYPE_CORRECTION => 'Correction',
            self::TYPE_AUTRE => 'Autre',
        ];

        return $labels[$this->reclamation_type] ?? $this->reclamation_type;
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Basse',
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_HIGH => 'Haute',
            self::PRIORITY_URGENT => 'Urgente',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    public function getIsRecentAttribute()
    {
        return $this->created_at >= now()->subHours(24);
    }
}
