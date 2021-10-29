<?php

namespace App\Models;

use Pipeline\Core\Boot\ModelBase;

class Note extends ModelBase
{
    const table = "note";
    
    public string $email;
    public string $salt;
}
