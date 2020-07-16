<?php

namespace App\Admin;

use App\Application\Sonata\MediaBundle\Entity\Gallery;
use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\ArticleBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockServiceInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleBlockAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $blocks;

    /**
     * @var BlockServiceManagerInterface
     */
    protected $blockManager;

    public function __construct($code, $class, $baseControllerName, BlockServiceManagerInterface $blockManager, array $blocks = [])
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->blockManager = $blockManager;
        $this->blocks = $blocks;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $article = null;
        if ($this->request->get('article')) {
            $article = $this->request->get('article');
        }

        /** @var ArticleBlock $articleBlock */
        $articleBlock = $this->getSubject();

        $this->setLabel($this->getSubject()->getName());

        if (!$articleBlock && !$articleBlock->getId()) {
            $articleBlock = $this->getNewInstance();
        }

        if (!$articleBlock) {
            return;
        }
        $type = $this->request->get('type');

        if ($type) {
            $articleBlock->setType($type);
        }

        if ($article) {
            $articleBlock->setSetting('article', $article);
        }

        $blockType = $articleBlock->getType();

        $service = $this->blockManager->get($articleBlock);

        if ($articleBlock->getId() > 0) {
            $service->load($articleBlock);
            $service->buildEditForm($form, $articleBlock);
        } else {
            $service->buildCreateForm($form, $articleBlock);
        }

        if ($form->has('settings') && isset($this->blocks[$blockType]['templates'])) {
            $settingsField = $form->get('settings');

            if (!$settingsField->has('template')) {
                $choices = [];

                if (null !== $defaultTemplate = $this->getDefaultTemplate($service)) {
                    $choices['default'] = $defaultTemplate;
                }

                foreach ($this->blocks[$blockType]['templates'] as $item) {
                    $choices[$item['name']] = $item['template'];
                }

                if (\count($choices) > 1) {
                    $templateOptions = [
                        'choices' => $choices,
                    ];

                    if ($settingsField->hasOption('choices_as_values')) {
                        $templateOptions['choices_as_values'] = true;
                    }

                    $settingsField->add('template', ChoiceType::class, $templateOptions);
                }
            }
        }

        $form->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('name')
            ->add('settings')
            ->add('enabled')
            ->add('updatedAt')
            ->add('createdAt')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param BlockServiceInterface $blockService
     *
     * @return string|null
     */
    private function getDefaultTemplate(BlockServiceInterface $blockService)
    {
        $resolver = new OptionsResolver();
        // use new interface method whenever possible
        // NEXT_MAJOR: Remove this check and legacy setDefaultSettings method call
        if (method_exists($blockService, 'configureSettings')) {
            $blockService->configureSettings($resolver);
        } else {
            $blockService->setDefaultSettings($resolver);
        }
        $options = $resolver->resolve();

        if (isset($options['template'])) {
            return $options['template'];
        }
    }

    public function getPersistentParameters()
    {
        $type = $this->getRequest()->get('type');
        $article = $this->getRequest()->get('article');
        return array_merge(['type' => $type, 'article' => $article], parent::getPersistentParameters());
    }

    /**
     * @param ArticleBlock $object
     * @return void
     */
    public function preUpdate($object)
    {
        $this->blockManager->get($object)->preUpdate($object);
    }

    /**
     * @param ArticleBlock $object
     * @return void
     */
    public function prePersist($object)
    {
        $this->blockManager->get($object)->prePersist($object);
    }
}
