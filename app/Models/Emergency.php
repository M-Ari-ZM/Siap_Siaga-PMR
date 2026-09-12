<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emergency extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_code',
        'reporter_id',
        'location_id',
        'custom_location_name',
        'latitude',
        'longitude',
        'incident_type',
        'description',
        'photo_path',
        'status',
        'reported_at',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function assignments()
    {
        return $this->hasMany(EmergencyAssignment::class);
    }

    public function handlingReport()
    {
        return $this->hasOne(HandlingReport::class);
    }

    public function timelines()
    {
        return $this->hasMany(EmergencyTimeline::class)->orderBy('created_at', 'asc');
    }

    public function activeAssignment()
    {
        return $this->hasOne(EmergencyAssignment::class)->whereIn('status', ['accepted', 'offered']);
    }

    public function getLocationDisplayAttribute(): string
    {
        if ($this->location) {
            return $this->location->name . ($this->location->building ? ' (' . $this->location->building . ')' : '');
        }

        return $this->custom_location_name ?? 'Lokasi GPS/Manual';
    }
}
