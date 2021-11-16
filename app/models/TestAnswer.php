<?php

namespace App\Models;

use Pipeline\Core\Boot\Model;

class Questio extends Model
{
    const table = "questions";
    
    public string $email;
    public string $salt;
}
