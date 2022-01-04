<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class PianoNote extends Model
{
    public int $try;
    public string $expectedNote;
    public string $playedNote;
    public float $responseTime;
}
