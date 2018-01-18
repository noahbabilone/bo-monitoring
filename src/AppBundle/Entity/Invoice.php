<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation As Gedmo;


/**
 * Invoice
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvoiceRepository")
 */
class Invoice
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
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @var bool
     *
     * @ORM\Column(name="ftp", type="boolean", nullable=true)
     */
    private $ftp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ftpDate", type="datetime", nullable=true)
     */
    private $ftpDate;

    /**
     * @var string
     *
     * @ORM\Column(name="ftp_api_error", type="string", length=512, nullable=true)
     */
    private $ftpApiError;

    /**
     * @var bool
     *
     * @ORM\Column(name="s3", type="boolean", nullable=true)
     */
    private $s3;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="s3Date", type="datetime", nullable=true)
     */
    private $s3Date;

    /**
     * @var string
     *
     * @ORM\Column(name="s3_api_error", type="string", length=512, nullable=true)
     */
    private $s3ApiError;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Customer", inversedBy="invoivces")
     */
    private $customer;


    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

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
     * Set file
     *
     * @param string $file
     *
     * @return Invoice
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set ftp
     *
     * @param boolean $ftp
     *
     * @return Invoice
     */
    public function setFtp($ftp)
    {
        $this->ftp = $ftp;

        return $this;
    }

    /**
     * Get ftp
     *
     * @return boolean
     */
    public function getFtp()
    {
        return $this->ftp;
    }

    /**
     * Set ftpDate
     *
     * @param \DateTime $ftpDate
     *
     * @return Invoice
     */
    public function setFtpDate($ftpDate)
    {
        $this->ftpDate = $ftpDate;

        return $this;
    }

    /**
     * Get ftpDate
     *
     * @return \DateTime
     */
    public function getFtpDate()
    {
        return $this->ftpDate;
    }

    /**
     * Set ftpApiError
     *
     * @param string $ftpApiError
     *
     * @return Invoice
     */
    public function setFtpApiError($ftpApiError)
    {
        $this->ftpApiError = $ftpApiError;

        return $this;
    }

    /**
     * Get ftpApiError
     *
     * @return string
     */
    public function getFtpApiError()
    {
        return $this->ftpApiError;
    }

    /**
     * Set s3
     *
     * @param boolean $s3
     *
     * @return Invoice
     */
    public function setS3($s3)
    {
        $this->s3 = $s3;

        return $this;
    }

    /**
     * Get s3
     *
     * @return boolean
     */
    public function getS3()
    {
        return $this->s3;
    }

    /**
     * Set s3Date
     *
     * @param \DateTime $s3Date
     *
     * @return Invoice
     */
    public function setS3Date($s3Date)
    {
        $this->s3Date = $s3Date;

        return $this;
    }

    /**
     * Get s3Date
     *
     * @return \DateTime
     */
    public function getS3Date()
    {
        return $this->s3Date;
    }

    /**
     * Set s3ApiError
     *
     * @param string $s3ApiError
     *
     * @return Invoice
     */
    public function setS3ApiError($s3ApiError)
    {
        $this->s3ApiError = $s3ApiError;

        return $this;
    }

    /**
     * Get s3ApiError
     *
     * @return string
     */
    public function getS3ApiError()
    {
        return $this->s3ApiError;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Invoice
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
     * @return Invoice
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
     * Set customer
     *
     * @param \AppBundle\Entity\Customer $customer
     *
     * @return Invoice
     */
    public function setCustomer(\AppBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \AppBundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
