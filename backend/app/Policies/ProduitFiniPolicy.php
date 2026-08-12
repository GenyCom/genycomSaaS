<?php
namespace App\Policies;

use App\Models\ProduitFini;
use App\Models\User;

class ProduitFiniPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProduitFini $produitFini): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProduitFini $produitFini): bool
    {
        return true;
    }

    public function delete(User $user, ProduitFini $produitFini): bool
    {
        return $user->is_superadmin || $user->is_owner;
    }
}
