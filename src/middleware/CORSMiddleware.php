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

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: ' . (env('ACCESS_CONTROL_ALLOW_CREDENTIALS', true) ? 'true' : 'false'));
        header('Access-Control-Allow-Methods: ' . implode(', ', env('ACCESS_CONTROL_ALLOW_METHODS', ['*'])));
        header('Access-Control-Allow-Headers: ' . implode(', ', env('ACCESS_CONTROL_ALLOW_HEADERS', ['*'])));
    }
}
