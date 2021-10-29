<?php

namespace App\Models;

use Pipeline\Core\Boot\ModelBase;

class Questio extends ModelBase
{
    const table = "questions";
    
    public string $email;
    public string $salt;
}
