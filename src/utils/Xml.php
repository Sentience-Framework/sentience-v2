<?php

namespace src\utils;

use SimpleXMLElement;
use src\exceptions\XmlException;

class Xml
{
    public static function encode(array|object $value, ?callable $pluralToSingular = null): ?string
    {
        $rootTag = is_object($value)
            ? Reflector::getShortName($value)
            : 'root';

        $simpleXmlElement = new SimpleXMLElement(
            sprintf(
                '<?xml version="1.0" encoding="UTF-8"?><%s></%s>',
                $rootTag,
                $rootTag
            )
        );

        $encode = function (array $value, SimpleXMLElement $simpleXmlElement) use (&$encode, $pluralToSingular): SimpleXMLElement {
            array_walk(
                $value,
                function ($value, $key) use ($simpleXmlElement, &$encode, $pluralToSingular): void {
                    if (is_numeric($key) && $pluralToSingular) {
                        $key = $pluralToSingular($simpleXmlElement->getName(), $key);
                    }

                    if (preg_match('/^[0-9]{1}/', $key)) {
                        throw new XmlException('XML does not support tags that start with a numeric character');
                    }

                    if (str_starts_with($key, 'xml')) {
                        throw new XmlException('XML does not support tags that start with xml');
                    }

                    if (is_null($value)) {
                        $simpleXmlElement->addChild($key, '');

                        return;
                    }

                    if (is_scalar($value)) {
                        $simpleXmlElement->addChild($key, htmlspecialchars((string) $value));

                        return;
                    }

                    $encode((array) $value, $simpleXmlElement->addChild($key));
                }
            );

            return $simpleXmlElement;
        };

        $simpleXmlElement = $encode((array) $value, $simpleXmlElement);

        $xml = $simpleXmlElement->asXML();

        if (is_bool($xml)) {
            return null;
        }

        return $xml;
    }

    public static function decode(string $xml): ?SimpleXMLElement
    {
        $simpleXmlElement = simplexml_load_string($xml);

        if (is_bool($simpleXmlElement)) {
            return null;
        }

        return $simpleXmlElement;
    }
}
