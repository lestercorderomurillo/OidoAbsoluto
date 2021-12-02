<?php

use Cosmic\HTTP\Server\Router;
use Cosmic\Prefabs\Middleware\Authentication;
/*
class Routes
{
    private function registerRoutes()
    {*/
        /* HomeController Routes */
        Router::get("/", HomeController::class, "login");
        Router::get("/index", HomeController::class, "login");
        Router::get("/login", HomeController::class, "login");
        Router::post("/login/submit", HomeController::class, "loginSubmit", ["email", "password"]);
        Router::get("/signup", HomeController::class, "signup");

        $values = ["first_name", "last_name", "email", "password", "confirm_password", "country", "birth_day", "phone", "gender"];
        Router::post("/signup/submit", HomeController::class, "signupSubmit", $values);
        Router::get("/forgot", HomeController::class, "resetRequest");
        Router::post("/forgot/submit", HomeController::class, "resetRequestSubmit");
        Router::get("/newpass", HomeController::class, "resetPassword", ["token"]);
        Router::post("/newpass/submit", HomeController::class, "resetPasswordSubmit");

        /* UserController Routes */
        Router::get("/test/audio", UserController::class, "hearingTest", ["mode"]);
        Router::post("/test/audio/submit", UserController::class, "submitHearingTest", ["mode", "expected_notes", "selected_notes"]);
        Router::get("/test/questions", UserController::class, "questionsTest");
        Router::get("/test/questions/submit", UserController::class, "questionsTest", ["mode"]);
        Router::get("/test/result", UserController::class, "testResult", ["id"]);
        Router::get("/profile", UserController::class, "profile")->setMiddlewares(Authentication::class);

        /* AdminstratorController Routes */
        Router::get("/admin/overview", AdminstratorController::class, "overview");

        /* DeveloperController Routes */
        Router::get("/testMethod1", DeveloperController::class, "testMethod1");
        Router::get("/testMethod2", DeveloperController::class, "testMethod2");

    /*}
}
*/