<?php

namespace AppBundle\Model;

class CloudFront
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     *
     */
    private $ARN;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $lastModifiedTime;

    /**
     * @var string
     */
    private $domainName;

    /**
     * @var \DateTime
     */
    private $aliases;

    /**
     * @var string
     */
    private $origins;

    /**
     * @var array
     */
    private $defaultCacheBehavior;

    /**
     * @var array
     */
    private $cacheBehaviors;

    /**
     * @var array
     */
    private $customErrorResponses;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $priceClass;

    /**
     * @var string
     */
    private $enabled;


    /**
     * @var object
     */
    private $viewerCertificate;

    /**
     * @var array
     */
    private $restrictions;

    /**
     * @var string
     */
    private $webACLId;

    /**
     * @var string
     */
    private $httpVersion;

    /**
     * @var string
     */
    private $isIPV6Enabled;


    public function __construct(array $data)
    {
        $cloudFront = json_decode(json_encode($data, NULL));
        $this->id = $cloudFront->Id;
        $this->ARN = $cloudFront->ARN;
        $this->status = $cloudFront->Status;
        $this->lastModifiedTime = \DateTime::createFromFormat('Y-m-d\TG:i:sT', $cloudFront->LastModifiedTime);
        $this->domainName = $cloudFront->DomainName;
        if (isset($cloudFront->Aliases) && isset($cloudFront->Aliases->Items)) {
            foreach ($cloudFront->Aliases->Items as $item) {
                $this->aliases [] = $item;
            }
        }
        if (isset($cloudFront->Origins) && isset($cloudFront->Origins->Items)) {
            foreach ($cloudFront->Origins->Items as $item) {
                $this->origins [] = $item;
            }
        }


        if (isset($cloudFront->CacheBehaviors) && isset($cloudFront->CacheBehaviors->Items)) {
            foreach ($cloudFront->CacheBehaviors->Items as $item) {
                $this->CacheBehaviors [] = $item;
            }
        }
        if (isset($cloudFront->CustomErrorResponses) && isset($cloudFront->CustomErrorResponses->Items)) {
            foreach ($cloudFront->CustomErrorResponses->Items as $item) {
                $this->customErrorResponses [] = $item;
            }
        }
        $this->defaultCacheBehavior [] = isset($cloudFront->DefaultCacheBehavior) ?? [];
        $this->comment = $cloudFront->Comment;
        $this->priceClass = $cloudFront->PriceClass;
        $this->enabled = (bool)$cloudFront->Enabled;
        $this->viewerCertificate =  json_decode(json_encode($cloudFront->ViewerCertificate, NULL));
        $this->restrictions = $cloudFront->Restrictions;
        $this->isIPV6Enabled = (bool)$cloudFront->IsIPV6Enabled;
        $this->webACLId = $cloudFront->WebACLId;
        $this->httpVersion = $cloudFront->HttpVersion;

    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getARN(): string
    {
        return $this->ARN;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getLastModifiedTime(): \DateTime
    {
        return $this->lastModifiedTime;

    }

    /**
     * @return string
     */
    public function getDomainName(): string
    {
        return $this->domainName;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return array
     */
    public function getOrigins(): array
    {
        return $this->origins;
    }

    /**
     * @return array
     */
    public function getDefaultCacheBehavior(): array
    {
        return $this->defaultCacheBehavior;

    }

    /**
     * @return array
     */
    public function getCustomErrorResponses(): array
    {
        return $this->customErrorResponses();
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getPriceClass(): string
    {
        return $this->priceClass;
    }

    /**
     * @return string
     */
    public function getEnabled(): string
    {
        return $this->enabled;
    }

    /**
     * @return object
     */
    public function getViewerCertificate()
    {
        return $this->viewerCertificate;
    }

    /**
     * @return array
     */
    public function getRestrictions(): array
    {
        return $this->restrictions;
    }

    /**
     * @return string
     */
    public function getWebACLId(): string
    {
        return $this->webACLId;

    }

    /**
     * @return string
     */
    public function getHttpVersion(): string
    {
        return $this->httpVersion;
    }

    /**
     * @return bool
     */
    public function getIsIPV6Enabled(): bool
    {
        return $this->isIPV6Enabled;
    }

    /**
     * @return string
     */
    public function getCacheBehaviors(): string
    {
        return $this->cacheBehaviors;
    }


}