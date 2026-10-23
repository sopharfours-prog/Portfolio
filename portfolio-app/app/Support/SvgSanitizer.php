<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Minimal allow-by-default SVG sanitiser: strips active content so an uploaded
 * SVG can't run script when opened directly from the public disk.
 */
class SvgSanitizer
{
    private const BLOCKED_ELEMENTS = ['script', 'foreignobject', 'iframe', 'embed', 'object', 'audio', 'video', 'handler', 'listener', 'set', 'animate'];

    public static function clean(string $svg): ?string
    {
        if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)) {
            return null; // no DTDs → no entity expansion / XXE
        }

        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || strtolower($dom->documentElement?->localName ?? '') !== 'svg') {
            return null;
        }

        $xpath = new DOMXPath($dom);
        foreach (iterator_to_array($xpath->query('//*')) as $node) {
            /** @var DOMElement $node */
            if (in_array(strtolower($node->localName), self::BLOCKED_ELEMENTS, true)) {
                $node->parentNode?->removeChild($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = strtolower(preg_replace('/\s+/', '', $attribute->nodeValue));
                $isLink = in_array($name, ['href', 'xlink:href', 'src'], true);
                if (str_starts_with($name, 'on')
                    || ($isLink && ! str_starts_with($value, '#'))
                    || str_contains($value, 'javascript:')
                    || ($name === 'style' && (str_contains($value, 'url(') || str_contains($value, 'expression(')))) {
                    $node->removeAttributeNode($attribute);
                }
            }
        }

        return $dom->saveXML($dom->documentElement);
    }

    /** @return array{0: ?int, 1: ?int} */
    public static function dimensions(string $svg): array
    {
        if (preg_match('/viewBox="\s*[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $svg, $m)) {
            return [(int) round((float) $m[1]), (int) round((float) $m[2])];
        }
        preg_match('/\swidth="([\d.]+)/', $svg, $w);
        preg_match('/\sheight="([\d.]+)/', $svg, $h);

        return [isset($w[1]) ? (int) $w[1] : null, isset($h[1]) ? (int) $h[1] : null];
    }
}
