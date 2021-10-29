<?php

namespace App\Models;

use Pipeline\Core\Boot\ModelBase;

class User extends ModelBase
{
    const table = "users";
    
    public string $email;
    public string $salt;
    public string $password;
    public string $token;
    public string $activated;
    public string $role;
}
