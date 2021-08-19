<?php

namespace App\Models;

use Pipeline\Model\Model;

class User extends Model
{
    const table = "users";
    
    public string $email;
    public string $salt;
    public string $password;
    public string $token;
    public string $activated;
}
