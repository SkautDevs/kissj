<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SkautisController extends AbstractController
{
    public function __construct(
    ) {
    }

    public function redirectFromSkautis(Request $request, Response $response): Response
    {
        $this->flashMessages->info($this->translator->trans('flash.info.redirectedFromSkautis'));
        
        $returnLink = $this->getParameterFromQuery($request, 'ReturnUrl');
        // TODO load user into session

        return $response
            ->withHeader('Location', $returnLink)
            ->withStatus(302);
    }
}
