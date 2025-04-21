<?php

namespace src\middleware;

use src\sentience\Request;
use src\sentience\Response;

class ExampleMiddleware extends Middleware
{
    public function killSwitch(Request $request): void
    {
        if ($request->getQueryParam('killswitch') == 'true') {
            Response::internalServerError('early termination');
        }
    }
}
