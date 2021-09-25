<?php

namespace Pipeline\Core\Container;

interface ContainerInterface
{
    public function get(string $id);
    public function set(string $id, $anything): ContainerInterface;
    public function has(string $id): bool;
    public function expose();
}
