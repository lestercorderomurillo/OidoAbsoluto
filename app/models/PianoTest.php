<?php

namespace App\Models;

use Cosmic\ORM\Bootstrap\Model;

class PianoTest extends Model
{
    public int $try;
    public int $type;
    public string $startDate;
    public string $endDate;
}
