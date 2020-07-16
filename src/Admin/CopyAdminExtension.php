<?php

namespace App\Admin;

use App\Controller\CopyAdminController;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CopyAdminExtension extends AbstractAdminExtension
{
    public function configureListFields(ListMapper $listMapper)
    {
        $cloneOptions = array(
            'template' => 'Admin/clone_action.html.twig'
        );

        if ($listMapper->has('_action')) {
            $actionField = $listMapper->get('_action');
            $fieldOptions = $actionField->getOptions();

            $fieldType = $actionField->getType();
            $fieldOptions['actions']['clone'] = $cloneOptions;

            $listMapper->remove('_action');
            $listMapper->add('_action', $fieldType, $fieldOptions);
        } else {
            $listMapper->add('_action', 'actions', array(
                'actions' => array(
                    'clone' => $cloneOptions,
                ),
                'label' => 'Aktionen'
            ));
        }
    }

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add('clone', $admin->getRouterIdParameter().'/clone', ['_controller' => CopyAdminController::class]);
    }
}
