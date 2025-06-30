# CORS handling

Sentience provides a way to automatically handle CORS. By adding the `src\middleware\CORSMiddleware::addHeaders()` middleware to your request, the route will add the CORS headers based on the environment variables set.

The environment variables are:
```
ACCESS_CONTROL_RETURN_ORIGIN
ACCESS_CONTROL_ALLOW_ORIGIN
ACCESS_CONTROL_ALLOW_CREDENTIALS
ACCESS_CONTROL_ALLOW_METHODS
ACCESS_CONTROL_ALLOW_HEADERS
```
