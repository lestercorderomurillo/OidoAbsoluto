<?php

namespace App\Models;

use Cosmic\ORM\Abstracts\Model;

class PianoTest extends Model
{
    public int $try;
    public string $mode;
    public string $uploadDate;
    public string $totalTime;
}
