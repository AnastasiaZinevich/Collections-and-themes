<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;


use App\Entity\Comment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;

#[ORM\Entity]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;
  
    #[ORM\Column(type: 'json')]
    private $tags = [];

    
    #[ORM\Column(type: 'json')]
    private $customFields = [];

    #[ORM\ManyToOne(targetEntity: Collection::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]

    private $collection;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'item', cascade: ['persist', 'remove'])]

    private $likes;


    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'item', cascade: ['persist', 'remove'])]
    private $comments;

  /**
     * @ORM\Column(type="integer")
     */
    private $likeCount = 0;




    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

   
    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }
    public function getLikeCount(): ?int
    {
        return $this->likeCount;
    }
    public function getLikesCount(): int
    {
        return $this->likes->count();
    }
    public function incrementLikeCount(): void
    {
        $this->likeCount++;
    }
    public function setCustomFields(array $customFields): static
    {
        $this->customFields = $customFields;

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


      /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

      /**
     * @return Collection|Comment[]
     */
    public function getComments(): DoctrineCollection
    {
        return $this->comments;
    }

}
