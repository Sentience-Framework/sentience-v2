<?php

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
        ->bind(Route::create('/')->setCallback([UserController::class, 'getUser'])->setMethods(['GET']))
        ->bind(
            RouteGroup::create('/contacts')
                ->bind(Route::create('/')->setCallback([UserController::class, 'getContacts'])->setMethods(['GET']))
                ->bind(Route::create('/')->setCallback([UserController::class, 'createContact'])->setMethods(['POST']))
                ->bind(
                    RouteGroup::create('/{contactId}')
                        ->bind(Route::create('/')->setCallback([UserController::class, 'getContact'])->setMethods(['GET']))
                        ->bind(Route::create('/')->setCallback([UserController::class, 'updateContact'])->setMethods(['PUT']))
                )
        ),

    Route::create('/{country}-{language}')
        ->setCallback('callback'),
];

function callback(Request $request): void
{
    Response::ok($request->pathVars);
}
