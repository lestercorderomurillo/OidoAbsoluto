<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class User extends Model
{
    public string $email;
    public string $salt;
    public string $password;
    public string $activated;
    public string $role;
    public string $token;
}
