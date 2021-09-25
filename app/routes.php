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
Router::post("/newpass", "Home", "resetPassword", ["token"]);
Router::post("/newpass/submit", "Home", "resetPasswordSubmit");

/* UserController Routes */
Router::get("/testing/audio", "User", "hearingTest", ["mode"]);
Router::post("/testing/audio/submit", "User", "submitHearingTest", ["mode", "expected_notes", "selected_notes"]);
Router::get("/testing/questions", "User", "questionsTest", ["mode"]);
Router::get("/user/overview", "User", "overview");
Router::get("/profile", "User", "profile");

/* AdminstratorController Routes */
Router::get("/admin/graph", "Admin", "graph");
Router::get("/admin/overview", "Admin", "overview");

/* DeveloperController Routes */
Router::get("/test", "Developer", "testMethod1");
