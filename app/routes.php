<?php

use Pipeline\HTTP\Server\Router;
use Pipeline\Middleware\Authorization;

/* Index*/
Router::get("/", "Home", "login");
Router::get("/index", "Home", "login");

/* HomeController Routes */
Router::get("/login", "Home", "login");
Router::post("/login/submit", "Home", "loginSubmit", ["email", "password"]);
Router::get("/signup", "Home", "signup");
Router::post("/signup/submit", "Home", "signupSubmit", 
["first_name", "last_name", "email", "password", "confirm_password", "country", "birth_day", "phone", "gender"]);

Router::get("/forgot", "Home", "resetRequest");
Router::post("/forgot/submit", "Home", "resetRequestSubmit");
Router::get("/newpass", "Home", "resetPassword", ["token"]);
Router::post("/newpass/submit", "Home", "resetPasswordSubmit");

/* UserController Routes */
Router::get("/test/audio", "User", "hearingTest", ["mode"]);
Router::post("/test/audio/submit", "User", "submitHearingTest", ["mode", "expected_notes", "selected_notes"]);
Router::get("/test/questions", "User", "questionsTest");
/*Router::get("/test/questions/submit", "User", "questionsTest", ["mode"]);*/
Router::get("/test/result", "User", "testResult", ["id"]);
Router::get("/profile", "User", "profile");

/* AdminstratorController Routes */
Router::get("/admin/overview", "Admin", "overview");

/* DeveloperController Routes */
Router::get("/test", "Developer", "testMethod1");
