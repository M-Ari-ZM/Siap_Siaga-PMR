<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyTimeline extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'emergency_id',
        'actor_id',
        'status',
        'title',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
