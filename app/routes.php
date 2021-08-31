<?php

use Pipeline\HTTP\Server\URIRouter;
use Pipeline\Middleware\Authorization;

/* Index*/
URIRouter::get("/", "Home", "login");
URIRouter::get("/index", "Home", "login"); 

/* HomeController Routes */
// if already logged, the controller must redirect to profile page
URIRouter::get("/login", "Home", "login"); 
URIRouter::post(
    "/login/submit",
    "Home",
    "loginSubmit",
    ["email", "password"]
);
URIRouter::get("/signup", "Home", "signup");
URIRouter::post(
    "/signup/submit",
    "Home",
    "signupSubmit",
    ["first_name", "last_name", "email", "password", "confirm_password", "country", "birth_day", "phone", "gender"]
);

URIRouter::get("/forgot", "Home", "resetRequest");
URIRouter::post("/forgot/submit", "Home", "resetRequestSubmit");
URIRouter::post("/newpass", "Home", "resetPassword", ["token"]);
URIRouter::post("/newpass/submit", "Home", "resetPasswordSubmit");

/* UserController Routes */
URIRouter::get("/testing/audio", "User", "hearingTest", ["mode"]);
URIRouter::post("/testing/audio/submit", "User", "submitHearingTest", ["mode", "expected_notes", "selected_notes"]);
URIRouter::get("/testing/questions", "User", "questionsTest", ["mode"]);
URIRouter::get("/user/overview", "User", "overview");
URIRouter::get("/profile", "User", "profile");

/* AdminstratorController Routes */
URIRouter::get("/admin/graph", "Admin", "graph");
URIRouter::get("/admin/overview", "Admin", "overview");

/* DeveloperController Routes */
URIRouter::get("/test", "Developer", "testMethod1")->setMiddlewares(Authorization::class);
