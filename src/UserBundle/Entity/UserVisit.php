<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserVisit
 *
 * @ORM\Table(name="user_visit")
 * @ORM\Entity(repositoryClass="UserBundle\Entity\Repository\UserVisitRepository")
 */
class UserVisit
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="visited_at", type="datetime")
     */
    private $visitedAt;

    /**
     * @param User $user
     */
    public function __construct($user){
        $this->setUserId($user->getId());
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return UserVisit
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set visitedAt
     *
     * @param \DateTime $visitedAt
     *
     * @return UserVisit
     */
    public function setVisitedAt($visitedAt)
    {
        $this->visitedAt = $visitedAt;

        return $this;
    }

    /**
     * Get visitedAt
     *
     * @return \DateTime
     */
    public function getVisitedAt()
    {
        return $this->visitedAt;
    }
}

