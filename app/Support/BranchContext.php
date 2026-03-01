<?php

namespace App\Support;

class BranchContext
{
    public static function activeBranchId(): ?int
    {
        $activeBranchId = session('active_branch_id');
        if (! empty($activeBranchId)) {
            return (int) $activeBranchId;
        }

        $userBranchId = auth()->user()?->branch_id;
        if (! empty($userBranchId)) {
            return (int) $userBranchId;
        }

        return null;
    }
}

