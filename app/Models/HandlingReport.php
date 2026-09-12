<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandlingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_id',
        'pmr_user_id',
        'action_taken',
        'notes',
        'documentation_photo',
        'final_condition',
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
