<?php
/*
 * This file is part of the DatabackupPlugin
 *
 * Copyright (C) 2016 Nobuhiko Kimoto
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\DatabackupPlugin\Controller;

use Eccube\Application;
use Eccube\Util\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/../vendor/autoload.php';
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use Goodby\CSV\Export\Standard\CsvFileObject;
use Goodby\CSV\Export\Standard\Collection\CallbackCollection;
use Goodby\CSV\Export\Standard\Collection\PdoCollection;

use \wapmorgan\UnifiedArchive\UnifiedArchive;

class ConfigController
{

    /**
     * DatabackupPlugin用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder('DatabackupPlugin_config')->getForm();
        $errors = array();

        if ('POST' === $request->getMethod()) {

            $em = $app['db'];
            $app['db.config']->setSQLLogger(null);

            //$response = new StreamedResponse();
            //$response->setStatusCode(200);
            //$response->headers->set('Content-Type', 'text/csv');

            $sm = $em->getSchemaManager();
            $tables = $sm->listTables();

            $config = new ExporterConfig();
            $config->setFileMode(CsvFileObject::FILE_MODE_APPEND);
            $exporter = new Exporter($config);
            $exporter->unstrict();

            $service = $app['eccube.service.plugin'];
            $this->bkup_dir = $service->createTempDir();
            $this->bkup_dir = $this->bkup_dir . '/';

            foreach ($tables as $table) {

                $filename = $this->bkup_dir.$table->getName().'.csv';
                $exporter->export($filename, array(array_keys($table->getColumns())));

                $stmt = $em->prepare('SELECT '.implode(',', array_keys($table->getColumns())).' FROM '. $table->getName());
                $stmt->execute();
                $exporter->export($filename, new PdoCollection($stmt->getIterator()));
            }

            $filename = __DIR__ . '/../Resource/backup/backup' . '.tar.gz';
            //圧縮フラグTRUEはgzip圧縮をおこなう
            $tar = new \Archive_Tar($filename, TRUE);

            //bkupフォルダに移動する
            chdir($this->bkup_dir);

            //圧縮をおこなう
            $zip = $tar->create("./");

            $fs = new Filesystem();
            $fs->remove($this->bkup_dir);

            $response = new Response();
            $response->headers->set('Content-type', 'application/octect-stream');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s";', basename($filename)));
            $response->sendHeaders();
            $response->setContent(readfile($filename));

            return $response;
        }

        return $app->render('DatabackupPlugin/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
            'errors' => $errors,
        ));
    }
}
