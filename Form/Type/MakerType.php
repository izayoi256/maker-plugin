<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 * Copyright (C) 2017 Shotaro HAMA All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MakerType.
 */
class MakerType extends AbstractType
{
    private $app;

    /**
     * MakerType constructor.
     *
     * @param \Silex\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Build config type form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'メーカー名',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => $this->app->trans('admin.plugin.pel.blank.error'))),
                ),
            ))
            ->add('id', 'hidden', array());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_pel_maker';
    }
}
