<?php

namespace App\Entity;

use App\Application\Sonata\ClassificationBundle\Entity\Category;
use App\Application\Sonata\ClassificationBundle\Entity\Collection;
use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Security\Voter\UVCObjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\ArticleController;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    use TimestampableEntity;

    public const SIZE_30 = 0;
    public const SIZE_70 = 1;

    public const SIZE_30_TEXT = '30%';
    public const SIZE_70_TEXT = '70%';

    public const SIZES = [self::SIZE_30_TEXT => self::SIZE_30, self::SIZE_70_TEXT => self::SIZE_70];

    public const STATUS_NEW = 'new';
    public const STATUS_ACCEPTANCE = 'acceptance';
    public const STATUS_LIVE = 'live';
    public const STATUS_ARCHIVED = 'archived';

    public const ALL_STATUS = [
        self::STATUS_NEW => 'neu',
        self::STATUS_ACCEPTANCE => 'in Abnahme',
        self::STATUS_LIVE => 'live',
        self::STATUS_ARCHIVED => 'archiviert',
    ];

    public const NONE_CONTENT = 0;
    public const NONE_CONTENT_TEXT = 'Keiner';
    public const AUTO_CONTENT = 1;
    public const AUTO_CONTENT_TEXT = 'Automatisch';
    public const DEFINED_CONTENT = 2;
    public const DEFINED_CONTENT_TEXT = 'Manual';

    public const RELATED_CONTENT_TYPES = [
        self::NONE_CONTENT_TEXT => self::NONE_CONTENT,
        self::AUTO_CONTENT_TEXT => self::AUTO_CONTENT,
        self::DEFINED_CONTENT_TEXT => self::DEFINED_CONTENT
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(unique=true, fields={"title"}, updatable=true)
     */
    private $slug;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     * @Assert\All(constraints={@Assert\NotBlank()})
     */
    private $accessRoles = [];

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\ClassificationBundle\Entity\Category")
     */
    private $category;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $showAsTeaser = false;

    /**
     * @var SonataPagePage
     * @ORM\ManyToOne(targetEntity="App\Entity\SonataPagePage", inversedBy="articles")
     */
    private $page;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ArticleHasBlock", mappedBy="article", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $articleHasBlocks;

    /**
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", fetch="EAGER")
     * @ORM\JoinTable(name="related_articles",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="related_article_id", referencedColumnName="id")}
     *      )
     */
    private $relatedArticles;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $killDate;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $liveDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     * @Gedmo\Timestampable(on="create")
     */
    private $publicationDate;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $relatedContentType = 0;

    /**
     * @var Media|null
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media")
     */
    private $headerPicture;

    /**
     * @var Media|null
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media")
     */
    private $teaserPicture;

    /**
     * @var string | null
     * @ORM\Column(type="string", nullable=true)
     */
    private $teaserTitle;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    private $teaserText;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $size;

    /**
     * @var int | null
     * @ORM\Column(type="integer")
     * @ORM\Version()
     */
    private $version;

    /**
     * @var string | null
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @var string | null
     * @ORM\Column(type="string", nullable=true)
     */
    private $previewToken;

    public function __construct()
    {
        $this->articleHasBlocks = new ArrayCollection();
        $this->relatedArticles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getKillDate(): ?\DateTime
    {
        return $this->killDate;
    }

    /**
     * @param \DateTime|null $killDate
     * @return self
     */
    public function setKillDate(?\DateTime $killDate): self
    {
        $this->killDate = $killDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLiveDate(): ?\DateTime
    {
        return $this->liveDate;
    }

    /**
     * @param \DateTime|null $liveDate
     * @return self
     */
    public function setLiveDate(?\DateTime $liveDate): self
    {
        $this->liveDate = $liveDate;

        return $this;
    }

    /**
     * @return \DateTime | null
     */
    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    /**
     * @param \DateTime $publicationDate
     */
    public function setPublicationDate(\DateTime $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return int
     */
    public function getRelatedContentType(): int
    {
        return $this->relatedContentType;
    }

    /**
     * @param int $relatedContentType
     */
    public function setRelatedContentType(int $relatedContentType): void
    {
        $this->relatedContentType = $relatedContentType;
    }

    /**
     * @return Media|null
     */
    public function getHeaderPicture(): ?Media
    {
        return $this->headerPicture;
    }

    /**
     * @param Media|null $headerPicture
     * @return self
     */
    public function setHeaderPicture(?Media $headerPicture): self
    {
        $this->headerPicture = $headerPicture;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getTeaserPicture(): ?Media
    {
        return $this->teaserPicture;
    }

    /**
     * @param Media|null $teaserPicture
     * @return Article
     */
    public function setTeaserPicture(?Media $teaserPicture): Article
    {
        $this->teaserPicture = $teaserPicture;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTeaserTitle(): ?string
    {
        return $this->teaserTitle;
    }

    /**
     * @param string|null $teaserTitle
     * @return Article
     */
    public function setTeaserTitle(?string $teaserTitle): Article
    {
        $this->teaserTitle = $teaserTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getTeaserText(): ?string
    {
        return $this->teaserText;
    }

    /**
     * @param string $teaserText
     * @return self
     */
    public function setTeaserText(?string $teaserText): self
    {
        $this->teaserText = $teaserText;

        return $this;
    }

    /**
     * @param mixed $id
     * @return Article
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array
     */
    public function getAccessRoles(): array
    {
        return array_values($this->accessRoles);
    }

    /**
     * @param array $accessRoles
     * @return Article
     */
    public function setAccessRoles(array $accessRoles): Article
    {
        $this->accessRoles = $accessRoles;
        return $this;
    }

    public function __toString()
    {
        return $this->title ?? 'New Article';
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getShowAsTeaser(): ?bool
    {
        return $this->showAsTeaser;
    }

    public function setShowAsTeaser(bool $showAsTeaser): self
    {
        $this->showAsTeaser = $showAsTeaser;

        return $this;
    }

    public function getPage(): ?SonataPagePage
    {
        return $this->page;
    }

    public function setPage(?SonataPagePage $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @param ArrayCollection $articleHasBlocks
     * @return Article
     */
    public function setArticleHasBlocks(ArrayCollection $articleHasBlocks): Article
    {
        $this->articleHasBlocks = $articleHasBlocks;
        return $this;
    }

    public function getArticleHasBlocks()
    {
        return $this->articleHasBlocks;
    }

    public function addArticleHasBlock(ArticleHasBlock $articleHasBlock): self
    {
        if (!$this->articleHasBlocks->contains($articleHasBlock)) {
            $this->articleHasBlocks[] = $articleHasBlock;
            $articleHasBlock->setArticle($this);
        }

        return $this;
    }

    public function removeArticleHasBlock(ArticleHasBlock $articleHasBlock): self
    {
        if ($this->articleHasBlocks->contains($articleHasBlock)) {
            $this->articleHasBlocks->removeElement($articleHasBlock);
            // set the owning side to null (unless already changed)
            if ($articleHasBlock->getArticle() === $this) {
                $articleHasBlock->setArticle(null);
            }
        }

        return $this;
    }

    public function getRelatedArticles()
    {
        return $this->relatedArticles;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Article
     */
    public function setStatus(?string $status): Article
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreviewToken(): ?string
    {
        return $this->previewToken;
    }

    /**
     * @param string|null $previewToken
     * @return Article
     */
    public function setPreviewToken(?string $previewToken): Article
    {
        $this->previewToken = $previewToken;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    public function getRequiredRoleMatches()
    {
        return (int) count($this->getAccessRoles());
    }

    public function __clone()
    {
        $this->setId(null);
        $this->setTitle($this->getTitle() . ' (Kopie)');

        $articleHasBlockCollection = $this->getArticleHasBlocks();
        $this->setArticleHasBlocks(new ArrayCollection());

        /** @var ArticleHasBlock $articleHasBlock */
        foreach ($articleHasBlockCollection as $articleHasBlock) {
            $cloneArticleHasBlock = clone $articleHasBlock;
            $cloneArticleHasBlock->setArticle($this);
            $this->addArticleHasBlock($cloneArticleHasBlock);
        }
    }
}
