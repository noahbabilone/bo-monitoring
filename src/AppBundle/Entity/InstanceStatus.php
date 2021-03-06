<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstanceStatus
 *
 * @ORM\Table(name="instance_status", indexes={
 *     @ORM\Index(name="instance_idx_instanceid_status", columns={"instance_id", "status"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InstanceStatusRepository")
 */
class InstanceStatus
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
     * @ORM\Column(name="instance_id", type="string", length=255, unique=true)
     */
    private $instanceId;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", options={"default":0})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="error_date", type="datetime")
     */
    private $errorDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_last_notif", type="datetime")
     */
    private $dateLastNotif;

    /**
     * @var int
     *
     * @ORM\Column(name="error_code", type="integer")
     */
    private $errorCode;

    /**
     * @var string
     *
     * @ORM\Column(name="error_content", type="text", nullable=true)
     */
    private $errorContent;

    /**
     * @var string
     *
     * @ORM\Column(name="what", type="string", length=512, nullable=true)
     */
    private $what;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return InstanceStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
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
     * Set instanceId
     *
     * @param string $instanceId
     *
     * @return InstanceStatus
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * Get instanceId
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set errorDate
     *
     * @param \DateTime $errorDate
     *
     * @return InstanceStatus
     */
    public function setErrorDate($errorDate)
    {
        $this->errorDate = $errorDate;

        return $this;
    }

    /**
     * Get errorDate
     *
     * @return \DateTime
     */
    public function getErrorDate()
    {
        return $this->errorDate;
    }

    /**
     * Set errorCode
     *
     * @param integer $errorCode
     *
     * @return InstanceStatus
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get errorCode
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set errorContent
     *
     * @param string $errorContent
     *
     * @return InstanceStatus
     */
    public function setErrorContent($errorContent)
    {
        $this->errorContent = $errorContent;

        return $this;
    }

    /**
     * Get errorContent
     *
     * @return string
     */
    public function getErrorContent()
    {
        return $this->errorContent;
    }

    /**
     * Set what
     *
     * @param string $what
     *
     * @return InstanceStatus
     */
    public function setWhat($what)
    {
        $this->what = $what;

        return $this;
    }

    /**
     * Get what
     *
     * @return string
     */
    public function getWhat()
    {
        return $this->what;
    }

    /**
     * Set dateLastNotif
     *
     * @param \DateTime $dateLastNotif
     *
     * @return InstanceStatus
     */
    public function setDateLastNotif($dateLastNotif)
    {
        $this->dateLastNotif = $dateLastNotif;

        return $this;
    }

    /**
     * Get dateLastNotif
     *
     * @return \DateTime
     */
    public function getDateLastNotif()
    {
        return $this->dateLastNotif;
    }
}
