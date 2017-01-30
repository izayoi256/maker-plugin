<?php
/*
 * This file is part of the ProductExternalLink plugin
 *
 * Copyright (C) 2017 Shotaro HAMA All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductExternalLink\Form\Extension\Admin;

use Doctrine\ORM\EntityRepository;
use Eccube\Application;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductMakerTypeExtension.
 */
class ProductMakerTypeExtension extends AbstractTypeExtension
{
    private $app;

    /**
     * ProductMakerTypeExtension constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pel_maker', 'entity', array(
                'label' => 'メーカー',
                'class' => 'Plugin\ProductExternalLink\Entity\Maker',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('m')->orderBy('m.rank', 'DESC');
                },
                'property' => 'name',
                'required' => false,
                'empty_value' => '',
                'mapped' => false,
            ))
            ->add('pel_maker_url', 'text', array(
                'label' => 'URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                ),
                'mapped' => false,
                'attr' => array(
                    'placeholder' => $this->app->trans('admin.plugin.pel.placeholder.url'),
                ),
            ));
    }

    /**
     * @return string
     */
    public function getExtendedType()
    {
        return 'admin_product';
    }
}
