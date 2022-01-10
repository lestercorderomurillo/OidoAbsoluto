<?php

namespace App\ViewModels;

use Cosmic\ORM\Bootstrap\Model;

class DetailedTestViewModel extends Model
{
    /** @var PianoNote[] $notes */
    public array $notes;

    public string $author;

    public int $try;
    public string $displayString;
    public string $uploadDate;
    public string $totalTime;
    
    public int $totalNotes = 0;
    public int $totalMatches = 0;

    public int $totalPianoNotes = 0;
    public int $totalPianoMatches = 0;

    public int $totalSinNotes = 0;
    public int $totalSinMatches = 0;

    public int $totalNaturalNotes = 0;
    public int $totalNaturalMatches = 0;

    public int $totalSosNotes = 0;
    public int $totalSosMatches = 0;
}
