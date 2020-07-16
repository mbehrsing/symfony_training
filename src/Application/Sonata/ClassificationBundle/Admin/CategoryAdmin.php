<?php

namespace App\Application\Sonata\ClassificationBundle\Admin;

use App\Application\Sonata\ClassificationBundle\Entity\Category;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\EqualType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Sonata\ClassificationBundle\Admin\CategoryAdmin as BaseAdmin;

class CategoryAdmin extends BaseAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);
    }

    public function configureRoutes(RouteCollection $routes)
    {
        parent::configureRoutes($routes);
    }
}
