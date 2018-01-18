<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation As Gedmo;


/**
 * LockFile
 *
 * @ORM\Table(name="lock_file")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LockFileRepository")
 */
class LockFile
{
    const RUNNING = 0; #EN COURS
    const BLOCKED = 1; #BLOQUER
    const DELETED = 2; #SUPPRIMER
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
     * @ORM\Column(name="id_file_s3", type="string", length=255, nullable=true)
     */
    private $idFileS3;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer",nullable=true)
     */
    private $size;

    /**
     * @var string
     * @ORM\Column(name="key_s3", type="string", length=255, nullable=true)
     */
    private $keyS3;


    /**
     * @var string
     * @ORM\Column(name="e_tag", type="string", length=255, nullable=true)
     */
    private $eTag;

    /**
     * @var string
     * @ORM\Column(name="storage_class", type="string", length=255, nullable=true)
     */
    private $storageClass;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_modified", type="datetime", length=255, nullable=true)
     */
    private $lastModified;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_last_notif", type="datetime", nullable=true)
     */
    private $dateLastNotif;

    /**
     * @var bool
     * @ORM\Column(name="prod", type="boolean",options={ "default":true })
     */
    private $prod;

    /**
     * @var string
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;


    /**
     * LockFile constructor.
     */
    public function __construct()
    {
//        $this->expire = true;
//        $this->prod = true;
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return LockFile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get idFileS3
     *
     * @return string
     */
    public function getIdFileS3()
    {
        return $this->idFileS3;
    }

    /**
     * Set idFileS3
     *
     * @param string $idFileS3
     *
     * @return LockFile
     */
    public function setIdFileS3($idFileS3)
    {
        $this->idFileS3 = $idFileS3;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return LockFile
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get keyS3
     *
     * @return string
     */
    public function getKeyS3()
    {
        return $this->keyS3;
    }

    /**
     * Set keyS3
     *
     * @param string $keyS3
     *
     * @return LockFile
     */
    public function setKeyS3($keyS3)
    {
        $this->keyS3 = $keyS3;

        return $this;
    }

    /**
     * Get eTag
     *
     * @return string
     */
    public function getETag()
    {
        return $this->eTag;
    }

    /**
     * Set eTag
     *
     * @param string eTag
     *
     * @return LockFile
     */
    public function setETag($eTag)
    {
        $this->eTag = $eTag;

        return $this;
    }

    /**
     * Get storageClass
     *
     * @return string
     */
    public function getStorageClass()
    {
        return $this->storageClass;
    }

    /**
     * Set storageClass
     *
     * @param string $storageClass
     *
     * @return LockFile
     */
    public function setStorageClass($storageClass)
    {
        $this->storageClass = $storageClass;

        return $this;
    }

    /**
     * Get lastModified
     *
     * @return string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set lastModified
     *
     * @param string $lastModified
     *
     * @return LockFile
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return LockFile
     */
    public function setCreated($created)
    {
        $this->created = $created;

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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return LockFile
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    public function init(array $data)
    {
        if (is_array($data)) {
            if (isset($data['Key'])) {
                $this->setKeyS3($data['Key']);
            }
            if (isset($data['ETag'])) {
                $this->setETag($data['ETag']);
            }
            if (isset($data['Size'])) {
                $this->setSize($data['Size']);
            }
            if (isset($data['StorageClass'])) {
                $this->setStorageClass($data['StorageClass']);
            }
            if (isset($data['Owner']['ID'])) {
                $this->setIdFileS3($data['Owner']['ID']);
            }
            if (isset($data['LastModified'])) {
                $this->setLastModified(\DateTime::createFromFormat('Y-m-d\TG:i:sT', $data['LastModified']));
            }
            $keyS3 = isset($data['Key']) ? explode("/", $data['Key']) : null;
            $filename = is_array($keyS3) ? end($keyS3) : null;

            if (null !== $filename) {
                $this->setName($filename);
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isProd(): bool
    {
        return $this->prod;
    }

    /**
     * @param bool $prod
     * @return LockFile
     */
    public function setProd(bool $prod): LockFile
    {
        $this->prod = $prod;
        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return LockFile
     */
    public function setState(string $state): LockFile
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @param \DateTime $dateLastNotif
     * @return LockFile
     */
    public function setDateLastNotif(\DateTime $dateLastNotif): LockFile
    {
        $this->dateLastNotif = $dateLastNotif;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastNotif(): \DateTime
    {
        return $this->dateLastNotif;
    }


}
