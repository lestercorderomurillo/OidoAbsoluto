<?php

namespace Pipeline\Pype\Component;

interface DOMInterface
{
    public static function create(string $string_input, array $array_input = []): DOMInterface;
    public function __construct(string $tag, array $attributes = []);
    public function render(): string;
}
