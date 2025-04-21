<?php

namespace src\middleware;

abstract class Middleware
{
    /**
     * When creating a middleware method, the return type can be a void or an array
     * All the entires in the array returned, can be used in the following middleware or callable
     *
     * For example:
     *
     * If the authentication middleware returns ['authToken' => new AuthToken()]
     * And the final callable looks like this: callable(Request $request, AuthToken $authToken);
     *
     * Then the final callable will receive the auth token from the middleware
     *
     * If the final callable does not have a function argument for $authToken
     * Then the argument will not be passed from the middleware to the final callable
     */
}
