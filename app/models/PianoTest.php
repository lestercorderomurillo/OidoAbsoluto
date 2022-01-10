<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class PianoTest extends Model
{
    public int $try;
    public string $mode;
    public string $uploadDate;
    public string $totalTime;
}
