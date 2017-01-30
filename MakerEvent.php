<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\ProductExternalLink\Event\Maker;
use Plugin\ProductExternalLink\Event\MakerLegacy;
use Plugin\ProductExternalLink\Util\Version;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class MakerEvent.
 */
class MakerEvent
{
    /**
     * @var Application
     */
    private $app;

    /**
     * MakerEvent constructor.
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    /**
     * New event function on version >= 3.0.9 (new hook point).
     * Add/Edit product render trigger.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker'];
        $makerEvent->onAdminProductEditInitialize($event);
    }

    /**
     * New Event:function on version >= 3.0.9 (new hook point).
     * Save event.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker'];
        $makerEvent->onAdminProductEditComplete($event);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker'];
        $makerEvent->onRenderProductDetail($event);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product list render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductList(TemplateEvent $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker'];
        $makerEvent->onRenderProductList($event);
    }

    /**
     * Add product trigger.
     *
     * @param FilterResponseEvent $event
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     */
    public function onRenderAdminProduct(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        /* @var MakerLegacy $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker_legacy'];
        $makerEvent->onRenderAdminProduct($event);
    }

    /**
     * Product detail render (front).
     *
     * @param FilterResponseEvent $event
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     */
    public function onRenderProductDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        /* @var MakerLegacy $makerEvent */
        $makerEvent = $this->app['eccube.plugin.pel.event.maker_legacy'];
        $makerEvent->onRenderProductDetailBefore($event);
    }

    public function onRenderAdminProductEdit(TemplateEvent $event)
    {
        $replacement = $this->app->renderView('ProductExternalLink/Resource/template/admin/product_script.twig');
        $source = preg_replace('/(\{%\s*block\s+javascript\s*%\})/ui', '$1' . $replacement, $event->getSource());
        $event->setSource($source);
    }

    /**
     * v3.0.9以降のフックポイントに対応しているのか.
     *
     * @return bool
     */
    private function supportNewHookPoint()
    {
        return Version::isSupport();
    }
}
