<?php

namespace App\Models;

use App\Models\Central\Auth\Authentication\CentralUser;

/**
 * This class is a bridge for Laravel's default User model.
 * It extends CentralUser to maintain compatibility with packages and tests
 * that expect App\Models\User.
 */
class User extends CentralUser
{
    //
}
