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
            return $this->renderer->render($response, 'register-patrol.html', $args);
        });

        $this->post("/register", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

        $this->get("/view", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'view-patrol.html', $args);
        });

    });

    // PARTICIPANT

    $this->group("/participant", function () {

        $this->get("/details[/{id}]", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'participant-details.html', $args);
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
            return $this->renderer->render($response, 'register-ist.html', $args);
        });

        $this->post("/register", function (Request $request, Response $response, array $args) {
            // TODO process
            return $response->withRedirect("TODO");
        });

        $this->get("/view", function (Request $request, Response $response, array $args) {
            return $this->renderer->render($response, 'view-ist.html', $args);
        });

    });

    // REGISTRATION
	
	$this->get("/registration", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'registration.twig', ['router' => $this->router]);
	})->setName('registration');
	
	$this->post("/signup", function (Request $request, Response $response, array $args) {
		$email = $request->getParsedBodyParam("email");
		$this->userService->registerUser($email);
		$this->userService->sendLoginLink($email);
		return $this->view->render($response, 'signed-up.twig', ['email' => $email]);
	})->setName('signup');
	
	// LANDING PAGE
	
	$this->get("", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'landing-page.twig', ['router' => $this->router]);
	})->setName("landing");
	
});
