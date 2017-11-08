<?php

use Slim\Http\Request;
use Slim\Http\Response;

// TODO fix problem with "public" subdirectory in URI (not nice)

// Routes
$app->group("/" . $settings['settings']['eventName'], function () {

    $this->get("/login/{token}", function (Request $request, Response $response, array $args) {
        // TODO $role = login($args['token']);
        $role = 'patrol-leader';
        return $response->withRedirect("TODO$role");
    });

    // PATROL-LEADER

    $this->group("/patrol-leader", function () {

        $this->get("/register", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'register-patrol.phtml', $args);
        });

        $this->post("/register", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

        $this->get("/view", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'view-patrol.phtml', $args);
        });

    });

    // PARTICIPANT

    $this->group("/participant", function () {

        $this->get("/details[/{id}]", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'participant-details.phtml', $args);
        });

        $this->post("/details[/{id}]", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

        $this->get("/delete/{id}", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

    });

    // IST

    $this->group("/ist", function () {

        $this->get("/register", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'register-ist.phtml', $args);
        });

        $this->post("/register", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

        $this->get("/view", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'view-ist.phtml', $args);
        });

    });

    // REGISTRATION
	
	$this->get("/registration", function (Request $request, Response $response, array $args) {
		return $this->renderer->render($response, 'registration.phtml', ['router' => $this->router]);
	})->setName('registration');
	
	$this->post("/signup", function (Request $request, Response $response, array $args) {
		// TODO process
		//$this->UserService
		$email = $request->getParsedBodyParam("email");
		return $response->withRedirect($this->get('router')->pathFor('signed-up', ['email' => $email]));
	})->setName('signup');
	
    $this->get("/signed-up/{email}", function (Request $request, Response $response, array $args) {
        return $this->renderer->render($response, 'signed-up.phtml', $args);
    })->setName("signed-up");
    
    // LANDING PAGE
	
	$this->get("", function (Request $request, Response $response, array $args) {
		return $this->renderer->render($response, 'landing-page.phtml', ['router' => $this->router]);
	})->setName("landing");

});
