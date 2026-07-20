<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

trait ExcelXmlHelpers
{
    protected static function xmlMakeRun(DOMDocument $dom, string $ns, string $text, bool $bold = false): \DOMElement
    {
        $r = $dom->createElementNS($ns, 'r');
        $rPr = $dom->createElementNS($ns, 'rPr');
        if ($bold) {
            $rPr->appendChild($dom->createElementNS($ns, 'b'));
        }
        $rFont = $dom->createElementNS($ns, 'rFont');
        $rFont->setAttribute('val', 'Arimo');
        $rPr->appendChild($rFont);
        $sz = $dom->createElementNS($ns, 'sz');
        $sz->setAttribute('val', '12');
        $rPr->appendChild($sz);
        $color = $dom->createElementNS($ns, 'color');
        $color->setAttribute('rgb', 'FF000000');
        $rPr->appendChild($color);
        $r->appendChild($rPr);
        $t = $dom->createElementNS($ns, 't');
        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $t->appendChild($dom->createTextNode($text));
        $r->appendChild($t);
        return $r;
    }

    protected static function xmlSetInlineStr(DOMXPath $xpath, DOMDocument $dom, string $ns, string $ref, string $text): void
    {
        $cells = $xpath->query("//s:c[@r='$ref']");
        if ($cells->length > 0) {
            $cell = $cells->item(0);
            $cell->setAttribute('t', 'inlineStr');
            foreach (['v', 'is'] as $tag) {
                $existing = $cell->getElementsByTagNameNS($ns, $tag)->item(0);
                if ($existing) $cell->removeChild($existing);
            }
            $is = $dom->createElementNS($ns, 'is');
            $t = $dom->createElementNS($ns, 't');
            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
            $t->appendChild($dom->createTextNode($text));
            $is->appendChild($t);
            $cell->appendChild($is);
        }
    }

    protected static function xmlSetNumber(DOMXPath $xpath, DOMDocument $dom, string $ns, string $ref, $value): void
    {
        $cells = $xpath->query("//s:c[@r='$ref']");
        if ($cells->length > 0) {
            $cell = $cells->item(0);
            foreach (['v', 'is', 'f'] as $tag) {
                $existing = $cell->getElementsByTagNameNS($ns, $tag)->item(0);
                if ($existing) $cell->removeChild($existing);
            }
            $cell->removeAttribute('t');
            $v = $dom->createElementNS($ns, 'v');
            $v->textContent = (string) $value;
            $cell->appendChild($v);
        }
    }

    protected static function xmlMakeBRow(DOMDocument $dom, string $ns, string $text, int &$infoRn): \DOMElement
    {
        $rn = $infoRn++;
        $row = $dom->createElementNS($ns, 'row');
        $row->setAttribute('r', (string) $rn);
        $row->setAttribute('spans', '1:15');
        $cell = $dom->createElementNS($ns, 'c');
        $cell->setAttribute('r', 'B' . $rn);
        $cell->setAttribute('t', 'inlineStr');
        $cell->setAttribute('s', '1');
        $is = $dom->createElementNS($ns, 'is');
        $t = $dom->createElementNS($ns, 't');
        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $t->appendChild($dom->createTextNode($text));
        $is->appendChild($t);
        $cell->appendChild($is);
        $row->appendChild($cell);
        return $row;
    }
}
