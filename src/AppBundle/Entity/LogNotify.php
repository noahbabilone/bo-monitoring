<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogNotify
 *
 * @ORM\Table(name="log_notify", indexes={
 *     @ORM\Index(name="log_idx_instanceid", columns={"instance_id"}),
 *     @ORM\Index(name="log_idx_date", columns={"error_date"}),
 *     @ORM\Index(name="log_idx_instanceid_date", columns={"instance_id", "error_date"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LogNotifyRepository")
 */
class LogNotify
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
     * @ORM\Column(name="instance_id", type="string", length=255)
     */
    private $instanceId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="error_date", type="datetime")
     */
    private $errorDate;

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
     * @return LogNotify
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
     * @return LogNotify
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
     * @return LogNotify
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
     * @return LogNotify
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
     * @return LogNotify
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
}

