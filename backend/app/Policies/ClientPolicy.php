<?php
namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Tout utilisateur authentifié du tenant peut voir la liste.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tout utilisateur authentifié du tenant peut voir un client.
     */
    public function view(User $user, Client $client): bool
    {
        return true;
    }

    /**
     * Tout utilisateur authentifié peut créer un client.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Tout utilisateur authentifié peut modifier un client.
     */
    public function update(User $user, Client $client): bool
    {
        return true;
    }

    /**
     * Seuls les admins/propriétaires peuvent supprimer un client.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->is_superadmin || $user->is_owner;
    }
}
