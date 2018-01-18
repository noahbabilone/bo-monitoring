<?php

namespace AppBundle\Service;


use AppBundle\Entity\LockFile;
use AppBundle\Model\Backend;
use AppBundle\Model\CloudFront;
use AppBundle\Model\Varnish;
use Aws\AutoScaling\AutoScalingClient;
use Aws\CloudFront\CloudFrontClient;
use Aws\Ec2\Ec2Client;
use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

class InstancesRetriver implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    private $asgNames = null;
    /** @var EntityManager $em */
    private $em;
//    private $container;

    /**
     * InstancesRetriver constructor.
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
        $this->asgNames = ['prod' => $this->container->getParameter('asg_prod'), 'preprod' => $this->container->getParameter('asg_preprod'), 'api_prod' => $this->container->getParameter('asg_api_prod'), 'api_preprod' => $this->container->getParameter('asg_api_preprod'), 'db_prod' => $this->container->getParameter('rds_prod'), 'db_preprod' => $this->container->getParameter('rds_preprod'),];
    }

    /**
     * @return array
     */
    public function getAllVarnishInstances()
    {
        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');

        $result = $ec2->describeInstances(['DryRun' => false, 'Filters' => [['Name' => 'tag-key', 'Values' => ['Statut_Varnish'],],],])->toArray();

        $instances = [];
        foreach ($result['Reservations'] as $reservation) {
            $instances[] = new Varnish($reservation);
        }

        unset($result);

        return $instances;
    }

    /**
     * @return Varnish | array
     */
    public function getActiveVarnishInstance()
    {

        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');

        $result = $ec2->describeInstances(['DryRun' => false, 'Filters' => [['Name' => 'tag-key', 'Values' => ['Statut_Varnish'],], ['Name' => 'tag-value', 'Values' => ['Active'],],],])->toArray();

        $instances = [];
// dump($result) or die;
        foreach ($result['Reservations'] as $reservation) {
            $instances[] = new Varnish($reservation);
        }
        unset($result);;

        return (is_array($instances) && count($instances) > 0) ? $instances[0] : $instances;
    }

    /**
     * @param string $env
     * @return array
     */
    public function getBackendInstancesByEnv($env = 'prod')
    {
        /** @var AutoScalingClient $asgClient */
        $asgClient = $this->container->get('aws.autoscaling');

        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');


        $result = $asgClient->describeAutoScalingGroups(['AutoScalingGroupNames' => [$this->asgNames[$env]],])->toArray();

//        dump($result); die;
        $instanceIds = [];

        foreach ($result['AutoScalingGroups'][0]['Instances'] as $item) if ($item['LifecycleState'] == 'InService') $instanceIds[] = $item['InstanceId'];

        if (0 == count($instanceIds)) {
            return null;
        }
        $result = $ec2->describeInstances(['DryRun' => false, 'InstanceIds' => $instanceIds,])->toArray();
        /*
                dump($result);
                die;*/
        $instances = [];
//        dump($result['Reservations']) or die;

        foreach ($result['Reservations'] as $reservation) {
            $back = new Backend($reservation);
            if ($back->getStatusInstance() == 'running') $instances[] = $back; else
                unset($back);
        }

        unset($result);
        return $instances;
    }

    /**
     * @param string $env
     * @return array
     */
    public function getBackendInstancesRds($env = "db_prod")
    {

        /** @var S3Client $s3 */
        $rds = $this->container->get('aws.rds');
        $instances = null;

        //Executes the DescribeInstances operation.

        $result = $rds->describeDBInstances(array('DBInstanceIdentifier' => $this->asgNames[$env]));
        if (count($result) > 0) {
            $instances = $result->get('DBInstances');
            unset($result);
        }

        return $instances;
    }


    /**
     * @param $name
     * @return Varnish|array
     */
    public function getInstanceByName($name)
    {

        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');

        $result = $ec2->describeInstances(['DryRun' => false, 'Filters' => [['Name' => 'tag-key', 'Values' => ['Name'],], ['Name' => 'tag-value', 'Values' => [$name],],

        ],])->toArray();

        $instances = [];

        foreach ($result['Reservations'] as $reservation) {
            $instances[] = new Varnish($reservation);
        }
        unset($result);

        return (is_array($instances) && count($instances) > 0) ? $instances[0] : $instances;
    }


    /**
     * @param $instanceID
     * @return Varnish|array
     * @internal param $name
     */
    public function getInstanceByID($instanceID)
    {

        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');

        $result = $ec2->describeInstances(['DryRun' => false, 'InstanceIds' => array($instanceID)])->toArray();
//dump($result) or die;
        $instances = [];

        foreach ($result['Reservations'] as $reservation) {
            $instances[] = new Varnish($reservation);
        }
        unset($result);

        return (is_array($instances) && count($instances) > 0) ? $instances[0] : $instances;
    }


    public function getCloudFronts()
    {

        /** @var CloudFrontClient $client */
        $client = $this->container->get('aws.cloudfront');
        $result = $client->listDistributions(['Marker' => '', 'MaxItems' => 100,])->toArray();

        $cloudFronts = [];

        $items = $result['DistributionList']['Items'] ?? null;

        if (null !== $items) {
            foreach ($items as $item) {
                $cloudFronts[] = new CloudFront($item);
            }
        } else {
            throw new \OutOfBoundsException(sprintf('The DistributionList or Items is not within the property path result'));
        }
//        dump($cloudFronts) or die;
        unset($result);
        return $cloudFronts;
    }

    public function getViacomPairing()
    {
        /** @var Ec2Client $ec2 */
        $ec2 = $this->container->get('aws.ec2');

        $result = $ec2->describeInstances(['DryRun' => false, 'Filters' => [['Name' => 'tag-key', 'Values' => ['viacom'],], ['Name' => 'tag-value', 'Values' => ['pairing'],],],])->toArray();

        $instances = [];
        foreach ($result['Reservations'] as $reservation) {
            if (isset($reservation['Instances']) && count($reservation['Instances']) > 1) {
                for ($i = 0; $i < count($reservation['Instances']); $i++) $instances[] = new Varnish($reservation, $i);
            } else
                $instances[] = new Varnish($reservation);
        }
        unset($result);;

        return $instances;

    }


    /**
     * @param bool $prod
     * @return bool
     */
    public function s3File($prod = true)
    {
//      $env = true === $prod ? "prod/lock_files" : "preprod/lock_files";
        $env = true === $prod ? "preprod/lock_files" : "preprod/lock_files";
        /*     $result = $client->listObjects([
                'Bucket' => 'webapp--log', // REQUIRED
                'Delimiter' => 'string',
                'EncodingType' => 'url',
                'Marker' => 'string',
                'MaxKeys' => integer,
                'Prefix' => ' ',
                'RequestPayer' => 'requester',
            ]);
        */
        /** @var S3Client $client */
        $client = $this->container->get('aws.s3');
        $result = $client->listObjects(['Bucket' => 'appfreeboxmut-exe', "Prefix" => "$env", 'MaxKeys' => 10]);
        $yesterday = new \DateTime('yesterday');
        if (count($result) > 0 && isset($result['Contents'])) {
            foreach ($result['Contents'] as $key => $content) {
                $key = isset($content['Key']) ? explode("/", $content['Key']) : null;
                $filename = is_array($key) ? end($key) : null;
                if (strpos($filename, ".lock") !== false) {
                    $lockFile = $this->em->getRepository(LockFile::class)->findOneByName($filename);
                    if ($lockFile instanceof LockFile) {
                        if (isset($content['LastModified'])) {
                            $lockFile->setLastModified(\DateTime::createFromFormat('Y-m-d\TG:i:sT', $content['LastModified']));

                            if ($lockFile->getLastModified() < $yesterday) {
                                $lockFile->setState(LockFile::BLOCKED);
                            } else if ($lockFile->getLastModified() > $yesterday) {
                                $lockFile->setState(LockFile::RUNNING);
                            } else {
                                $lockFile->setState(LockFile::DELETED);
                            }
                        }
                    } else {
                        $lockFile = new LockFile();
                        $lockFile->init($content);
                        $lockFile->setProd(boolval($prod));
                        $yesterday = new \DateTime('yesterday');
                        if ($lockFile->getLastModified() < $yesterday) {
                            $lockFile->setState(LockFile::BLOCKED);
                        } else if ($lockFile->getLastModified() > $yesterday) {
                            $lockFile->setState(LockFile::RUNNING);
                        } else {
                            $lockFile->setState(LockFile::DELETED);
                        }
                        $this->em->persist($lockFile);
                    }

                    if ($key % 10 == 0) $this->em->flush();
                }
            }
            $this->em->flush();
        }
        unset($result);
        return true;
    }

}