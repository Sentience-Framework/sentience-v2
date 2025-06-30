# XML

With the flick of an environment variable, Sentience can be transformed to an XML framework.

## 1. Setting XML as default

In the `.env` there's a variable called
```
APP_DEFAULT_ENCODING='json'
```

By default it's set to `json`. By changing this variable to `xml`, Sentience automatically serializes all your responses to XML.

## 2. Handling XML arrays

Since XML doesn't allow numeric tag names, Sentience tries to guess the singular name of your array elements by subtracting an `s` from the end of your key.

Example:
```
<examples>
    <example>1</example>
    <example>2</example>
    <example>3</example>
    <example>4</example>
</example>
```

## 3. Root element name

By default the root element is called `<root>`. If you wish to override this, send a named object to the response method.

Example:
```
$response = new stdClass();
$response->id = 1;
$response->name = 'Sentience';

Response::ok($response);
```

Results in:
```
<stdClass>
    <id>1</id>
    <name>Sentience</name>
</stdClass>
```

## 4. Incoming XML

If the client sends XML in the request body, you can parse it to a SimpleXMLElement by calling
```
$request->getXml();
```
