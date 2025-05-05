<?php

namespace src\sentience;

use SimpleXMLElement;
use src\utils\Json;
use src\utils\UrlEncoding;
use src\utils\Xml;

class Request
{
    public string $url;
    public string $path;
    public string $method;
    public array $headers;
    public string $queryString;
    public array $queryParams;
    public array $cookies;
    public array $pathVars = [];
    public string $body;

    public function __construct()
    {
        $url = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        $this->url = $url['scheme'] . '://' . $url['host'] . (key_exists('port', $url) ? ':' . $url['port'] : '') . $url['path'] . (key_exists('query', $url) ? '?' . $url['query'] : '');
        $this->path = $url['path'];
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $this->queryString = $url['query'] ?? '';
        $this->queryParams = UrlEncoding::decode($this->queryString, false);
        $this->cookies = $_COOKIE;
        $this->body = file_get_contents('php://input');
    }

    public function getHeader(string $key, ?string $default = null): ?string
    {
        if (!key_exists($key, $this->headers)) {
            return $default;
        }

        return $this->headers[$key];
    }

    public function getQueryParam(string $key, null|string|array $default = null): mixed
    {
        if (!key_exists($key, $this->queryParams)) {
            return $default;
        }

        return $this->queryParams[$key];
    }

    public function getCookie(string $key, ?string $default = null): ?string
    {
        if (!key_exists($key, $this->cookies)) {
            return $default;
        }

        return $this->cookies[$key];
    }

    public function getPathVar(string $key, ?string $default = null): mixed
    {
        if (!key_exists($key, $this->pathVars)) {
            return $default;
        }

        return $this->pathVars[$key];
    }

    public function getJson(bool $associative = true): mixed
    {
        return Json::decode($this->body, $associative);
    }

    public function getXml(): ?SimpleXMLElement
    {
        return Xml::decode($this->body);
    }

    public function getUrlEncoded(bool $unique = false): array
    {
        return UrlEncoding::decode($this->body, $unique);
    }

    public function getIPAddress(): ?string
    {
        $keys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($keys as $key) {
            if (key_exists($key, $_SERVER)) {
                return $_SERVER[$key];
            }
        }

        return null;
    }
}
