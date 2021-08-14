<?php

namespace App\Models;

use VIP\Model\AbstractModel;

class User extends AbstractModel
{
    const table = "users";
    
    public string $email;
    public string $salt;
    public string $password;
    public string $token;
    public string $activated;
}
