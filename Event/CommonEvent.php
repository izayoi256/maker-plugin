<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 * Copyright (C) 2017 Shotaro HAMA All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink\Event;

use Eccube\Application;

/**
 * Class AbstractEvent.
 */
class CommonEvent
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string target render on the front-end
     */
    protected $makerTag = '<!--# pel-plugin-tag #-->';

    protected $otherUrlTag = '<!--# pel-plugin-other-url-tag #-->';

    /**
     * AbstractEvent constructor.
     * @param \Silex\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Render position
     *
     * @param string $html
     * @param string $part
     * @param string $markTag
     *
     * @return mixed
     */
    protected function renderPosition($html, $part, $markTag = '')
    {
        if (!$markTag) {
            $markTag = $this->makerTag;
        }
        // for plugin tag
        if (strpos($html, $markTag)) {
            $newHtml = $markTag.$part;
            $html = str_replace($markTag, $newHtml, $html);
        } else {
            // For old and new ec-cube version
            $search = '/(<div id="relative_category_box")|(<div class="relative_cat")/';
            $newHtml = $part.'<div id="relative_category_box" class="relative_cat"';
            $html = preg_replace($search, $newHtml, $html);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $part
     * @param string $otherUrlTag
     * @return string
     */
    protected function renderOtherUrl($html, $part, $otherUrlTag = '')
    {
        if (!strlen($otherUrlTag)) {
            $otherUrlTag = $this->otherUrlTag;
        }

        return str_replace($otherUrlTag, $otherUrlTag . $part, $html);
    }
}
