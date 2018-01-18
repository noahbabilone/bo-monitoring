<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation As Gedmo;

/**
 * Instance
 *
 * @ORM\Table(name="instance")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InstanceRepository")
 */
class Instance
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var string
     *
     * @ORM\Column(name="update_date", type="datetime",  nullable=true)
     */
    private $updateDate;


    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer",  nullable=true, options={"default":0})
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\URL", mappedBy="instance")
     */
    private $urls;
    
    
    /**
     * @var bool
     *
     * @ORM\Column(name="no_host", type="boolean",  options={"default":false})
     */
    private $noHost;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->urls = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Instance
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Set slug
     *
     * @param string $slug
     *
     * @return Instance
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
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
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Instance
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
     * Set updateDate
     *
     * @param \DateTime $updateDate
     *
     * @return Instance
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Instance
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add url
     *
     * @param \AppBundle\Entity\URL $url
     *
     * @return Instance
     */
    public function addUrl(\AppBundle\Entity\URL $url)
    {
        $this->urls[] = $url;

        return $this;
    }

    /**
     * Remove url
     *
     * @param \AppBundle\Entity\URL $url
     */
    public function removeUrl(\AppBundle\Entity\URL $url)
    {
        $this->urls->removeElement($url);
    }

    /**
     * Get urls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Set noHost
     *
     * @param boolean $noHost
     *
     * @return Instance
     */
    public function setNoHost($noHost)
    {
        $this->noHost = $noHost;

        return $this;
    }

    /**
     * Get noHost
     *
     * @return boolean
     */
    public function getNoHost()
    {
        return $this->noHost;
    }
}
