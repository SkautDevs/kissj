<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;


/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends TestCase {
    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    /**
     * Process the application given a request method and URI
     *
     * @param string            $requestMethod
     * @param string            $requestUri
     * @param array|object|null $requestData the request data
     * @return ResponseInterface
     */
    public function runApp(string $requestMethod, string $requestUri, $requestData = null): ResponseInterface {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        $app = $this->app();

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string            $requestMethod
     * @param string            $requestUri
     * @param array|object|null $requestData the request data
     * @return App
     */
    public function app(): App {

        // Use the application settings
        $settings = require __DIR__.'/../../src/settings_test.php';

        // Instantiate the application
        $app = new App($settings);

        // Set up dependencies
        require __DIR__.'/../../src/dependencies.php';

        //mock mailer
        $app->getContainer()['mailer'] = function() {
            return new \kissj\Mailer\MockMailer();
        };

        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__.'/../../src/middleware.php';
        }

        // Register routes
        require __DIR__.'/../../src/routes.php';

        return $app;
    }
}
