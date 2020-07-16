<?php

namespace App\Block;

use App\Entity\Article;
use App\Entity\BestList;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleBlockService extends AbstractAdminBlockService
{
    protected $adminPool;

    public function __construct(Pool $adminPool, EngineInterface $template)
    {
        parent::__construct('Article-Block', $template);
        $this->adminPool = $adminPool;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $block = $blockContext->getBlock();

        /** @var Article|null $article */
        $article = $block->getSetting('article');

        if (!$article || !$article->isActive()) {
            return new Response();
        }

        return $this->renderResponse('Block/text.html.twig', array(
            'block' => $block,
            'headline' => $article->getTitle(),
        ), $response);
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'article' => null,
            'articleId' => null,
        ]);
    }

    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $this->load($block);

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => array(
                array($this->getBuilderForAdmin(Article::class, $form, 'article', 'Artikel'), null, ['required' => false])
            )
        ]);
    }


    /**
     * @param FormMapper $formMapper
     * @param $fieldName
     * @param $label
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function getBuilderForAdmin($adminClass, FormMapper $formMapper, $fieldName, $label)
    {
        /** @var AbstractAdmin $admin */
        $admin = $this->adminPool->getAdminByClass($adminClass);

        $field = $admin->getModelManager()->getNewFieldDescriptionInstance(
            $admin->getClass(),
            $fieldName
        );
        $field->setAssociationAdmin($admin);
        $field->setAdmin($formMapper->getAdmin());
        $field->setOption('edit', 'list');
        $field->setFieldName($fieldName);
        $field->setType(ClassMetadataInfo::MANY_TO_ONE);

        return $formMapper->create($fieldName, ModelListType::class, [
            'sonata_field_description' => $field,
            'class' => $admin->getClass(),
            'model_manager' => $admin->getModelManager(),
            'required' => true,
            'label' => $label,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $this->preUpdate($block);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        if (($article = $block->getSetting('article')) instanceof Article) {
            $block->setSetting('articleId', $article->getId());
        }
    }

    public function load(BlockInterface $block)
    {
        if ($articleId = $block->getSetting('articleId')) {
            $block->setSetting('article', $this->adminPool->getAdminByClass(Article::class)->getObject($articleId));
        }
    }
}
