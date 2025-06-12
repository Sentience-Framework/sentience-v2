<?php

namespace src\sentience;

use src\exceptions\EncodingException;
use src\utils\Json;
use src\utils\UrlEncoding;
use src\utils\Xml;

class Response
{
    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NON_AUTHORITATIVE_INFORMATION = 203;
    public const NO_CONTENT = 204;
    public const RESET_CONTENT = 205;
    public const PARTIAL_CONTENT = 206;
    public const MULTI_STATUS = 207;
    public const ALREADY_REPORTED = 208;
    public const IM_USED = 226;
    public const MULTIPLE_CHOICES = 300;
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const NOT_MODIFIED = 304;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const PAYMENT_REQUIRED = 402;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT = 408;
    public const CONFLICT = 409;
    public const GONE = 410;
    public const LENGTH_REQUIRED = 411;
    public const PRECONDITION_FAILED = 412;
    public const PAYLOAD_TOO_LARGE = 413;
    public const URI_TOO_LONG = 414;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const RANGE_NOT_SATISFIABLE = 416;
    public const EXPECTATION_FAILED = 417;
    public const TEAPOT = 418;
    public const MISDIRECTED_REQUEST = 421;
    public const UNPROCESSABLE_CONTENT = 422;
    public const LOCKED = 423;
    public const FAILED_DEPENDENCY = 424;
    public const TOO_EARLY = 425;
    public const UPGRADE_REQUIRED = 426;
    public const PRECONDITION_REQUIRED = 428;
    public const TOO_MANY_REQUESTS = 429;
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const VARIANT_ALSO_NEGOTIATES = 506;
    public const INSUFFICIENT_STORAGE = 507;
    public const LOOP_DETECTED = 508;
    public const NOT_EXTENDED = 510;
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    public static function ok(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::OK, $content, $encoding);
    }

    public static function created(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::CREATED, $content, $encoding);
    }

    public static function accepted(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::ACCEPTED, $content, $encoding);
    }

    public static function nonAuthoritativeInformation(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NON_AUTHORITATIVE_INFORMATION, $content, $encoding);
    }

    public static function noContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NO_CONTENT, $content, $encoding);
    }

    public static function resetContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::RESET_CONTENT, $content, $encoding);
    }

    public static function partialContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PARTIAL_CONTENT, $content, $encoding);
    }

    public static function multiStatus(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::MULTI_STATUS, $content, $encoding);
    }

    public static function alreadyReported(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::ALREADY_REPORTED, $content, $encoding);
    }

    public static function imUsed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::IM_USED, $content, $encoding);
    }

    public static function multipleChoices(string $url): void
    {
        static::redirect(static::MULTIPLE_CHOICES, $url);
    }

    public static function movedPermanently(string $url): void
    {
        static::redirect(static::MOVED_PERMANENTLY, $url);
    }

    public static function found(string $url): void
    {
        static::redirect(static::FOUND, $url);
    }

    public static function seeOther(string $url): void
    {
        static::redirect(static::SEE_OTHER, $url);
    }

    public static function notModified(string $url): void
    {
        static::redirect(static::NOT_MODIFIED, $url);
    }

    public static function temporaryRedirect(string $url): void
    {
        static::redirect(static::TEMPORARY_REDIRECT, $url);
    }

    public static function permanentRedirect(string $url): void
    {
        static::redirect(static::PERMANENT_REDIRECT, $url);
    }

    public static function badRequest(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::BAD_REQUEST, $content, $encoding);
    }

    public static function unauthorized(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::UNAUTHORIZED, $content, $encoding);
    }

    public static function paymentRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PAYMENT_REQUIRED, $content, $encoding);
    }

    public static function forbidden(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::FORBIDDEN, $content, $encoding);
    }

    public static function notFound(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NOT_FOUND, $content, $encoding);
    }

    public static function methodNotAllowed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::METHOD_NOT_ALLOWED, $content, $encoding);
    }

    public static function notAcceptable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NOT_ACCEPTABLE, $content, $encoding);
    }

    public static function proxyAuthenticationRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PROXY_AUTHENTICATION_REQUIRED, $content, $encoding);
    }

    public static function requestTimeout(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::REQUEST_TIMEOUT, $content, $encoding);
    }

    public static function conflict(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::CONFLICT, $content, $encoding);
    }

    public static function gone(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::GONE, $content, $encoding);
    }

    public static function lengthRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::LENGTH_REQUIRED, $content, $encoding);
    }

    public static function preconditionFailed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PRECONDITION_FAILED, $content, $encoding);
    }

    public static function payloadTooLarge(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PAYLOAD_TOO_LARGE, $content, $encoding);
    }

    public static function uriTooLong(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::URI_TOO_LONG, $content, $encoding);
    }

    public static function unsupportedMediaType(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::UNSUPPORTED_MEDIA_TYPE, $content, $encoding);
    }

    public static function rangeNotSatisfiable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::RANGE_NOT_SATISFIABLE, $content, $encoding);
    }

    public static function expectationFailed(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::EXPECTATION_FAILED, $content, $encoding);
    }

    public static function teapot(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::TEAPOT, $content, $encoding);
    }

    public static function misdirectedRequest(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::MISDIRECTED_REQUEST, $content, $encoding);
    }

    public static function unprocessableContent(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::UNPROCESSABLE_CONTENT, $content, $encoding);
    }

    public static function locked(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::LOCKED, $content, $encoding);
    }

    public static function failedDependency(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::FAILED_DEPENDENCY, $content, $encoding);
    }

    public static function tooEarly(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::TOO_EARLY, $content, $encoding);
    }

    public static function upgradeRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::UPGRADE_REQUIRED, $content, $encoding);
    }

    public static function preconditionRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::PRECONDITION_REQUIRED, $content, $encoding);
    }

    public static function tooManyRequests(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::TOO_MANY_REQUESTS, $content, $encoding);
    }

    public static function requestHeaderFieldsTooLarge(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::REQUEST_HEADER_FIELDS_TOO_LARGE, $content, $encoding);
    }

    public static function unavailableForLegalReasons(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::UNAVAILABLE_FOR_LEGAL_REASONS, $content, $encoding);
    }

    public static function internalServerError(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::INTERNAL_SERVER_ERROR, $content, $encoding);
    }

    public static function notImplemented(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NOT_IMPLEMENTED, $content, $encoding);
    }

    public static function badGateway(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::BAD_GATEWAY, $content, $encoding);
    }

    public static function serviceUnavailable(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::SERVICE_UNAVAILABLE, $content, $encoding);
    }

    public static function gatewayTimeout(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::GATEWAY_TIMEOUT, $content, $encoding);
    }

    public static function httpVersionNotSupported(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::HTTP_VERSION_NOT_SUPPORTED, $content, $encoding);
    }

    public static function variantAlsoNegotiates(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::VARIANT_ALSO_NEGOTIATES, $content, $encoding);
    }

    public static function insufficientStorage(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::INSUFFICIENT_STORAGE, $content, $encoding);
    }

    public static function loopDetected(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::LOOP_DETECTED, $content, $encoding);
    }

    public static function notExtended(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NOT_EXTENDED, $content, $encoding);
    }

    public static function networkAuthenticationRequired(mixed $content = null, string $encoding = 'default'): void
    {
        static::respond(static::NETWORK_AUTHENTICATION_REQUIRED, $content, $encoding);
    }

    public static function custom(string $content, string $contentType, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        header('Content-Type: ' . $contentType, true);

        echo $content;

        exit;
    }

    protected static function redirect(int $statusCode, string $url): void
    {
        http_response_code($statusCode);

        header(sprintf('Location: %s', $url));

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
            header('Content-Type: text/plain', true);

            echo strval($content);

            exit;
        }

        $encoding = !in_array($encoding, ['json', 'xml', 'url'])
            ? env('APP_DEFAULT_ENCODING')
            : $encoding;

        if ($encoding == 'json') {
            header('Content-Type: application/json', true);

            echo Json::encode($content);

            exit;
        }

        if ($encoding == 'xml') {
            header('Content-Type: text/xml', true);

            echo Xml::encode(
                $content,
                function (string $parent, string $key) use ($statusCode): string {
                    $lowercaseParent = strtolower($parent);

                    if (preg_match('/^.{1}ies$/', $lowercaseParent)) {
                        return substr($parent, 0, -1);
                    }

                    if (preg_match('/ies$/', $lowercaseParent)) {
                        $singular = substr($parent, 0, -3);

                        return $singular . (preg_match('/[A-Z]{1}$/', $singular) ? 'Y' : 'y');
                    }

                    if (preg_match('/[^aeiouy]es$/', $lowercaseParent)) {
                        return substr($parent, 0, -2);
                    }

                    if (preg_match('/s{1}$/', $lowercaseParent)) {
                        return substr($parent, 0, -1);
                    }

                    if ($lowercaseParent == 'trace' && $statusCode == 500) {
                        return 'frame';
                    }

                    return $parent;
                }
            );

            exit;
        }

        if ($encoding == 'url') {
            header('Content-Type: application/x-www-form-urlencoded', false);

            echo UrlEncoding::encode($content);

            exit;
        }

        throw new EncodingException('unknown default encoding %s', $encoding);
    }
}
