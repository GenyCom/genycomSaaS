<?php
namespace App\Policies;

use App\Models\Projet;
use App\Models\User;

class ProjetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Projet $projet): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Projet $projet): bool
    {
        return true;
    }

    public function delete(User $user, Projet $projet): bool
    {
        return $user->is_superadmin || $user->is_owner;
    }
}
