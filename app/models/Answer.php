<?php

namespace App\Models;

use Cosmic\ORM\Abstracts\Model;

class Answer extends Model
{
    public int $question;
    public string $value;
}
