<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 * Copyright (C) 2017 Shotaro HAMA All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink;

use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * @param array       $config
     * @param Application $app
     */
    public function install($config, $app)
    {
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function disable($config, $app)
    {
    }

    /**
     * @param array       $config
     * @param Application $app
     */
    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }
}
