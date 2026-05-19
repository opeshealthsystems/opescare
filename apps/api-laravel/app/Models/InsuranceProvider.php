<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InsuranceProvider extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'country_code',
        'contact_email',
        'contact_phone',
        'portal_url',
        'api_endpoint',
        'status',
    ];

    public function plans()
    {
        return $this->hasMany(InsurancePlan::class);
    }

    public function activePlans()
    {
        return $this->hasMany(InsurancePlan::class)->where('status', 'active');
    }
}
