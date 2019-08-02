<?php

namespace intermundia\yiicms\formatters;

use DOMDocument;
use DOMElement;

class SitemapXmlResponseFormatter extends \yii\web\XmlResponseFormatter {

    public $rootTag = 'urlset';
    public $itemTag = 'url';

    public $rootAttributes = ['xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'];

    public function format($response)
    {
        $charset = $this->encoding === null ? $response->charset : $this->encoding;
        if (stripos($this->contentType, 'charset') === false) {
            $this->contentType .= '; charset=' . $charset;
        }
        $response->getHeaders()->set('Content-Type', $this->contentType);
        if ($response->data !== null) {
            $dom = new DOMDocument($this->version, $charset);
            if (!empty($this->rootTag)) {
                $root = $dom->createElement($this->rootTag);
                foreach($this->rootAttributes as $rootAttributeName => $rootAttributeValue) {
                    $root->setAttribute($rootAttributeName, $rootAttributeValue);
                }
                $dom->appendChild($root);
                $this->buildXml($root, $response->data);
            } else {
                $this->buildXml($dom, $response->data);
            }
            $response->content = $dom->saveXML();
        }
    }
}