# Request

Sentience offers a Request class that saves the incoming http request to a class.

The Request class contains the following properties and methods:
```
Request::url;
Request::path;
Request::method;
Request::headers;
Request::queryString;
Request::queryParams;
Request::cookies;
Request::pathVars;
Request::body;

Request::getHeader();
Request::getQueryParam();
Request::getCookie();
Request::getPathVar();
Request::getJson();
Request::getXml();
Request::getIPAddress();
```
