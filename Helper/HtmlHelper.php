<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Helper;

class HtmlHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param string $html
     * @param string $selector XPath, example - "//li"
     * @return string - html from selected tag(s)
     */
    function extractHtmlForTag($html, $selector)
    {
        $html = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';
        $doc = new \DomDocument();
        @$doc->loadHTML($html);
        $finder = new \DOMXPath($doc);
        $nodes = $finder->query($selector);
        $tagHtml = '';
        foreach ($nodes as $node) {
            /** @var \DOMNode $node */
            $tagHtml .= $node->ownerDocument->saveHTML($node);
        }
        return $tagHtml;
    }
}