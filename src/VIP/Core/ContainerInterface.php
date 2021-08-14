<?php

namespace VIP\Core;

interface ContainerInterface
{
    public function get(string $id);
    public function set(string $id, $anything): void;
    public function has(string $id): bool;
    public function expose(): array;
}
