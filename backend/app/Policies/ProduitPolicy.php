<?php
namespace App\Policies;

use App\Models\Produit;
use App\Models\User;

class ProduitPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Produit $produit): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Produit $produit): bool
    {
        return true;
    }

    public function delete(User $user, Produit $produit): bool
    {
        return $user->is_superadmin || $user->is_owner;
    }
}
