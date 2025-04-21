# Routing

Sentience offers built-in routers for HTTP requests and CLI commands. Both routers are initialized when a new instance of Sentience is created.

## 1. Binding routes and commands

The Sentience class offers two methods to bind commands and routes.
```
$sentience->bindCommand(\src\routers\Command);
$sentience->bindRoute(\src\routers\Route | \src\routers\RouteGroup);
```

The order in which commands and routes are defined, are they order they are processed in. If you have a route called `/users/create`, and it's defined after `/users/{id}`, then `/users/{id}` will match first.

## 2. Setting callback and middleware

To set the callback and middleware, Sentience allows you to method chain setter functions upon calling the `create()` method.

Example:
```
Route::create()
    ->setCallback([PublicController::class, 'homepage'])
    ->setMiddleware([
        [TrackingMiddleware::class, 'registerPageVisit']
    ])
```

The `RouteGroup` lacks the `setCallback()` method, but instead has a `bind()` method to bind child routes or route groups.

Example:
```
RouteGroup::create('/users/{userId}')
    ->setMiddleware([
        [CORSMiddleware::class, 'addHeaders'],
    ])
    ->bind(
        Route::create('/')
            ->setCallback([UserController::class, 'getUser'])
            ->setMethods(['GET'])
    );
```

The order in which middleware is defined, is the order they will be executed in. If a route group defines middleware, and the route itself also defines middleware, then the middleware defined by the route group will be executed first.

There is no limit how deep you can nest these route groups.

## 3. Routing errors

When a command or route cannot be found, Sentience calls the following methods depending on the php_sapi.

```
Sentience::cliNotFound();
Sentience::httpNotFound();
```

If the route exists, but the request method isn't valid, then the `Sentience::httpMethodNotAllowed()` is called.
