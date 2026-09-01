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

    /**
     * Where to send this user after sign-in.
     *
     * Must never return a portal that the V1 launch-scope freeze has switched
     * off. EnforceFeatureFlag 404s those paths — deliberately and
     * byte-identically to a nonexistent route — so returning one here means the
     * user completes a successful login and lands on a dead page. That is
     * exactly what happened to every insurance account.
     */
    public function landingUrlForUser(User $user): string
    {
        $profile = $this->profileForUser($user);

        $url = $profile ? $profile->landingUrl() : url('/portals/patient');

        return \App\Support\Features::pathIsFrozen($url)
            ? route('portal.unavailable')
            : $url;
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
