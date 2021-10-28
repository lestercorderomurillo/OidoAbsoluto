<?php

namespace Pipeline\HTTP\Server;

class WebServer
{    
    private Router $router;
    private Session $session;
    private IncomingRequest $request;

    public function __construct()
    {
        $this->router = new Router();
        $this->session = new Session();
        $this->request = new IncomingRequest();
    }

    public function run()
    {
        $this->router->handle($this->request);
    }

    public function getActiveSession(): Session
    {
        return $this->session;
    }
}
