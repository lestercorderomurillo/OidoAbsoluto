<?php

namespace App\Models;

use Pipeline\Model\Model;

class Note extends Model
{
    const table = "note";
    
    public string $email;
    public string $salt;
}
