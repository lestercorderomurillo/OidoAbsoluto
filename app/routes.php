<?php

use App\Middlewares\Authorization;
use Pipeline\HTTP\Server\URIRouter as Router;

Router::get("/", "Home", "login");
Router::get("/login", "Home", "login");
Router::get("/signup", "Home", "signup");
Router::get("/piano", "Piano", "hearingTest", ["mode"]);
Router::get("/profile", "Home", "profile");
Router::get("/reset_password", "Home", "resetPassword", ["token"]);
Router::get("/reset_request", "Home", "resetRequest");
Router::post("/piano_submit", "Piano", "submitHearingTest", ["mode", "expected_notes", "selected_notes"]);
Router::post("/login_submit", "Home", "loginSubmit", ["email", "password"]);
Router::post("/signup_submit", "Home", "signupSubmit", ["first_name", "last_name", "email", "password", "confirm_password", "country", "birth_day", "phone", "gender"]);
Router::get("/string", "Home", "string");
Router::get("/test", "Home", "test")->setMiddlewares(Authorization::class);
