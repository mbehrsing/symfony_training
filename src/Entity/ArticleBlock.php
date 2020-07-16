<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BaseBlock;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="article_block")
 * @ORM\HasLifecycleCallbacks
 */
class ArticleBlock extends BaseBlock implements BlockContextInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $settings;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var BlockInterface|null
     */
    protected $parent;

    /**
     * @var BlockInterface[]
     */
    protected $children;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ArticleHasBlock", mappedBy="block", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $articleHasBlocks;

    public function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
        $this->articleHasBlocks = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection|ArticleHasBlock[]
     */
    public function getArticleHasBlocks(): Collection
    {
        return $this->articleHasBlocks;
    }

    /**
     * @param ArrayCollection $articleHasBlocks
     * @return ArticleBlock
     */
    public function setArticleHasBlocks(ArrayCollection $articleHasBlocks): ArticleBlock
    {
        $this->articleHasBlocks = $articleHasBlocks;
        return $this;
    }

    public function addArticleHasBlock(ArticleHasBlock $articleHasBlock): self
    {
        if (!$this->articleHasBlocks->contains($articleHasBlock)) {
            $this->articleHasBlocks[] = $articleHasBlock;
            $articleHasBlock->setBlock($this);
        }

        return $this;
    }

    public function removeArticleHasBlock(ArticleHasBlock $articleHasBlock): self
    {
        if ($this->articleHasBlocks->contains($articleHasBlock)) {
            $this->articleHasBlocks->removeElement($articleHasBlock);
            // set the owning side to null (unless already changed)
            if ($articleHasBlock->getBlock() === $this) {
                $articleHasBlock->setBlock(null);
            }
        }

        return $this;
    }

    public function getBlock()
    {
        return $this;
    }

    public function getTemplate()
    {
        return;
    }

    public function getName()
    {
        return  $this->name ?? $this->type;
    }

    public function __clone()
    {
        $this->setId(null);
        $this->setArticleHasBlocks(new ArrayCollection());
    }
}
