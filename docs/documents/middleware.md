# Middleware

Sentience offers a way to add middleware to commands and routes. It relies on dependency injection to pass values to the command or route callback.

## 1. Creating middleware

Middleware is simply a callback that returns a void or an array. Predefined functions, anonymous Closure's, and [class, method] array's are allowed.

If you chose the [class, method] way, you'll need to make sure the class extends the src\middleware\Middleware class.

## 2. Function args

Middleware supports the same dependency injection as command or route callbacks. The following examples are all valid
```
function (Database $database, Request $request): void;
function (Database $database, Request $request, ...$args): void;
function (...$args): array;
function (): array;
```

## 3. Return values

Middleware can pass values to the next middleware, or final callback, by returning an associative array.

If a middleware checks which user is authenticated, and returns a user, then the following middlewares and final callback can include `user` in their arguments, and the User class will be passed in.
```
function middleware(Request $request): array {
    $user = get_user($request->getHeader('authentication'));

    return [
        'user' => $user
    ];
}

function callback(User $user): void {
    Response::ok($user);
}
```
