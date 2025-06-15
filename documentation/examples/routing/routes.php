<?php

$routes = [
    Route::register(
        '/healthcheck',
        function (): void {
            Response::ok(['status' => 'available']);
        }
    )->setMiddleware([
                [CORSMiddleware::class, 'addHeaders']
            ]),

    RouteGroup::register('/response')
        ->setMiddleware([
            [CORSMiddleware::class, 'addHeaders']
        ])
        ->bind(Route::register('/json', [ExampleController::class, 'jsonResponse']))
        ->bind(Route::register('/xml', [ExampleController::class, 'xmlResponse']))
        ->bind(Route::register('/url', [ExampleController::class, 'urlResponse'])),

    RouteGroup::register('/users/{userId}')
        ->setMiddleware([
            [CORSMiddleware::class, 'addHeaders'],
            [ExampleMiddleware::class, 'killSwitch']
        ])
        ->bind(Route::register('/', [ExampleController::class, 'getUser'])->setMethods(['GET']))
        ->bind(
            RouteGroup::register('/contacts')
                ->bind(Route::register('/', [ExampleController::class, 'getContacts'])->setMethods(['GET']))
                ->bind(Route::register('/', [ExampleController::class, 'createContact'])->setMethods(['POST']))
                ->bind(
                    RouteGroup::register('/{contactId}')
                        ->bind(Route::register('/', [ExampleController::class, 'getContact'])->setMethods(['GET']))
                        ->bind(Route::register('/', [ExampleController::class, 'updateContact'])->setMethods(['PUT']))
                )
        ),

    Route::register('/{country}-{language}', function (Request $request): void {
        Response::ok($request->pathVars);
    })
];
