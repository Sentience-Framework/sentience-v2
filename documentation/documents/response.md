# Response

Sentience offers a Response class. It contains methods for each http status code, based on the documentation provided by Mozilla: https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status

## 1. Response methods

The Response class contains the following static methods:
```
Response::ok();
Response::created();
Response::accepted();
Response::nonAuthoritativeInformation();
Response::noContent();
Response::resetContent();
Response::partialContent();
Response::multiStatus();
Response::alreadyReported();
Response::imUsed();
Response::multipleChoices();
Response::movedPermanently();
Response::found();
Response::seeOther();
Response::notModified();
Response::temporaryRedirect();
Response::permanentRedirect();
Response::badRequest();
Response::unauthorized();
Response::paymentRequired();
Response::forbidden();
Response::notFound();
Response::methodNotAllowed();
Response::notAcceptable();
Response::proxyAuthenticationRequired();
Response::requestTimeout();
Response::conflict();
Response::gone();
Response::lengthRequired();
Response::preconditionFailed();
Response::payloadTooLarge();
Response::uriTooLong();
Response::unsupportedMediaType();
Response::rangeNotSatisfiable();
Response::expectationFailed();
Response::teapot();
Response::misdirectedRequest();
Response::unprocessableContent();
Response::locked();
Response::failedDependency();
Response::tooEarly();
Response::upgradeRequired();
Response::preconditionRequired();
Response::tooManyRequests();
Response::requestHeaderFieldsTooLarge();
Response::unavailableForLegalReasons();
Response::internalServerError();
Response::notImplemented();
Response::badGateway();
Response::serviceUnavailable();
Response::gatewayTimeout();
Response::httpVersionNotSupported();
Response::variantAlsoNegotiates();
Response::insufficientStorage();
Response::loopDetected();
Response::notExtended();
Response::networkAuthenticationRequired();
```

## 2. Encoding responses

Based on the content, Sentience automatically encodes the data to the format set in the environment variable `APP_DEFAULT_ENCODING`.

If the content-type header is already set by function, then it doesn't override the content type. Otherwise, it automatically sets the content-type header based on the provided input and default encoding.
