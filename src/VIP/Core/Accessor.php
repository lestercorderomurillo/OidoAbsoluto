<?php

namespace VIP\Core;

use VIP\App\App;
use VIP\HTTP\Common\Session;

function Services(string $service_name)
{
    return App::$app->getServices()->getContainer()->get($service_name);
}

function Session(): Session
{
    return App::$app->getSession();
}
