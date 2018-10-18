<?php

/*
 * This file is part of the DatabackupPlugin
 *
 * Copyright (C) 2016 Nobuhiko Kimoto
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\DatabackupPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DatabackupPluginConfigType extends AbstractType
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;

        $builder
            ->add('import_file', 'file', array(
                'label' => false,
                'mapped' => false,
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'ファイルを選択してください。')),
                    new Assert\File(array(
                        'mimeTypes' => array('application/zip', 'application/x-tar', 'application/x-gzip'),
                        'mimeTypesMessage' => 'zipファイル、tarファイル、tar.gzファイルのいずれかをアップロードしてください。',
                        //'mimeTypes' => array('text/csv', 'text/tsv'),
                        //'mimeTypesMessage' => 'csvをアップロードしてください。',
                    )),
                ),
            ))
            ;
    }

    public function getName()
    {
        return 'DatabackupPlugin_config';
    }
}
