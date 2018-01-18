<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstanceStatus
 *
 * @ORM\Table(name="instance_notify", indexes={
 *     @ORM\Index(name="instance_idx_instanceid_notify", columns={"name", "state"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InstanceStatusRepository")
 */
class InstanceNotify
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="boolean", options={"default":true})
     */
    private $state;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime",nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_last_Notify", type="datetime",nullable=true)
     */
    private $dateLastNotify;

    public function __construct()
    {
        $this->createDate = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return InstanceNotify
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return InstanceNotify
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set state
     *
     * @param \boolean $state
     *
     * @return InstanceNotify
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return InstanceNotify
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set dateLastNotify
     *
     * @param \DateTime $dateLastNotify
     *
     * @return InstanceNotify
     */
    public function setDateLastNotify($dateLastNotify)
    {
        $this->dateLastNotify = $dateLastNotify;

        return $this;
    }

    /**
     * Get dateLastNotify
     *
     * @return \DateTime
     */
    public function getDateLastNotify()
    {
        return $this->dateLastNotify;
    }
}
