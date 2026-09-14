<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_induk',
        'name',
        'email',
        'phone_number',
        'role',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isPmr(): bool
    {
        return $this->role === 'pmr';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function pmrProfile()
    {
        return $this->hasOne(PmrProfile::class);
    }

    public function reportedEmergencies()
    {
        return $this->hasMany(Emergency::class, 'reporter_id');
    }

    public function pmrAssignments()
    {
        return $this->hasMany(EmergencyAssignment::class, 'pmr_user_id');
    }

    public function handlingReports()
    {
        return $this->hasMany(HandlingReport::class, 'pmr_user_id');
    }
}
