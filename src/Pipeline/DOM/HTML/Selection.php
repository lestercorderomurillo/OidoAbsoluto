<?php

namespace Pipeline\DOM\HTML;

class Selection
{
    private string $value;
    private int $start;
    private int $end;

    public function __construct(int $start_position, int $end_position, string $value = null)
    {
        $this->value = $value;
        $this->start = max($start_position, 0);
        $this->end = min($end_position, strlen($value));
    }

    public function isValid(): bool
    {
        return ($this->end != false);
    }

    public function getString(): string
    {
        return trim(substr($this->value, $this->start, $this->end - $this->start));
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

    public function &moveStartPosition(int $offset = 1): Selection
    {
        $this->start += $offset;
        $this->start = max($this->start, 0);
        return $this;
    }

    public function &moveEndPosition(int $offset = 1): Selection
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
}
