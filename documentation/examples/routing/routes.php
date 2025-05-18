<?php

use src\controllers\ExampleController;
use src\middleware\CORSMiddleware;
use src\middleware\ExampleMiddleware;
use src\routers\Route;
use src\routers\RouteGroup;
use src\sentience\Request;
use src\sentience\Response;

[
    Route::create('/healthcheck')
        ->setMiddleware([
            [CORSMiddleware::class, 'addHeaders']
        ])
        ->setCallback(function (): void {
            Response::ok(['status' => 'available']);
        }),

    RouteGroup::create('/users/{userId}')
        ->setMiddleware([
            [CORSMiddleware::class, 'addHeaders'],
            [ExampleMiddleware::class, 'killSwitch']
        ])
        ->bind(Route::create('/')->setCallback([ExampleController::class, 'getUser'])->setMethods(['GET']))
        ->bind(
            RouteGroup::create('/contacts')
                ->bind(Route::create('/')->setCallback([ExampleController::class, 'getContacts'])->setMethods(['GET']))
                ->bind(Route::create('/')->setCallback([ExampleController::class, 'createContact'])->setMethods(['POST']))
                ->bind(
                    RouteGroup::create('/{contactId}')
                        ->bind(Route::create('/')->setCallback([ExampleController::class, 'getContact'])->setMethods(['GET']))
                        ->bind(Route::create('/')->setCallback([ExampleController::class, 'updateContact'])->setMethods(['PUT']))
                )
        ),

    Route::create('/{country}-{language}')
        ->setCallback('callback')
];

function callback(Request $request): void
{
    Response::ok($request->pathVars);
}
