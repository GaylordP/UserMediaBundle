<?php

namespace GaylordP\UserMediaBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use GaylordP\UserBundle\Annotation\CreatedAt;
use GaylordP\UserBundle\Annotation\CreatedBy;
use GaylordP\UserBundle\Entity\Traits\Deletable;

/**
 * UserMedia
 *
 * @ORM\MappedSuperclass
 */
class UserMedia
{
    public const NUM_ITEMS = 4;

    use Deletable;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Media
     *
     * @ORM\OneToOne(
     *     targetEntity="GaylordP\UserMediaBundle\Entity\Media",
     *     cascade={"persist"},
     *     fetch="EAGER"
     * )
     */
    protected $media;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @CreatedAt
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     fetch="EAGER"
     * )
     * @CreatedBy
     */
    protected $createdBy;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get media
     *
     * @return Media
     */
    public function getMedia(): ?Media
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param Media $media
     */
    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $date
     */
    public function setCreatedAt(\DateTime $date): void
    {
        $this->createdAt = $date;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param User $user
     */
    public function setCreatedBy(User $user): void
    {
        $this->createdBy = $user;
    }
}
