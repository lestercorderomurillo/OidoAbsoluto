<?php

namespace App\ViewModels;

use Cosmic\ORM\Abstracts\Model;

class PianoTestViewModel extends Model
{
    public int $try;
    public string $displayMode;
    public string $uploadDate;
    public string $totalTime;
    public string $token;
}
