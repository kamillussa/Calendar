<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use	Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="work_hours", type="string")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern = "/([01]?[0-9]|2[0-3]):[0-5][0-9]-([01]?[0-9]|2[0-3]):[0-5][0-9]/",
     *     message = "Set proper work hours (00:00-00:00)"
     * )
     */
    protected $workHours;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="user")
     */
    protected $events;

    /**
     * @return mixed
     */
    public function getWorkHours()
    {
        return $this->workHours;
    }

    /**
     * @param mixed $workHours
     */
    public function setWorkHours($workHours)
    {
        $this->workHours = $workHours;
    }

    public function __construct()
    {
        parent::__construct();
        $this->events = new ArrayCollection();
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
     * Add event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return User
     */
    public function addEvent(\AppBundle\Entity\Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \AppBundle\Entity\Event $event
     */
    public function removeEvent(\AppBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }
}
