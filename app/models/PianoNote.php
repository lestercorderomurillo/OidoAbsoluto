<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class PianoNote extends Model
{
    public int $try;
    public int $noteIndex;
    public string $expectedNote;
    public string $selectedNote;
    public float $reactionTime;
}
