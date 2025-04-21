<?php

namespace src\middleware;

use src\sentience\Request;

class CORSMiddleware extends Middleware
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function addHeaders(): void
    {
        $originHeader = $this->request->getHeader('origin');
        $originEnv = implode(', ', env('ACCESS_CONTROL_ALLOW_ORIGIN', ['*']));

        $returnOrigin = env('ACCESS_CONTROL_RETURN_ORIGIN', true);

        $origin = ($returnOrigin && $originHeader)
            ? $originHeader
            : $originEnv;

        header(sprintf('Access-Control-Allow-Origin: %s', $origin));
        header(sprintf('Access-Control-Allow-Credentials: %s', env('ACCESS_CONTROL_ALLOW_CREDENTIALS', true) ? 'true' : 'false'));
        header(sprintf('Access-Control-Allow-Methods: %s', implode(', ', env('ACCESS_CONTROL_ALLOW_METHODS', ['*']))));
        header(sprintf('Access-Control-Allow-Headers: %s', implode(', ', env('ACCESS_CONTROL_ALLOW_HEADERS', ['*']))));
    }
}
