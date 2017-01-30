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

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Product;
use Eccube\Event\EventArgs;
use Eccube\Common\Constant;
use Eccube\Event\TemplateEvent;
use Plugin\ProductExternalLink\Entity\ProductMaker;
use Plugin\ProductExternalLink\Repository\ProductMakerRepository;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Maker.
 * New event on version EC-CUBE version >= 3.0.9 (new hook point).
 */
class Maker extends CommonEvent
{
    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Add/Edit product trigger.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        log_info('Event: product maker hook into the product render start.');
        /**
         * @var FormBuilder $builder
         */
        $builder = $event->getArgument('builder');

        // Remove old extension
        $builder->remove('pel_maker')
            ->remove('pel_maker_url');

        $target = '_blank';

        // Add new extension
        $builder
            ->add('plg_pel_maker', 'entity', array(
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
            ->add('plg_pel_maker_url', 'text', array(
                'label' => 'URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                ),
                'mapped' => false,
                'attr' => array(
                    'placeholder' => $this->app->trans('admin.plugin.pel.placeholder.url'),
                ),
            ))
            ->add('plg_other_url', 'text', array(
                'label' => 'その他のURL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                ),
                'mapped' => false,
            ))
            ->add('plg_other_url_target', 'checkbox', array(
                'label' => 'その他のURLを別のウィンドウで開く',
                'required' => false,
                'mapped' => false,
                'value' => $target,
            ))
            ->add('plg_disabled', 'choice', array(
                'label' => false,
                'expanded' => true,
                'choices' => array(
                    Constant::DISABLED => '有効',
                    Constant::ENABLED => '無効',
                ),
                'mapped' => false,
            ))
        ;
        $builder->get('plg_other_url_target')->addModelTransformer(new CallbackTransformer(
            function ($string) use ($target) {
                return $string == $target;
            },
            function ($bool) use ($target) {
                return $bool ? $target : '';
            }
        ));

        /**
         * @var Product $Product
         */
        $Product = $event->getArgument('Product');
        $id = $Product->getId();

        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = null;

        if ($id) {
            /**
             * @var ProductMakerRepository $repository
             */
            $repository = $this->app['eccube.plugin.pel.repository.product_maker'];
            $ProductMaker = $repository->find($id);
        }

        if (!$ProductMaker) {
            log_info('Event: Product maker not found!', array('Product id' => $id));

            return;
        }

        $builder->get('plg_pel_maker')->setData($ProductMaker->getMaker());
        $builder->get('plg_pel_maker_url')->setData($ProductMaker->getMakerUrl());
        $builder->get('plg_other_url')->setData($ProductMaker->getOtherUrl());
        $builder->get('plg_other_url_target')->setData($ProductMaker->getOtherUrlTarget());
        $builder->get('plg_disabled')->setData($ProductMaker->isDisabled());
        log_info('Event: product maker hook into the product render end.');
    }

    /**
     * New Event:function on version >= 3.0.9 (new hook point)
     * Save event.
     *
     * @param EventArgs $eventArgs
     */
    public function onAdminProductEditComplete(EventArgs $eventArgs)
    {
        log_info('Event: product maker hook into the product management complete start.');
        /**
         * @var Form $form
         */
        $form = $eventArgs->getArgument('form');

        /**
         * @var Product $Product
         */
        $Product = $eventArgs->getArgument('Product');

        /**
         * @var ProductMakerRepository $repository
         */
        $repository = $this->app['eccube.plugin.pel.repository.product_maker'];
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $repository->find($Product);
        if (!$ProductMaker) {
            $ProductMaker = new ProductMaker();
        }

        $maker = $form->get('plg_pel_maker')->getData();
        $makerUrl = $form->get('plg_pel_maker_url')->getData();

        $ProductMaker
            ->setId($Product->getId())
            ->setMaker($maker)
            ->setMakerUrl($makerUrl)
            ->setDelFlg(Constant::DISABLED)
            ->setOtherUrl($form->get('plg_other_url')->getData())
            ->setOtherUrlTarget($form->get('plg_other_url_target')->getData())
            ->setDisabled($form->get('plg_disabled')->getData())
        ;
        /**
         * @var EntityRepository $this->app['orm.em']
         */
        $this->app['orm.em']->persist($ProductMaker);
        $this->app['orm.em']->flush($ProductMaker);
        log_info('Event: product maker save success!', array('Product id' => $ProductMaker->getId()));

        log_info('Event: product maker hook into the product management complete end.');
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        log_info('Event: product maker hook into the product detail start.');

        $parameters = $event->getParameters();
        /**
         * @var Product $Product
         */
        $Product = $parameters['Product'];

        if (!$Product) {
            return;
        }

        /**
         * @var ProductMakerRepository $repository
         */
        $repository = $this->app['eccube.plugin.pel.repository.product_maker'];
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $repository->find($Product);
        if (!$ProductMaker) {
            log_info('Event: product maker not found.', array('Product id' => $Product->getId()));

            return;
        }

        $Maker = $ProductMaker->getMaker();

        if (!$Maker) {
            log_info('Event: maker not found.', array('Product maker id' => $ProductMaker->getId()));
            // 商品メーカーマスタにデータが存在しないまたは削除されていれば無視する
        }

        /**
         * @var \Twig_Environment $twig
         */
        $twig = $this->app['twig'];

        $twigAppend = $twig->getLoader()->getSource('ProductExternalLink/Resource/template/default/maker.twig');

        /**
         * @var string $twigSource twig template.
         */
        $twigSource = $event->getSource();

        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->makerTag);

        $twigAppend = $twig->getLoader()->getSource('ProductExternalLink/Resource/template/default/detail_other_url.twig');
        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->otherUrlTag);

        $event->setSource($twigSource);

        $parameters['pel_maker_name'] = $Maker ? $Maker->getName() : '';
        $parameters['pel_maker_url'] = $ProductMaker->getMakerUrl();
        $parameters['pel_disabled'] = $ProductMaker->isDisabled();
        $parameters['pel_ProductMaker'] = $ProductMaker;
        $event->setParameters($parameters);
        log_info('Event: product maker render success.', array('Product id' => $ProductMaker->getId()));
        log_info('Event: product maker hook into the product detail end.');
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductList(TemplateEvent $event)
    {
        log_info('Event: product maker hook into the product list start.');

        $parameters = $event->getParameters();
        /** @var Product[] $Products */
        $Products = $parameters['pagination'];

        if (!$Products) {
            return;
        }

        $pel_ProductMakers = array();

        foreach ($Products as $Product) {
            $pel_ProductMakers[$Product->getId()] = $this->app['eccube.plugin.pel.repository.product_maker']->find($Product);
        }

        /** @var \Twig_Environment $twig */
        $twig = $this->app['twig'];
        $twigSource = $event->getSource();

        $twigAppend = $twig->getLoader()->getSource('ProductExternalLink/Resource/template/default/list_maker.twig');
        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->makerTag);

        $twigAppend = $twig->getLoader()->getSource('ProductExternalLink/Resource/template/default/list_other_url.twig');
        $twigSource = $this->renderPosition($twigSource, $twigAppend, $this->otherUrlTag);

        $event->setSource($twigSource);

        $parameters['pel_ProductMakers'] = $pel_ProductMakers;
        $event->setParameters($parameters);
        log_info('Event: product maker render success.');
        log_info('Event: product maker hook into the product list end.');
    }
}
