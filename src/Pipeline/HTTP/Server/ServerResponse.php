<?php

namespace Pipeline\HTTP\Server;

use Pipeline\HTTP\Message;

class ServerResponse extends Message
{
    public function __construct(int $status_code = 200, string $protocol = "1.1")
    {
        parent::__construct($status_code, $protocol);
    }

    public function sendAndDiscard()
    {
        $this->send();
        exit();
    }

    public function send()
    {
        header("HTTP/$this->protocol: $this->status_code" . Message::CODES[$this->status_code]);

        foreach ($this->headers as $key => $values) {

            foreach ($values as $value) {
                header("$key: $value", false);
            }
        }

        if ($this->getBody() != "") {
            echo ($this->getBody());
        }
    }
}
