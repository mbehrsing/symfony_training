<?php

namespace App\Admin;

use App\Entity\ArticleHasBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ArticleHasBlockAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $link_parameters = [];

        if ($this->hasParentFieldDescription()) {
            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', []);
        }

        $form
            ->add('block', ModelListType::class, ['btn_list' => false, 'btn_edit' => 'Bearbeiten', 'btn_delete' => false], [
                'link_parameters' => $link_parameters,
            ])
            ->add('position', HiddenType::class);
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id');
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id');
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('id');
    }

    /**
     * @param ArticleHasBlock $object
     */
    public function postRemove($object)
    {
        $object->getArticle()->removeArticleHasBlock($object);
        $object->getBlock()->removeArticleHasBlock($object);
    }
}
