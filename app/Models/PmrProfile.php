<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmrProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nisn_or_member_id',
        'class_grade',
        'availability_status',
        'is_on_duty',
        'current_latitude',
        'current_longitude',
        'last_location_updated_at',
    ];

    protected $casts = [
        'is_on_duty' => 'boolean',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'last_location_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAvailable(): bool
    {
        return $this->availability_status === 'available';
    }
}
