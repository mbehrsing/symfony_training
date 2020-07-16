<?php

namespace App\Block\Article;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\ArticleBlock;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaBlockService extends AbstractArticleBlockService
{

    /* @var Pool $pool */
    protected $pool;

    public function __construct($name = null, EngineInterface $templating = null, Pool $pool)
    {
        parent::__construct($name, $templating, $pool);
        $this->pool = $pool;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $block = $blockContext->getBlock();
        $this->load($block);

        return $this->renderResponse('Block/image.html.twig', array(
            'block' => $block,
            'media' => $block->getSetting('media'),
        ), $response);
    }

    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $this->load($block);
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                [$this->getMediaBuilder($form), null, ['required' => true]],
            ],
        ]);
    }

    protected function getMediaBuilder($formMapper)
    {
        /* @var AdminInterface $mediaAdmin */
        $mediaAdmin = $this->pool->getAdminByAdminCode('sonata.media.admin.media');
        $fieldDescription = $mediaAdmin->getModelManager()->getNewFieldDescriptionInstance($mediaAdmin->getClass(), 'media');
        $fieldDescription->setAssociationAdmin($mediaAdmin);
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');

        $fieldDescription->setAssociationMapping(array(
            'fieldName' => 'media',
            'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE
        ));

        return $formMapper->create('media', ModelListType::class, ['sonata_field_description' => $fieldDescription, 'class' => $mediaAdmin->getClass(), 'model_manager' => $mediaAdmin->getModelManager(), 'required' => true, 'btn_add' => 'Neu']);
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'media' => null,
            'mediaId' => null,
        ));
    }

    public function load(BlockInterface $block)
    {
        if ($mediaId = $block->getSetting('mediaId')) {
            $block->setSetting('media', $this->pool->getAdminByAdminCode('sonata.media.admin.media')->getObject($mediaId));
        }
    }

    public function validateBlock($errorElement, BlockInterface $block)
    {
        /** @var Media $media */
        $media = $block->getSetting('media');
    }

    /**
     * @param BlockInterface $block
     * @return void
     */
    public function preUpdate(BlockInterface $block)
    {
        $this->setMediaSetting($block);
    }

    /**
     * @param BlockInterface $block
     * @return void
     */
    public function prePersist(BlockInterface $block)
    {
        $this->setMediaSetting($block);
    }

    /**
     * @param BlockInterface $block
     * @return void
     */
    private function setMediaSetting(BlockInterface $block): void
    {
        $media = $block->getSetting('media');

        if ($media instanceof Media) {
            $block->setSetting('mediaId', $media->getId());
        } elseif (empty($media)) {
            $block->setSetting('mediaId', null);
        }
    }
}
