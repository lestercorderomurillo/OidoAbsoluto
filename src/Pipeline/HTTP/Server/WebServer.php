<?php

namespace Pipeline\HTTP\Server;

class WebServer
{    
    private URIRouter $router;
    private Session $session;
    private IncomingRequest $request;

    public function __construct()
    {
        Session::__initialize();
        $this->router = new URIRouter();
        $this->session = new Session();
        $this->request = new IncomingRequest();
    }

    public function run()
    {
        $this->router->handle($this->request);
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
