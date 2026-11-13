<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Prepares a post body (CKEditor HTML from the admin) for the storefront:
 *  - only content tags stay; scripts, forms, styles, comments and event handlers go, and wrappers such as <span>,
 *    <div> or <font> are unwrapped (their text stays)
 *  - links and images on shopmajazi.com become site-relative, so they work on any host and through the Next.js proxy
 *  - inline sizes are dropped so images fit the column; images load lazily; only known video embeds stay
 *  - <h1> becomes <h2> (the page has its own title); the h2s and h3s get ids and make the contents list
 *  - empty paragraphs go, and tables get a wrapper that scrolls sideways on phones
 */
class BlogHtml
{
    const KEEP = ['p', 'br', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'mark', 'sub', 'sup',
        'small', 'code', 'pre', 'kbd', 'a', 'img', 'figure', 'figcaption', 'ul', 'ol', 'li', 'blockquote', 'hr', 'dl', 'dt', 'dd',
        'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'iframe'];

    /** Removed with everything inside. Any other tag not in KEEP is unwrapped. */
    const DROP = ['script', 'style', 'link', 'meta', 'base', 'title', 'head', 'object', 'embed', 'applet', 'form', 'input',
        'button', 'select', 'option', 'textarea', 'noscript', 'template', 'svg', 'math', 'frame', 'frameset', 'canvas',
        'audio', 'video', 'source', 'track', 'dialog'];

    const ATTRS = [
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
        'ol' => ['start', 'reversed'],
        'iframe' => ['src', 'title', 'allowfullscreen'],
    ];

    const EMBEDS = '#^https://(www\.)?(youtube\.com/embed/|youtube-nocookie\.com/embed/|aparat\.com/video/video/embed/|player\.vimeo\.com/video/)#i';
    const OWN_SITE = '#^(?:https?:)?//(?:www\.)?shopmajazi\.(?:com|ir)(?=[/?\#]|$)#i';

    /** @return array{html: string, toc: list<array{id: string, text: string, level: int}>} */
    public static function clean(?string $html): array
    {
        if (trim((string) $html) === '') {
            return ['html' => '', 'toc' => []];
        }

        $doc = new DOMDocument();
        $errors = libxml_use_internal_errors(true);
        // The encoding hint keeps Persian text intact; the wrapper gives the fragment one root.
        $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($errors);
        $root = $doc->getElementsByTagName('div')->item(0);
        if (!$root) {
            return ['html' => '', 'toc' => []];
        }

        self::walk($root);
        $xpath = new DOMXPath($doc);

        foreach (iterator_to_array($xpath->query('.//p', $root)) as $p) {
            if (self::blank($p->textContent) && !$xpath->query('.//img|.//iframe', $p)->length) {
                $p->parentNode->removeChild($p);
            }
        }

        foreach (iterator_to_array($xpath->query('.//table', $root)) as $table) {
            $wrap = $doc->createElement('div');
            $wrap->setAttribute('class', 'table-wrap');
            $table->parentNode->replaceChild($wrap, $table);
            $wrap->appendChild($table);
        }

        // Contents: the h2s and h3s in reading order; only the h2s when that list would get long.
        $toc = [];
        $h2s = $xpath->query('.//h2', $root)->length;
        $headings = $xpath->query($h2s >= 3 && $xpath->query('.//h2|.//h3', $root)->length > 14 ? './/h2' : './/h2|.//h3', $root);
        foreach ($headings as $h) {
            $text = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $h->textContent));
            if ($text === '') {
                continue;
            }
            $id = 's-' . (count($toc) + 1);
            $h->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'text' => $text, 'level' => $h->nodeName === 'h3' ? 3 : 2];
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return ['html' => trim($out), 'toc' => $toc];
    }

    /** Words in the body, for the reading time. */
    public static function words(?string $html): int
    {
        return count(preg_split('/\s+/u', trim(html_entity_decode(strip_tags((string) $html))), -1, PREG_SPLIT_NO_EMPTY));
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }
            if ($tag === 'h1') {
                $child = self::rename($child, 'h2');
                $tag = 'h2';
            }
            self::walk($child);
            if (!in_array($tag, self::KEEP, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            if (!self::attributes($child, $tag)) {
                $node->removeChild($child);
            }
        }
    }

    /** Keeps only the allowed attributes, with safe addresses. False when the element should go entirely. */
    private static function attributes(DOMElement $el, string $tag): bool
    {
        $allowed = self::ATTRS[$tag] ?? [];
        foreach (iterator_to_array($el->attributes) as $attr) {
            if (!in_array(strtolower($attr->nodeName), $allowed, true)) {
                $el->removeAttributeNode($attr);
            }
        }

        if ($tag === 'a') {
            $href = self::url($el->getAttribute('href'));
            if ($href === null) {
                $el->removeAttribute('href');
            } else {
                $el->setAttribute('href', $href);
            }
            if (strtolower($el->getAttribute('target')) === '_blank') {
                $el->setAttribute('target', '_blank');
                $el->setAttribute('rel', 'noopener noreferrer');
            } else {
                $el->removeAttribute('target');
            }
        }
        if ($tag === 'img') {
            $src = self::url($el->getAttribute('src'), true);
            if (!$src) {
                return false;
            }
            $el->setAttribute('src', $src);
            $el->setAttribute('alt', trim($el->getAttribute('alt')));
            $el->setAttribute('loading', 'lazy');
            $el->setAttribute('decoding', 'async');
        }
        if ($tag === 'iframe') {
            if (!preg_match(self::EMBEDS, trim($el->getAttribute('src')))) {
                return false;
            }
            $el->setAttribute('loading', 'lazy');
            $el->setAttribute('allowfullscreen', '');
        }
        return true;
    }

    /** A safe address, made site-relative when it points at this shop. Null for javascript:, data: and the like. */
    private static function url(string $url, bool $image = false): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        $bare = strtolower(preg_replace('/[\x00-\x20]+/', '', $url));
        $scheme = preg_match('#^([a-z][a-z0-9+.\-]*):#', $bare, $m) ? $m[1] : null;
        if ($scheme && !in_array($scheme, $image ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'], true)) {
            return null;
        }
        $url = preg_replace(self::OWN_SITE, '', $url);
        return $url === '' ? '/' : $url;
    }

    private static function rename(DOMElement $el, string $tag): DOMElement
    {
        $new = $el->ownerDocument->createElement($tag);
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }
        $el->parentNode->replaceChild($new, $el);
        return $new;
    }

    private static function blank(string $text): bool
    {
        return preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{FEFF}]+/u', '', $text) === '';
    }
}
