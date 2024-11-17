<?php

namespace kissj\Application;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CookieHandler
{
    public function getCookie(Request $request, string $key): ?string
    {
        return FigRequestCookies::get($request, $key)->getValue();
    }

    public function setCookie(Response $response, string $key, string $value): Response
    {
        $response = FigResponseCookies::remove($response, $key);
        $response = FigResponseCookies::set(
            $response,
            SetCookie::create($key, $value)
                ->rememberForever()
                ->withPath('/')
        );

        return $response;
    }
}
