<?php
/**
 * Created by PhpStorm.
 * User: plazm
 * Date: 5/1/14
 * Time: 5:20 PM
 */

namespace tsCMS\PageBundle\Form;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType {

    private $pageId;

    function __construct($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "tsCMS_page_pagetype";
    }

    /**
     * @return mixed
     */
    private function getPageId()
    {
        return $this->pageId;
    }



    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $id = $this->getPageId();
        $builder
            ->add("title", "text", array(
                "required" => false, 
                "label"  => "page.title",
                "attr" => array(
                    "class" => "pageTitle"
                )
            ))
            ->add("routeConfig", "route", array(
                "required" => false, 
                "label"  => 'page.path',
                "attr" => array(
                    "class" => "pagePath"
                )
            ))
            ->add("parent", "extended_entity", array(
                "label"  => "page.parent",
                "class"    => "tsCMS\PageBundle\Entity\Page",
                "required" => false,
                "option_attributes" => array("data-path" => "routeConfig.path"),
                "attr" => array(
                    "class" => "pageParent"
                ),
                "query_builder" => function(EntityRepository $er) use($id)
                    {
                        $qb = $er->createQueryBuilder("p")
                            ->where("p.parent IS NOT NULL")
                            ->orderBy("p.title");
                        if ($id) {
                            $qb
                            ->andWhere("p.id <> :id")
                            ->setParameter("id", $id);
                        }
                        return $qb;
                    },
            ))
            ->add("content", "editor", array("required" => false, "label"  => "page.content"))
            ->add("save", "submit", array("label"  => "page.save"))
        ;

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'tsCMS\PageBundle\Entity\Page'
        ));
    }
}