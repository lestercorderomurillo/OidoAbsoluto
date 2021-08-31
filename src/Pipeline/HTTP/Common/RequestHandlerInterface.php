<?php

namespace Pipeline\HTTP\Common;

use Pipeline\HTTP\Message;
use Pipeline\Core\Types\JSON;

interface RequestHandlerInterface
{
    public function handle($anything): Message;
    public function JSON(JSON $json): Message;
    public function redirect(string $new_path): Message;
}
