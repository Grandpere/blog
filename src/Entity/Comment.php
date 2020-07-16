<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "Your content must be at least {{ limit }} characters long",
     *      maxMessage = "Your content cannot be longer than {{ limit }} characters"
     * )
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters"
     * )
     */
    private $authorName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Email(
     *      message = "The email '{{ value }}' is not a valid email."
     * )
     */
    private $authorEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorWebsite;

    /**
     * @ORM\Column(type="integer")
     */
    private $depth;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Comment", inversedBy="childrens")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="parent")
     */
    private $childrens;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isReported;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isModerate;


    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->depth = 0;
        $this->childrens = new ArrayCollection();
        $this->isActive = true;
        $this->isReported = false;
        $this->isModerate = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getAuthorWebsite(): ?string
    {
        return $this->authorWebsite;
    }

    public function setAuthorWebsite(?string $authorWebsite): self
    {
        $this->authorWebsite = $authorWebsite;

        return $this;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function increaseDepth()
    {
        $this->depth += 1;
    }

    public function decreaseDepth()
    {
        $this->depth -= 1;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildrens(): Collection
    {
        return $this->childrens;
    }

    public function addChildren(self $children): self
    {
        if (!$this->childrens->contains($children)) {
            $this->childrens[] = $children;
            $children->setParent($this);
        }

        return $this;
    }

    public function removeChildren(self $children): self
    {
        if ($this->childrens->contains($children)) {
            $this->childrens->removeElement($children);
            // set the owning side to null (unless already changed)
            if ($children->getParent() === $this) {
                $children->setParent(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsReported(): ?bool
    {
        return $this->isReported;
    }

    public function setIsReported(bool $isReported): self
    {
        $this->isReported = $isReported;

        return $this;
    }

    public function getIsModerate(): ?bool
    {
        return $this->isModerate;
    }

    public function setIsModerate(bool $isModerate): self
    {
        $this->isModerate = $isModerate;

        return $this;
    }
}
