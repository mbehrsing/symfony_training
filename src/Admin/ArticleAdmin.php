<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Knp\Menu\ItemInterface as MenuItemInterface;

final class ArticleAdmin extends AbstractAdmin
{
    private $roles;


    private $articleRepository;

    private $userControlRole;

    public function __construct($code, $class, $baseControllerName, $roles)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->roles = $roles;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('clone', $this->getRouterIdParameter() . '/clone');
    }


    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('title')
            ->add('liveDate')
            ->add('killDate')
            ->add('category')
            ->add('page')
            ->add('accessRoles', null, [], ChoiceType::class, ['choices' => $this->getRoles(), 'multiple' => false])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->addIdentifier('title')
            ->add('accessRoles', 'choice', ['choices' => $this->getRoles(), 'multiple' => true])
            ->add('liveDate')
            ->add('killDate')
            ->add('status', 'choice', ['choices' => Article::ALL_STATUS])
            ->add('category', null, [])
            ->add('page')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'clone' => [
                        'template' => 'Admin/clone_action.html.twig',
                    ],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Allgemein', ['class' => 'col-md-6'])
                ->add('title')
                ->add('slug', null, ['disabled' => true])
                ->add('headerPicture', ModelListType::class);
        $formMapper
                ->add('page', ModelListType::class, [], ['link_parameters' => ['filter' => ['site' => 1]]])
                ->add('category', ModelListType::class, [
                    'btn_edit' => false
                ], [
                    'link_parameters' => ['context' => 'news', 'hide_context' => true],
                ])
                ->add('teaserTitle', TextType::class, ['required' => false])
                ->add('teaserText', TextareaType::class, ['required' => true])

                ->add('teaserPicture', ModelListType::class)
            ->end()
            ->with('Meta', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'choices' => array_flip(Article::ALL_STATUS),
                    'disabled' => true
                ])
                ->add('relatedContentType', ChoiceFieldMaskType::class, [
                    'multiple' => false,
                    'expanded' => false,
                    'map' => [
                        Article::NONE_CONTENT => [],
                        Article::AUTO_CONTENT => [],
                        Article::DEFINED_CONTENT => ['relatedArticles']],
                    'choices' => Article::RELATED_CONTENT_TYPES,
                ])->add(
                    'relatedArticles',
                    ModelType::class,
                    [
                        'multiple' => true,
                        'expanded' => false,     // or false
                        'class' => Article::class,
                        'property' => 'title',   // or any field in your media entity
                        'btn_add' => false,
                        'btn_list' => false,
                        'btn_delete' => true,
                        'btn_catalogue' => 'admin',
                    ],
                )
                ->add('publicationDate', DatePickerType::class, ['required' => true])
                ->add('liveDate', DatePickerType::class, ['required' => false])
                ->add('killDate', DatePickerType::class, ['required' => false])
                ->add('size', ChoiceType::class, ['choices' => Article::SIZES,'required' => true, 'help' => 'Sizes are only relevant for news teaser'])
                ->add('showAsTeaser')
                ->add(
                    'accessRoles',
                    CollectionType::class,
                    [
                        'allow_add' => true,
                        'allow_delete' => true,
                        'entry_type' => ChoiceType::class,
                        'entry_options' => [
                            'choices' => $this->getRoles(),
                            'expanded' => false,
                            'required' => false,
                            'translation_domain' => 'default'
                        ]
                    ]
                )
            ->end()
            ->with('Module')
                ->add(
                    'articleHasBlocks',
                    \Sonata\Form\Type\CollectionType::class,
                    [],
                    [
                        'link_parameters' => [
                            'article' => $this->getSubject() ? $this->getSubject()->getId() : null,
                        ],
                        'sortable' => 'position',
                        'edit' => 'inline',
                        'inline' => 'table',
                        'admin_code' => 'admin.article_has_block',
                    ]
                )
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('title')
            ->add('status')
            ->add('liveDate')
            ->add('killDate')
        ;
    }

    private function getRoles()
    {
        $filteredRoles = [];
        foreach ($this->roles as $role => $inheritedRoles) {
            if (stripos($role, 'ROLE_USER_CONTROL') === 0) {
                $filteredRoles[$role] = $role;
            }
        }

        return $filteredRoles;
    }

    public function preUpdate($object)
    {
        $object = $this->handleBlocks($object);
        $this->handleRelatedArticles($object);
    }

    /**
     * @param Article $object
     */
    public function prePersist($object)
    {
        $object = $this->handleBlocks($object);
        $this->handleRelatedArticles($object);
    }

    /**
     * @param Article $object
     */
    public function postPersist($object)
    {
    }

    /**
     * @param Article $object
     * @return Article
     */
    public function handleBlocks($object)
    {
        foreach ($object->getArticleHasBlocks() as $articleHasBlocks) {
            if ($articleHasBlocks->getBlock() === null) {
                $articleHasBlocks->setArticle(null);
            } else {
                $articleHasBlocks->setArticle($object);
                $object->addArticleHasBlock($articleHasBlocks);
            }
        }

        return $object;
    }

    /**
     * @param Article $object
     */
    public function handleRelatedArticles(Article $object): void
    {
        if ($object->getRelatedContentType() === Article::NONE_CONTENT || $object->getRelatedContentType() === Article::AUTO_CONTENT) {
            foreach ($object->getRelatedArticles() as $relatedArticle) {
                $object->getRelatedArticles()->removeElement($relatedArticle);
            }
        }
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action, $childAdmin);
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object); // TODO: Change the autogenerated stub

        if ($object->getRelatedContentType() === Article::DEFINED_CONTENT && count($object->getRelatedArticles()) < 3) {
            $errorElement->with('relatedArticles')->addViolation('Sie müssen mindestens 3 Auswahlmöglichkeiten auswählen')->end();
        }
    }
}
