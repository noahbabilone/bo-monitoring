<?php

namespace AppBundle\Model;

class Backend
{
    /**
     * @var string
     */
    private $instanceId;

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
     * @var string
     */
    private $autoScalingGroupName;

    /**
     * @var array
     */
    private $tags;

    public function __construct(array $data, $asg = '')
    {
        $instance = json_decode(json_encode($data['Instances'][0], NULL));

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
//                case 'Statut_Backend':
//                    $this->statusBackend = $tag->Value;
//                    break;
                default:
            }
        }

        $this->instanceType = $instance->InstanceType;
        $this->launchTime = \DateTime::createFromFormat('Y-m-d\TG:i:sT', $instance->LaunchTime);
        $this->availabilityZone = $instance->Placement->AvailabilityZone;
        $this->monitoring = $instance->Monitoring->State;
        $this->vpc = (isset($instance->VpcId)) ? $instance->VpcId : '';
        $this->subnet = (isset($instance->SubnetId)) ? $instance->SubnetId : '';
        $this->privateIp = (isset($instance->PrivateIpAddress)) ? $instance->PrivateIpAddress : '';

        $this->publicIp = (isset($instance->PublicIpAddress)) ? $instance->PublicIpAddress : '';
        $this->securityGroup = (isset($instance->SecurityGroups[0])) ? $instance->SecurityGroups[0]->GroupId : '';
        $this->securityGroupName = (isset($instance->SecurityGroups[0])) ? $instance->SecurityGroups[0]->GroupName : '';
        $this->autoScalingGroupName = $asg;
    }

    /**
     * @return string
     */
    public function getAutoScalingGroupName()
    {
        return $this->autoScalingGroupName;
    }

    /**
     * @param string $autoScalingGroupName
     * @return Backend
     */
    public function setAutoScalingGroupName($autoScalingGroupName)
    {
        $this->autoScalingGroupName = $autoScalingGroupName;
        return $this;
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
     * @return Backend
     */
    public function setTags(array $tags): Backend
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
     * @return Backend
     */
    public function setSecurityGroupName(string $securityGroupName): Backend
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
     * @return Backend
     */
    public function setInstanceName(string $instanceName): Backend
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
     * @return Backend
     */
    public function setInstanceEnv(string $instanceEnv): Backend
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
     * @return Backend
     */
    public function setLaunchTime(\DateTime $launchTime): Backend
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
     * @return Backend
     */
    public function setInstanceId(string $instanceId): Backend
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusBackend(): string
    {
        return $this->statusBackend;
    }

    /**
     * @param string $statusBackend
     * @return Backend
     */
    public function setStatusBackend(string $statusBackend): Backend
    {
        $this->statusBackend = $statusBackend;
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
     * @return Backend
     */
    public function setStatusInstance(string $statusInstance): Backend
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
     * @return Backend
     */
    public function setPrivateIp(string $privateIp): Backend
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
     * @return Backend
     */
    public function setInstanceType(string $instanceType): Backend
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
     * @return Backend
     */
    public function setAvailabilityZone(string $availabilityZone): Backend
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
     * @return Backend
     */
    public function setMonitoring(string $monitoring): Backend
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
     * @return Backend
     */
    public function setVpc(string $vpc): Backend
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
     * @return Backend
     */
    public function setSubnet(string $subnet): Backend
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
     * @return Backend
     */
    public function setPublicIp(string $publicIp): Backend
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
     * @return Backend
     */
    public function setSecurityGroup(string $securityGroup): Backend
    {
        $this->securityGroup = $securityGroup;
        return $this;
    }

}