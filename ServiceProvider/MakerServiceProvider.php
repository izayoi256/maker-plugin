<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 * Copyright (C) 2017 Shotaro HAMA All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink\ServiceProvider;

use Plugin\ProductExternalLink\Event\Maker;
use Plugin\ProductExternalLink\Event\MakerLegacy;
use Plugin\ProductExternalLink\Form\Extension\Admin\ProductMakerTypeExtension;
use Plugin\ProductExternalLink\Form\Type\MakerType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Eccube\Common\Constant;

// include log functions (for 3.0.0 - 3.0.11)
require_once(__DIR__.'/../log.php');

/**
 * Class MakerServiceProvider.
 */
class MakerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        // 管理画面定義
        $admin = $app['controllers_factory'];
        // 強制SSL
        if ($app['config']['force_ssl'] == Constant::ENABLED) {
            $admin->requireHttps();
        }

        // メーカーテーブル用リポジトリ
        $app['eccube.plugin.pel.repository.maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductExternalLink\Entity\Maker');
        });

        $app['eccube.plugin.pel.repository.product_maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductExternalLink\Entity\ProductMaker');
        });

        // Maker event
        $app['eccube.plugin.pel.event.maker'] = $app->share(function () use ($app) {
            return new Maker($app);
        });

        // Maker legacy event
        $app['eccube.plugin.pel.event.maker_legacy'] = $app->share(function () use ($app) {
            return new MakerLegacy($app);
        });

        // 一覧・登録・修正
        $admin->match('/plugin/pel/{id}', '\\Plugin\\ProductExternalLink\\Controller\\MakerController::index')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_plugin_pel_maker_index');

        // 削除
        $admin->delete('/plugin/pel/{id}/delete', '\\Plugin\\ProductExternalLink\\Controller\\MakerController::delete')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_plugin_pel_maker_delete');

        $admin->post('/plugin/pel/rank/move', '\\Plugin\\ProductExternalLink\\Controller\\MakerController::moveRank')
            ->bind('admin_plugin_pel_maker_move_rank');

        $app->mount('/'.trim($app['config']['admin_route'], '/').'/', $admin);

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new MakerType($app);

            return $types;
        }));

        // Form Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new ProductMakerTypeExtension($app);

            return $extensions;
        }));

        // メッセージ登録
        $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
        $app['translator']->addResource('yaml', $file, $app['locale']);

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'pel';
            $addNavi['name'] = 'メーカー管理';
            $addNavi['url'] = 'admin_plugin_pel_maker_index';

            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('product' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }

            $config['nav'] = $nav;

            return $config;
        }));

        // initialize logger (for 3.0.0 - 3.0.8)
        if (!method_exists('Eccube\Application', 'getInstance')) {
            eccube_log_init($app);
        }
    }

    /**
     * @param BaseApplication $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
