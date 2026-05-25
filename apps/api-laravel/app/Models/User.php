<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use \App\Traits\IsDemoRecord;
    use HasFactory, Notifiable, HasUuids;

    // role_id and is_demo are intentionally excluded from $fillable.
    // Assign roles via: $user->role_id = $roleId; $user->save();
    // Toggle demo via: $user->forceFill(['is_demo' => true])->save();
    protected $fillable = [
        'name',
        'email',
        'password',
        'primary_facility_id',
        'patient_id',
        'status',
        // NOT: role_id, is_demo
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class);
    }

    public function primaryFacility()
    {
        return $this->belongsTo(Facility::class, 'primary_facility_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roleName(): ?string
    {
        return $this->role?->name;
    }

    public function dashboardProfile(): ?\App\Models\DashboardProfile
    {
        return $this->role?->dashboardProfile;
    }

    public function facilityRoleAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FacilityRoleAssignment::class);
    }

    /**
     * Get the role for this user at a specific facility.
     * Falls back to global role_id for backward compatibility.
     */
    public function roleAtFacility(string $facilityId): ?Role
    {
        $assignment = $this->facilityRoleAssignments()
            ->active()
            ->where('facility_id', $facilityId)
            ->with('role')
            ->first();

        if ($assignment) {
            return $assignment->role;
        }

        // Backward compatibility fallback
        return $this->role ?? null;
    }
}
