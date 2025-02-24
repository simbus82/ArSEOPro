<?php
/**
* 2012-2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Areama <contact@areama.net>
*  @copyright 2018 Areama
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*/

class ArSeoProSitemapWriter
{
    public $xml;
    public $indexXml;
    public $owner;
    
    public function __construct($owner)
    {
        $this->owner = $owner;
    }
    
    public function startSitemap($file, $images = false, $alternates = true)
    {
        $this->xml = new XMLWriter();
        $this->xml->openURI($file);
        $this->xml->startDocument('1.0', 'utf-8');
        $this->xml->startElement('urlset');
        $this->xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        if ($images) {
            $this->xml->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        }
        if ($alternates) {
            $this->xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        }
        $this->xml->setIndent(true);
    }
    
    public function startIndexSitemap($file)
    {
        $this->indexXml = new XMLWriter();
        $this->indexXml->openURI($file);
        $this->indexXml->startDocument('1.0', 'utf-8');
        $this->indexXml->startElement('sitemapindex');
        $this->indexXml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->indexXml->setIndent(true);
    }
    
    public function addSitemaps($nodes)
    {
        foreach ($nodes as $node) {
            $this->indexXml->startElement('sitemap');
            foreach ($node as $el => $val) {
                if (!is_array($val)) {
                    $this->indexXml->startElement($el);
                    $this->indexXml->text($val);
                    $this->indexXml->endElement();
                }
            }
            $this->indexXml->endElement();
        }
    }
    
    public function endIndexSitemap()
    {
        $this->indexXml->endElement();
        $this->indexXml->flush();
    }
    
    public function addXmlNodes($nodes)
    {
        foreach ($nodes as $node) {
            $this->xml->startElement('url');
            foreach ($node as $el => $val) {
                if (!is_array($val) && !in_array($el, array('_id', '_lang', '_id_lang', '_type', '_link_rewrite', '_title', '_id_product_attribute'))) {
                    $this->xml->startElement($el);
                    $this->xml->text($val);
                    $this->xml->endElement();
                }
                if ($el == '_alternates') {
                    foreach ($val as $alt) {
                        $this->xml->startElement('xhtml:link');
                        $this->xml->writeAttribute('rel', 'alternate');
                        foreach ($alt as $attr => $value) {
                            $this->xml->writeAttribute($attr, $value);
                        }
                        $this->xml->endElement();
                    }
                }
                if ($el == '_images') {
                    foreach ($val as $image) {
                        $this->xml->startElement('image:image');
                        foreach ($image as $attr => $value) {
                            $this->xml->startElement('image:' . $attr);
                            $this->xml->text($value);
                            $this->xml->endElement();
                        }
                        $this->xml->endElement();
                    }
                }
            }
            $this->xml->endElement();
        }
    }
    
    public function endSitemap()
    {
        $this->xml->endElement();
        $this->xml->flush();
    }
}
