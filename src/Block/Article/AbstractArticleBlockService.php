<?php

namespace App\Block\Article;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\AdminBlockServiceInterface;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class AbstractArticleBlockService extends AbstractBlockService implements AdminBlockServiceInterface
{
    protected $identifier;

    /**
     * AbstractArticleBlockService constructor.
     * @param null $name
     * @param EngineInterface|null $templating
     */
    public function __construct($name = null, EngineInterface $templating = null, $identifier = null)
    {
        parent::__construct($name, $templating);

        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param FormMapper $form
     * @param BlockInterface $block
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
    }

    /**
     * @param FormMapper $form
     * @param BlockInterface $block
     */
    public function buildCreateForm(FormMapper $form, BlockInterface $block)
    {
        $this->buildEditForm($form, $block);
    }

    /**
     * @param ErrorElement $errorElement
     * @param BlockInterface $block
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
    }

    /**
     * @param null $code
     * @return Metadata|\Sonata\BlockBundle\Meta\MetadataInterface
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', ['class' => 'fa fa-file']);
    }

    /**
     * @param BlockInterface $block
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setName($this->getName());
    }

    /**
     * @param BlockInterface $block
     */
    public function postPersist(BlockInterface $block)
    {
    }

    /**
     * @param BlockInterface $block
     */
    public function preUpdate(BlockInterface $block)
    {
    }

    /**
     * @param BlockInterface $block
     */
    public function postUpdate(BlockInterface $block)
    {
    }

    /**
     * @param BlockInterface $block
     */
    public function preRemove(BlockInterface $block)
    {
    }

    /**
     * @param BlockInterface $block
     */
    public function postRemove(BlockInterface $block)
    {
    }
}
