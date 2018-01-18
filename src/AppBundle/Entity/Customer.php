<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 */
class Customer
{

    const HOTVIDEO = 'HOTVIDEO';
    const PINKTV = 'PINKTV';
    const BRAZZERS = 'BRAZZERS';
    const BASEIDHOTVIDEO= "32";
    const BASEIDPINKTV = "24";
    const BASEIDBRAZZERS= "1097";
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
     * @ORM\Column(name="url", type="string", length=256, nullable=true)
     */
    private $url; 
    
    /**
     * @var string
     *
     * @ORM\Column(name="url_dev", type="string", length=256, nullable=true)
     */
    private $urlDev;  
    
    /**
     * @var string
     *
     * @ORM\Column(name="path_invoice", type="string", length=256, nullable=true)
     */
    private $pathInvoice; 

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;


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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Invoice", mappedBy="customer",cascade={"persist", "remove"})
     */
    private $invoices;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Customer
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Customer
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Customer
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add invoice
     *
     * @param \AppBundle\Entity\Invoice $invoice
     *
     * @return Customer
     */
    public function addInvoice(\AppBundle\Entity\Invoice $invoice)
    {
        $this->invoices[] = $invoice;

        return $this;
    }

    /**
     * Remove invoice
     *
     * @param \AppBundle\Entity\Invoice $invoice
     */
    public function removeInvoice(\AppBundle\Entity\Invoice $invoice)
    {
        $this->invoices->removeElement($invoice);
    }

    /**
     * Get invoices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Customer
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set urlDev
     *
     * @param string $urlDev
     *
     * @return Customer
     */
    public function setUrlDev($urlDev)
    {
        $this->urlDev = $urlDev;

        return $this;
    }

    /**
     * Get urlDev
     *
     * @return string
     */
    public function getUrlDev()
    {
        return $this->urlDev;
    }

    /**
     * Set pathInvoice
     *
     * @param string $pathInvoice
     *
     * @return Customer
     */
    public function setPathInvoice($pathInvoice)
    {
        $this->pathInvoice = $pathInvoice;

        return $this;
    }

    /**
     * Get pathInvoice
     *
     * @return string
     */
    public function getPathInvoice()
    {
        return $this->pathInvoice;
    }
}
