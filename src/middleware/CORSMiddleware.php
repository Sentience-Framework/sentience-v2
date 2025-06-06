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

        $accessControlAllowOrigin = ($returnOrigin && $originHeader) ? $originHeader : $originEnv;
        $accessControlAllowCredentials = env('ACCESS_CONTROL_ALLOW_CREDENTIALS', true) ? 'true' : 'false';
        $accessControlAllowMethods = implode(', ', env('ACCESS_CONTROL_ALLOW_METHODS', ['*']));
        $accessControlAllowHeaders = implode(', ', env('ACCESS_CONTROL_ALLOW_HEADERS', ['*']));

        header('Access-Control-Allow-Origin: ' . $accessControlAllowOrigin);
        header('Access-Control-Allow-Credentials: ' . $accessControlAllowCredentials);
        header('Access-Control-Allow-Methods: ' . $accessControlAllowMethods);
        header('Access-Control-Allow-Headers: ' . $accessControlAllowHeaders);
    }
}
