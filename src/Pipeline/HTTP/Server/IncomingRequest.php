<?php

namespace Pipeline\HTTP\Server;

use Pipeline\HTTP\Common\Request;
use Pipeline\Utilities\Text;

class IncomingRequest extends Request
{
    public function __construct()
    {
        $protocol = "http";
        if (isset($_SERVER['HTTPS'])) {
            $protocol = "https";
        }

        $url = "$protocol://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        $this->protocol = parse_url($url, PHP_URL_SCHEME);
        $this->host = $_SERVER["HTTP_HOST"];
        $this->user = parse_url($url, PHP_URL_USER);
        $this->pass = parse_url($url, PHP_URL_PASS);
        $this->path = explode("?", $_SERVER["REQUEST_URI"], 2)[0];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->parameters = [];

        if ($this->method == "GET") {
            foreach ($_GET as $parameter => $value) {
                $this->parameters["$parameter"] = Text::sanitizeString($value);
            }
        } else if ($this->method == "POST") {
            foreach ($_POST as $parameter => $value) {
                $this->parameters["$parameter"] = Text::sanitizeString($value);
            }
        }
    }
}
