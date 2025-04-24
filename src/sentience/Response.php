<?php

namespace src\sentience;

use src\exceptions\EncodingException;
use src\utils\Json;
use src\utils\Xml;

class Response
{
    public static function ok(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(200, $content, $encoding);
    }

    public static function created(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(201, $content, $encoding);
    }

    public static function accepted(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(202, $content, $encoding);
    }

    public static function nonAuthoritativeInformation(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(203, $content, $encoding);
    }

    public static function noContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(204, $content, $encoding);
    }

    public static function resetContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(205, $content, $encoding);
    }

    public static function partialContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(206, $content, $encoding);
    }

    public static function multiStatus(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(207, $content, $encoding);
    }

    public static function alreadyReported(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(208, $content, $encoding);
    }

    public static function imUsed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(226, $content, $encoding);
    }

    public static function multipleChoices(string $url): void
    {
        static::redirect(300, $url);
    }

    public static function movedPermanently(string $url): void
    {
        static::redirect(301, $url);
    }

    public static function found(string $url): void
    {
        static::redirect(302, $url);
    }

    public static function seeOther(string $url): void
    {
        static::redirect(303, $url);
    }

    public static function notModified(string $url): void
    {
        static::redirect(304, $url);
    }

    public static function temporaryRedirect(string $url): void
    {
        static::redirect(307, $url);
    }

    public static function permanentRedirect(string $url): void
    {
        static::redirect(308, $url);
    }

    public static function badRequest(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(400, $content, $encoding);
    }

    public static function unauthorized(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(401, $content, $encoding);
    }

    public static function paymentRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(402, $content, $encoding);
    }

    public static function forbidden(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(403, $content, $encoding);
    }

    public static function notFound(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(404, $content, $encoding);
    }

    public static function methodNotAllowed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(405, $content, $encoding);
    }

    public static function notAcceptable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(406, $content, $encoding);
    }

    public static function proxyAuthenticationRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(407, $content, $encoding);
    }

    public static function requestTimeout(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(408, $content, $encoding);
    }

    public static function conflict(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(409, $content, $encoding);
    }

    public static function gone(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(410, $content, $encoding);
    }

    public static function lengthRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(411, $content, $encoding);
    }

    public static function preconditionFailed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(412, $content, $encoding);
    }

    public static function payloadTooLarge(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(413, $content, $encoding);
    }

    public static function uriTooLong(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(414, $content, $encoding);
    }

    public static function unsupportedMediaType(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(415, $content, $encoding);
    }

    public static function rangeNotSatisfiable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(416, $content, $encoding);
    }

    public static function expectationFailed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(417, $content, $encoding);
    }

    public static function teapot(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(418, $content, $encoding);
    }

    public static function misdirectedRequest(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(421, $content, $encoding);
    }

    public static function unprocessableContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(422, $content, $encoding);
    }

    public static function locked(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(423, $content, $encoding);
    }

    public static function failedDependency(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(424, $content, $encoding);
    }

    public static function tooEarly(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(425, $content, $encoding);
    }

    public static function upgradeRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(426, $content, $encoding);
    }

    public static function preconditionRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(428, $content, $encoding);
    }

    public static function tooManyRequests(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(429, $content, $encoding);
    }

    public static function requestHeaderFieldsTooLarge(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(431, $content, $encoding);
    }

    public static function unavailableForLegalReasons(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(451, $content, $encoding);
    }

    public static function internalServerError(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(500, $content, $encoding);
    }

    public static function notImplemented(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(501, $content, $encoding);
    }

    public static function badGateway(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(502, $content, $encoding);
    }

    public static function serviceUnavailable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(503, $content, $encoding);
    }

    public static function gatewayTimeout(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(504, $content, $encoding);
    }

    public static function httpVersionNotSupported(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(505, $content, $encoding);
    }

    public static function variantAlsoNegotiates(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(506, $content, $encoding);
    }

    public static function insufficientStorage(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(507, $content, $encoding);
    }

    public static function loopDetected(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(508, $content, $encoding);
    }

    public static function notExtended(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(510, $content, $encoding);
    }

    public static function networkAuthenticationRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(511, $content, $encoding);
    }

    protected static function redirect(int $statusCode, string $url): void
    {
        http_response_code($statusCode);

        header(sprintf('Location %s', $url));

        exit;
    }

    protected static function respond(int $statusCode, mixed $content, string $encoding): void
    {
        http_response_code($statusCode);

        if (is_null($content)) {
            header('Content-Type: ', false);

            echo '';

            exit;
        }

        if (is_scalar($content)) {
            header('Content-Type: text/plain', false);

            echo strval($content);

            exit;
        }

        $encoding = !in_array($encoding, ['json', 'xml'])
            ? env('APP_DEFAULT_ENCODING')
            : $encoding;

        if ($encoding == 'json') {
            header('Content-Type: application/json', false);

            echo Json::encode($content);

            exit;
        }

        if ($encoding == 'xml') {
            header('Content-Type: text/xml', false);

            echo Xml::encode(
                $content,
                function (string $parent, string $key) use ($statusCode): string {
                    $lowercaseParent = strtolower($parent);

                    if (preg_match('/^.{1}ies$/', $lowercaseParent)) {
                        return substr($parent, 0, strlen($parent) - 1);
                    }

                    if (preg_match('/ies$/', $lowercaseParent)) {
                        $singular = substr($parent, 0, strlen($parent) - 3);

                        return $singular . (preg_match('/[A-Z]{1}$/', $singular) ? 'Y' : 'y');
                    }

                    if (preg_match('/[^aeiouy]es$/', $lowercaseParent)) {
                        return substr($parent, 0, strlen($parent) - 2);
                    }

                    if (preg_match('/s{1}$/', $lowercaseParent)) {
                        return substr($parent, 0, strlen($parent) - 1);
                    }

                    if ($lowercaseParent == 'trace' && $statusCode == 500) {
                        return 'frame';
                    }

                    return $parent;
                }
            );

            exit;
        }

        throw new EncodingException('unknown default encoding %s', $encoding);
    }
}
