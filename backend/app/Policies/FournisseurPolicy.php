<?php
namespace App\Policies;

use App\Models\Fournisseur;
use App\Models\User;

class FournisseurPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Fournisseur $fournisseur): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Fournisseur $fournisseur): bool
    {
        return true;
    }

    public function delete(User $user, Fournisseur $fournisseur): bool
    {
        return $user->is_superadmin || $user->is_owner;
    }
}
