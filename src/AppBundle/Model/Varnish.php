<?php

namespace AppBundle\Model;

class Varnish
{
    /**
     * @var string
     */
    private $instanceId;

    /**
     * @var string
     */
    private $statusVarnish;

    /**
     * @var string
     */
    private $statusInstance;

    /**
     * @var string
     */
    private $privateIp;

    /**
     * @var string
     */
    private $instanceType;

    /**
     * @var \DateTime
     */
    private $launchTime;

    /**
     * @var string
     */
    private $availabilityZone;

    /**
     * @var string
     */
    private $monitoring;

    /**
     * @var string
     */
    private $vpc;

    /**
     * @var string
     */
    private $subnet;

    /**
     * @var string
     */
    private $publicIp;

    /**
     * @var string
     */
    private $securityGroup;

    /**
     * @var string
     */
    private $securityGroupName;


    /**
     * @var string
     */
    private $instanceName;

    /**
     * @var string
     */
    private $instanceEnv;


    /**
     * @var array
     */
    private $tags;

    /**
     * @var
     */
    private $imageId;
    private $architecture;
    private $rootDeviceName;
    private $networkInterfaces;
    private $rootDeviceType;
    private $securityGroups;
    private $sourceDestCheck;
    private $virtualizationType;
    private $keyName;
    private $iamInstanceProfile;
    private $ebsOptimized;
    private $publicDnsName;
    private $privateDnsName;
    private $blockDeviceMappings;

    public function __construct(array $data, $index = 0)
    {
        if (isset($data['Instances']) && count($data['Instances']) > $index) {
            $instance = json_decode(json_encode($data['Instances'][$index], NULL));
            $this->instanceId = $instance->InstanceId;
            $this->statusInstance = $instance->State->Name;

            $this->tags = $instance->Tags;

            foreach ($instance->Tags as $tag) {
                switch ($tag->Key) {
                    case 'Env':
                        $this->instanceEnv = $tag->Value;
                        break;
                    case 'Name':
                        $this->instanceName = $tag->Value;
                        break;
                    case 'Statut_Varnish':
                        $this->statusVarnish = $tag->Value;
                        break;
                    default:
                }
            }

            $this->instanceType = $instance->InstanceType;
            $this->launchTime = \DateTime::createFromFormat('Y-m-d\TG:i:sT', $instance->LaunchTime);
            $this->availabilityZone = $instance->Placement->AvailabilityZone;
            $this->monitoring = $instance->Monitoring->State;
            $this->vpc = (isset($instance->VpcId)) ? $instance->VpcId : '';
            $this->subnet = (isset($instance->SubnetId)) ? $instance->SubnetId : '';
            $this->publicIp = (isset($instance->PublicIpAddress)) ? $instance->PublicIpAddress : '';
            $this->privateIp = (isset($instance->PrivateIpAddress)) ? $instance->PrivateIpAddress : '';
            $this->securityGroup = (isset($instance->SecurityGroups[0])) ? $instance->SecurityGroups[0]->GroupId : '';
            $this->securityGroupName = (isset($instance->SecurityGroups[0])) ? $instance->SecurityGroups[0]->GroupName : '';

            $this->imageId = $instance->ImageId ?? null;
            $this->architecture = $instance->Architecture ?? null;
            $this->networkInterfaces = $instance->NetworkInterfaces ?? [];
            $this->rootDeviceName = $instance->RootDeviceName ?? null;
            $this->rootDeviceType = $instance->RootDeviceType ?? null;
            $this->securityGroups = $instance->SecurityGroups ?? null;
            $this->sourceDestCheck = $instance->SourceDestCheck ?? [];
            $this->iamInstanceProfile = $instance->IamInstanceProfile ?? null;
            $this->tags = $instance->Tags ?? [];
            $this->virtualizationType = $instance->VirtualizationType ?? null;
            $this->keyName = $instance->KeyName ?? null;
            $this->ebsOptimized = $instance->EbsOptimized ?? null;
            $this->publicDnsName = $instance->PublicDnsName ?? null;
            $this->privateDnsName = $instance->PrivateDnsName ?? null;
            $this->blockDeviceMappings = $instance->BlockDeviceMappings ?? null;
        }
    }

    /**
     * @return array
     */
    public function getBlockDeviceMappings(): array
    {
        return $this->blockDeviceMappings;
    }

    /**
     * @return string
     */
    public function getPublicDnsName(): string
    {
        return $this->publicDnsName;
    }

    /**
     * @return string
     */
    public function getPrivateDnsName(): string
    {
        return $this->privateDnsName;
    }

    /**
     * @return bool
     */
    public function getEbsOptimized(): bool
    {
        return $this->ebsOptimized;
    }

    public function getIamInstanceProfile()
    {
        return $this->iamInstanceProfile;
    }

    /**
     * @return array
     */
    public function getVirtualizationType(): array
    {
        return $this->virtualizationType;
    }

    /**
     * @return array
     */
    public function getSourceDestCheck(): array
    {
        return $this->virtualizationType;
    }

    /**
     * @return array
     */
    public function getSecurityGroups(): array
    {
        return $this->securityGroups;
    }

    /**
     * @return string
     */
    public function getRootDeviceType(): string
    {
        return $this->rootDeviceType;
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @return array
     */
    public function getNetworkInterfaces(): array
    {
        return $this->networkInterfaces;
    }

    /**
     * @return string
     */
    public function getRootDeviceName(): string
    {
        return $this->rootDeviceName;
    }

    /**
     * @return string
     */
    public function getArchitecture(): string
    {
        return $this->architecture;
    }

    /**
     * @return string
     */
    public function getImageId(): string
    {
        return $this->imageId;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return Varnish
     */
    public function setTags(array $tags): Varnish
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecurityGroupName(): string
    {
        return $this->securityGroupName;
    }

    /**
     * @param string $securityGroupName
     * @return Varnish
     */
    public function setSecurityGroupName(string $securityGroupName): Varnish
    {
        $this->securityGroupName = $securityGroupName;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    /**
     * @param string $instanceName
     * @return Varnish
     */
    public function setInstanceName(string $instanceName): Varnish
    {
        $this->instanceName = $instanceName;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceEnv(): string
    {
        return $this->instanceEnv;
    }

    /**
     * @param string $instanceEnv
     * @return Varnish
     */
    public function setInstanceEnv(string $instanceEnv): Varnish
    {
        $this->instanceEnv = $instanceEnv;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLaunchTime(): \DateTime
    {
        return $this->launchTime;
    }

    /**
     * @param \DateTime $launchTime
     * @return Varnish
     */
    public function setLaunchTime(\DateTime $launchTime): Varnish
    {
        $this->launchTime = $launchTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    /**
     * @param string $instanceId
     * @return Varnish
     */
    public function setInstanceId(string $instanceId): Varnish
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusVarnish(): string
    {
        return $this->statusVarnish;
    }

    /**
     * @param string $statusVarnish
     * @return Varnish
     */
    public function setStatusVarnish(string $statusVarnish): Varnish
    {
        $this->statusVarnish = $statusVarnish;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusInstance(): string
    {
        return $this->statusInstance;
    }

    /**
     * @param string $statusInstance
     * @return Varnish
     */
    public function setStatusInstance(string $statusInstance): Varnish
    {
        $this->statusInstance = $statusInstance;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateIp(): string
    {
        return $this->privateIp;
    }

    /**
     * @param string $privateIp
     * @return Varnish
     */
    public function setPrivateIp(string $privateIp): Varnish
    {
        $this->privateIp = $privateIp;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceType(): string
    {
        return $this->instanceType;
    }

    /**
     * @param string $instanceType
     * @return Varnish
     */
    public function setInstanceType(string $instanceType): Varnish
    {
        $this->instanceType = $instanceType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvailabilityZone(): string
    {
        return $this->availabilityZone;
    }

    /**
     * @param string $availabilityZone
     * @return Varnish
     */
    public function setAvailabilityZone(string $availabilityZone): Varnish
    {
        $this->availabilityZone = $availabilityZone;
        return $this;
    }

    /**
     * @return string
     */
    public function getMonitoring(): string
    {
        return $this->monitoring;
    }

    /**
     * @param string $monitoring
     * @return Varnish
     */
    public function setMonitoring(string $monitoring): Varnish
    {
        $this->monitoring = $monitoring;
        return $this;
    }

    /**
     * @return string
     */
    public function getVpc(): string
    {
        return $this->vpc;
    }

    /**
     * @param string $vpc
     * @return Varnish
     */
    public function setVpc(string $vpc): Varnish
    {
        $this->vpc = $vpc;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubnet(): string
    {
        return $this->subnet;
    }

    /**
     * @param string $subnet
     * @return Varnish
     */
    public function setSubnet(string $subnet): Varnish
    {
        $this->subnet = $subnet;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicIp(): string
    {
        return $this->publicIp;
    }

    /**
     * @param string $publicIp
     * @return Varnish
     */
    public function setPublicIp(string $publicIp): Varnish
    {
        $this->publicIp = $publicIp;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecurityGroup(): string
    {
        return $this->securityGroup;
    }

    /**
     * @param string $securityGroup
     * @return Varnish
     */
    public function setSecurityGroup(string $securityGroup): Varnish
    {
        $this->securityGroup = $securityGroup;
        return $this;
    }
}