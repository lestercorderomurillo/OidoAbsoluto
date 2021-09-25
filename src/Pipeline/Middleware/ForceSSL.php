<?php

namespace Pipeline\Middleware;

use Pipeline\HTTP\Message;

class ForceSSL extends Middleware
{
    public function handle($request): Message
    {
        $new_path =  $request->getPath();
        $new_path = substr($new_path, 1);

        if ($request->getProtocol() != "https") {
            return $this->redirect($new_path);
        }

        /* Forward request */
        return $request;
    }
}
