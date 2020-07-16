<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ArticleHasBlock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ArticleBlock", inversedBy="articleHasBlocks", cascade={"persist"})
     */
    private $block;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="articleHasBlocks")
     */
    private $article;

    /**
     * @param mixed $id
     * @return ArticleHasBlock
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getBlock(): ?ArticleBlock
    {
        return $this->block;
    }

    public function setBlock(?ArticleBlock $block): self
    {
        $this->block = $block;

        return $this;
    }

    public function __clone()
    {
        $this->setId(null);

        $cloneBlock = clone $this->getBlock();
        $cloneBlock->addArticleHasBlock($this);

        $this->setBlock($cloneBlock);
    }
}
