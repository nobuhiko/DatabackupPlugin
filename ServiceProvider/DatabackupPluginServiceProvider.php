<?php

/*
 * This file is part of the DatabackupPlugin
 *
 * Copyright (C) 2018 Nobuhiko Kimoto
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\DatabackupPlugin\ServiceProvider;

use Eccube\Application;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\DatabackupPlugin\Form\Type\DatabackupPluginConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;


class DatabackupPluginServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // プラグイン用設定画面
        $app->match('/' . $app['config']['admin_route'] . '/plugin/DatabackupPlugin/config', 'Plugin\DatabackupPlugin\Controller\ConfigController::index')->bind('plugin_DatabackupPlugin_config');

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new DatabackupPluginConfigType($app);
            return $types;
        }));

        // Form Extension

        // Repository

        // Service

        // // メッセージ登録
        // $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
        //     $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
        //     $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
        //     if (file_exists($file)) {
        //         $translator->addResource('yaml', $file, $app['locale']);
        //     }
        //     return $translator;
        // }));

        // load config
        // $conf = $app['config'];
        // $app['config'] = $app->share(function () use ($conf) {
        //     $confarray = array();
        //     $path_file = __DIR__ . '/../Resource/config/path.yml';
        //     if (file_exists($path_file)) {
        //         $config_yml = Yaml::parse(file_get_contents($path_file));
        //         if (isset($config_yml)) {
        //             $confarray = array_replace_recursive($confarray, $config_yml);
        //         }
        //     }

        //     $constant_file = __DIR__ . '/../Resource/config/constant.yml';
        //     if (file_exists($constant_file)) {
        //         $config_yml = Yaml::parse(file_get_contents($constant_file));
        //         if (isset($config_yml)) {
        //             $confarray = array_replace_recursive($confarray, $config_yml);
        //         }
        //     }

        //     return array_replace_recursive($conf, $confarray);
        // });

        // ログファイル設定
        $app['monolog.DatabackupPlugin'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('plugin.DatabackupPlugin');

            $file = $app['config']['root_dir'] . '/app/log/DatabackupPlugin.log';
            $RotateHandler = new RotatingFileHandler($file, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'DatabackupPlugin_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::INFO)
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }
}
