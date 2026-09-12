<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_id',
        'pmr_user_id',
        'status',
        'distance_meters',
        'dispatch_score',
        'offered_at',
        'responded_at',
    ];

    protected $casts = [
        'distance_meters' => 'integer',
        'dispatch_score' => 'float',
        'offered_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }

    public function pmrUser()
    {
        return $this->belongsTo(User::class, 'pmr_user_id');
    }
}
