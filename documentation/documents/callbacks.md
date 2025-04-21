# Callbacks

Sentience has a dependency injection system that works with all types of callbacks.
```
setCallback('string_function');
setCallback(function () { });
setCallback([class, 'method']);
```

## 1. Where do these injected dependencies come from

Sentience offers a service object in the root of the project. By default it has one method called `database`. You can include the database by including it in your callbacks args like `Database $database`. The name of the function arg needs to match the service method.

There are also 3 reserved keywords:
- request
- words
- flags

### 1.1 Request

Sentience offers a request class for http requests. If Sentience is run in the command line, $request will be `null`.

### 1.2 Words and flags

Words and flags are a fancier way of saying arguments. Here is a little example to illustrate what words and flags are:

```
php sentience.php namespace:command this is a word --this-is-a=flag
```

## 2. Returns

Callbacks are not supposed to return output, unless they're middleware. Use the `Stdio` or `Response` class to return output.
