<?php

namespace Pipeline\PypeEngine\Inproc;

class Selection
{
    private $value;
    private $start;
    private $end;

    public function __construct($start_position, $end_position, $string_or_null = null)
    {
        $this->value = $string_or_null;
        $this->start = max($start_position, 0);
        $this->end = $end_position;
    }

    public function isValid(): bool
    {
        return ($this->end != false);
    }

    public function getString(): string
    {
        return $this->value;
    }

    public function &setStartPosition(int $position): Selection
    {
        $this->start = max($position, 0);
        return $this;
    }

    public function &setEndPosition(int $position): Selection
    {
        $this->end = $position;
        return $this;
    }

    public function &moveStartPosition(int $offset): Selection
    {
        $this->start += $offset;
        $this->start = max($this->start, 0);
        return $this;
    }

    public function &moveEndPosition(int $offset): Selection
    {
        $this->end += $offset;
        return $this;
    }

    public function getStartPosition(): int
    {
        return $this->start;
    }

    public function getEndPosition(): int
    {
        return $this->end;
    }

    public function getReducedString(): string
    {
        return trim(substr($this->value, $this->start, $this->end - $this->start));
    }
}
