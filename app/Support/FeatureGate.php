<?php

namespace App\Support;

use App\Models\User;

class FeatureGate
{
    public function allows(string $feature, ?User $user = null): bool
    {
        $features = config('feature_gate.features', []);
        if (!array_key_exists($feature, $features)) {
            return false;
        }

        $default = (bool) $features[$feature];
        $user = $user ?: auth()->user();

        if (!$user) {
            return $default;
        }

        if ($this->hasBypassRole($user)) {
            return true;
        }

        $override = $this->resolveRoleOverride($user, $feature);
        if ($override !== null) {
            return $override;
        }

        return $default;
    }

    public function denies(string $feature, ?User $user = null): bool
    {
        return !$this->allows($feature, $user);
    }

    protected function hasBypassRole(User $user): bool
    {
        $slugs = config('feature_gate.bypass_role_slugs', []);
        if (empty($slugs)) {
            return false;
        }

        return $user->roles()->whereIn('slug', $slugs)->exists();
    }

    protected function resolveRoleOverride(User $user, string $feature): ?bool
    {
        $overrides = config('feature_gate.role_overrides', []);
        if (!is_array($overrides) || empty($overrides)) {
            return null;
        }

        $roleSlugs = $user->roles()->pluck('slug')->filter()->values()->all();
        foreach ($roleSlugs as $slug) {
            $roleMap = $overrides[$slug] ?? null;
            if (!is_array($roleMap)) {
                continue;
            }

            if (array_key_exists($feature, $roleMap)) {
                return (bool) $roleMap[$feature];
            }

            if (array_key_exists('*', $roleMap)) {
                return (bool) $roleMap['*'];
            }
        }

        return null;
    }
}

