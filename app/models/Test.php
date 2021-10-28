<?php

namespace App\Models;

use Pipeline\Core\Model;

class Note extends Model
{
    const table = "note";
    
    public string $email;
    public string $salt;
}
