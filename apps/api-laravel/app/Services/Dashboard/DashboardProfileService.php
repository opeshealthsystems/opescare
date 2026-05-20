<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\DashboardProfile;
use Illuminate\Support\Facades\Auth;

class DashboardProfileService
{
    public function profileForUser(User $user): ?DashboardProfile
    {
        return $user->role?->dashboardProfile;
    }

    public function landingUrlForUser(User $user): string
    {
        $profile = $this->profileForUser($user);

        if ($profile) {
            return $profile->landingUrl();
        }

        return url('/portals/patient');
    }

    public function landingUrlForCurrent(): string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return route('login');
        }

        return $this->landingUrlForUser($user);
    }

    public function portalPrefixForUser(User $user): string
    {
        return $this->profileForUser($user)?->portal_prefix ?? 'patient';
    }
}
