<?php

namespace Cosmic\HPHP\Engine;

use Cosmic\Traits\StringableTrait;
use Cosmic\HPHP\Interfaces\SelectionInterface;

class Selection implements SelectionInterface
{
    use StringableTrait;

    /**
     * @var string $input The original input without replacements.
     */
    protected string $input;

    /**
     * @var int $fp The start position of the selection.
     */
    protected int $fp;

    /**
     * @var int|false $lp The end position of the selection.
     */
    protected $lp = false;

    /**
     * Use to indicate a selector without end position.
     */
    const EOS = -1;

    /**
     * __construct
     *
     * @param  mixed $input
     * @param  mixed $fp
     * @param  mixed $lp
     * @return void
     */
    public function __construct(string $input, int $fp, $lp)
    {
        $this->input = $input;

        $this->fp = max($fp, 0);

        if ($lp == static::EOS) {
            $this->lp = strlen($input);
        } else if ($lp != false) {
            $this->lp = min($lp, strlen($input));
        }
    }

    /**
     * @inheritdoc
     */
    public function getFirstPosition(): int
    {
        return $this->fp;
    }

    /**
     * @inheritdoc
     */
    public function &getFirstPositionHandler()
    {
        return $this->fp;
    }

    /**
     * @inheritdoc
     */
    public function getLastPosition()
    {
        return $this->lp;
    }

    /**
     * @inheritdoc
     */
    public function &getLastPositionHandler()
    {
        return $this->lp;
    }

    /**
     * @inheritdoc
     */
    public function getParentString(): string
    {
        return $this->input;
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return ($this->lp != false || $this->lp == static::EOS);
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        if ($this->isValid()) {
            return substr($this->input, $this->fp, $this->lp - $this->fp);
        } else {
            return '{INVALID-SELECTION: NO END}';
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
