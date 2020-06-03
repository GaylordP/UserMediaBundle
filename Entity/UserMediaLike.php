<?php

namespace GaylordP\UserMediaBundle\Entity;

use App\Entity\User;
use App\Entity\UserMedia;
use Doctrine\ORM\Mapping as ORM;
use GaylordP\UserBundle\Annotation\CreatedAt;
use GaylordP\UserBundle\Annotation\CreatedBy;
use GaylordP\UserBundle\Entity\Traits\Deletable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserMediaLike
 *
 * @ORM\Entity(repositoryClass="GaylordP\UserMediaBundle\Repository\UserMediaLikeRepository")
 */
class UserMediaLike
{
    use Deletable;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var UserMedia
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\UserMedia",
     *     fetch="EAGER"
     * )
     * @Assert\NotBlank()
     */
    private $userMedia;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @CreatedAt
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     fetch="EAGER"
     * )
     * @CreatedBy
     */
    private $createdBy;

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
     * Get user media
     *
     * @return UserMedia
     */
    public function getUserMedia(): ?UserMedia
    {
        return $this->userMedia;
    }

    /**
     * Set user media
     *
     * @param UserMedia $userMedia
     */
    public function setUserMedia(?UserMedia $userMedia): void
    {
        $this->userMedia = $userMedia;
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
