<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'slug', 'billing_cycle', 'price_kobo', 'currency',
        'description', 'features', 'max_facilities', 'max_staff',
        'max_patients_per_month', 'is_active', 'is_public', 'trial_days',
        'sort_order', 'created_by',
    ];

    protected $casts = [
        'features'                 => 'array',
        'is_active'                => 'boolean',
        'is_public'                => 'boolean',
        'price_kobo'               => 'integer',
        'max_facilities'           => 'integer',
        'max_staff'                => 'integer',
        'max_patients_per_month'   => 'integer',
        'trial_days'               => 'integer',
        'sort_order'               => 'integer',
    ];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class, 'plan_id');
    }

    /** Price in major currency unit (XAF / FCFA) */
    public function priceFormatted(): string
    {
        $amount = $this->price_kobo / 100;
        return $this->currency . ' ' . number_format($amount, 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function hasFeature(string $featureKey): bool
    {
        return $this->planFeatures()->where('feature_key', $featureKey)->exists();
    }
}
