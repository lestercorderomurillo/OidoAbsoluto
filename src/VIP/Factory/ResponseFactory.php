<?php

namespace VIP\Factory;

use VIP\App\App;
use VIP\Controller\BaseController;
use VIP\HTTP\Server\Response\AbstractResponse;
use VIP\HTTP\Server\Response\Response;
use VIP\HTTP\Server\Response\View;

use function VIP\Core\Logger;

class ResponseFactory implements FactoryInterface
{
    public static function createError(int $code = 200, string $message = ""): AbstractResponse
    {
        BaseController::setSystemController();
        App::$app->setFailure();
        if ($message == "") {
            $message = AbstractResponse::CODES[$code];
        }

        if ($code >= 500) {
            Logger()->debug(
                "{0} responded with '{1}' : '{2}'",
                [
                    BaseController::getFQCurrentControllerName(),
                    $code,
                    $message
                ]
            );
        }
        return (new View("error", ["code" => "$code", "message" => "$message"]))->setStatusCode($code);
    }

    public static function create(string $string_input = "div", array $array_input = []): AbstractResponse
    {
        $html = HTMLFactory::create($string_input, $array_input)->render();
        return (new Response($html))->setStatusCode(200);
    }
}
