<?php

namespace App\Application\Error;

use Slim\Handlers\ErrorHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Dflydev\FigCookies\FigRequestCookies;
use App\Domain\Admin\Banlist\BanlistService;
use App\Domain\Main\Utility\Utility;


class MyErrorHandler extends ErrorHandler {

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {

        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            // ignore page is not found when user is/was logged in
            // unfortunately the real login can't be easy checked because the login flow is later
            // so for anoymous access only the presence of the login token is checked
            $token = FigRequestCookies::get($request, 'token');
            if (is_null($token->getValue())) {
                $exception = new PageNotFoundException();
            }
        }

        return parent::__invoke($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
    }

    protected function determineStatusCode(): int {
        return 200;
    }
}
