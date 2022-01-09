<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class Answer extends Model
{
    public int $question;
    public string $value;
}
