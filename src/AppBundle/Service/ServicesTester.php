<?php

namespace AppBundle\Service;


use AppBundle\Entity\InstanceNotify;
use AppBundle\Entity\LockFile;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Ec2\Ec2Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServicesTester implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $editors = ["HOVIDEO", "PINKTV", "BRAZZERS"];
    /** @var UrlTester $urlTester */
    private $urlTester;
    /** @var ServicesNotifyer $servicesNotifyer */
    private $servicesNotifyer;
    private $em;

    public function __construct(EntityManager $em, ContainerInterface $container, UrlTester $urlTester, ServicesNotifyer $servicesNotifyer)
    {
        $this->em = $em;
        $this->container = $container;

        $this->urlTester = $urlTester;
        $this->servicesNotifyer = $servicesNotifyer;
    }

    /**
     * @param OutputInterface $output
     * @param bool $dryrun
     * @return bool
     */
    public function testVarnish(OutputInterface $output, $dryrun = false)
    {
        $items = $this->container->get('app.url.maker')->getVarnishUrls();
        $iNotify = $this->em->getRepository('AppBundle:InstanceNotify')->findOneBySlug('varnish');
        $eProdNotify = (null !== $iNotify && $iNotify instanceof InstanceNotify) ? $iNotify->getState() : true;

        $sendNotify = array();
        $notif = $dryrun;
        if (count($items) === 0) return false;

        foreach ($items as $item) {
            if (isset($item['details']['privateip'])) {
                $ipRun = $item['details']['privateip'];
                if (!in_array($ipRun, $sendNotify)) {
                    $sendNotify[] = $ipRun;
                    $notif = $dryrun;
                }
            }
            $url = 'http://' . $item['to_test'];
            $result = $this->urlTester->testUrl($url);
            $codeCurl = ($result !== null) ? $result['code'] : "200 OK";
            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone("Europe/Paris"));

            $output->writeln('Code: ' . $codeCurl . ' - [' . $date->format('d/m/Y H:i:s') . '] Url : ' . $item['to_test']);
            $notif = $this->notify($item, $result, $notif, $eProdNotify);
        }
        return true;
    }

    /**
     * @param $item
     * @param array|null $result
     * @param bool $dryrun
     * @param bool $eProdNotify
     * @param bool $otherBackend
     * @return bool
     */
    private function notify($item, $result = null, $dryrun = false, $eProdNotify = true, $otherBackend = false)
    {
        $notify = $dryrun;
        if (null !== $result) {

            $notify = $this->servicesNotifyer->notify($item, $result, $dryrun, $eProdNotify, $otherBackend);
        } else {
            $this->servicesNotifyer->dbUpdate($item);
        }
        return $notify;
    }

    /**
     * @param OutputInterface $output
     * @param bool $dryrun
     * @param bool $eProdNotify
     * @return bool
     */
    public function testExternalLinks(OutputInterface $output, $dryrun = false, $eProdNotify = true)
    {
        $items = $this->container->get('app.url.maker')->getExternalLinks();
        if (count($items) === 0) return false;

        foreach ($items as $item) {
            $result = $this->urlTester->testUrl($item['to_test']);
            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone("Europe/Paris"));
            $codeCurl = ($result !== null) ? $result['code'] : "200 OK";
            $output->writeln('Code: ' . $codeCurl . ' - [' . $date->format('d/m/Y H:i:s') . '] Url : ' . $item['to_test']);

            $this->notify($item, $result, $dryrun, $eProdNotify, true);
        }
        return true;

    }

    /**
     * Test les backend prod et preprod
     * @param OutputInterface $output
     * @param bool $dryrun
     * @return bool
     */
    public function testBackends(OutputInterface $output, $dryrun = false)
    {
        // $dryrun = false; // A Supprimer une fois que le problème de HTTPS est résolu.   
        $items = $this->container->get('app.url.maker')->getBackendsUrls();
        /** @var InstanceNotify $iNotify */
        $iNotify = $this->em->getRepository('AppBundle:InstanceNotify')->findOneBySlug('backoffice_prod');
        $eProdNotify = (null !== $iNotify && $iNotify instanceof InstanceNotify) ? $iNotify->getState() : true;

        $output->writeln('Backoffice PROD');
        if (count($items) === 0) return false;

        if (isset($items['prod']) && !empty($items['prod'])) {
            $sendNotify = array();
            $notif = $dryrun;
            foreach ($items['prod'] as $item) {

                if (isset($item['details']['privateip'])) {
                    if (!in_array($item['details']['privateip'], $sendNotify)) {
                        $sendNotify[] = $item['details']['privateip'];
                        $notif = $dryrun;
                    }
                }

                $date = new \DateTime();
                $date->setTimezone(new \DateTimeZone("Europe/Paris"));

                $url = 'https://' . $item['to_test'];
                $result = $this->urlTester->testUrl($url, $item['host_to_test']);
                $codeCurl = ($result !== null) ? $result['code'] : "200 OK";
                $output->writeln('Code: ' . $codeCurl . ' - [' . $date->format('d/m/Y H:i:s') . '] Host : ' . $item['host_to_test'] . ' - Url : ' . $item['to_test']);
                $notif = $this->notify($item, $result, $notif, $eProdNotify);

            }
        } else {
            $message = "Pas d'instance  Running. Veuillez vérifier l'Autoscaling Scaling Group: " . $this->container->getParameter('prod');
            $subject = "ASG Backend Prod n'a pas trouvé l'instnce de backoffice Prod";
            $this->servicesNotifyer->snsNotif($message . " \n", $subject);
            $output->writeln($message);
        }

//        die;

        $iNotify = $this->em->getRepository('AppBundle:InstanceNotify')->findOneBySlug('backend_api_prod');
        $eProdNotify = (null !== $iNotify && $iNotify instanceof InstanceNotify) ? $iNotify->getState() : true;

        $output->writeln('Backend API PROD');

        if (isset($items['api_prod']) && !empty($items['api_prod'])) {
            $sendNotify = array();
            $notify = $dryrun;

            foreach ($items['api_prod'] as $item) {
                if (isset($item['details']['privateip'])) {
                    if (!in_array($item['details']['privateip'], $sendNotify)) {
                        $sendNotify[] = $item['details']['privateip'];
                        $notify = $dryrun;
                    }
                }
//                dump($sendNotify, $notify);
                $url = 'http://' . $item['to_test'];
                $result = $this->urlTester->testUrl($url, $item['host_to_test']);
                $date = new \DateTime();
                $date->setTimezone(new \DateTimeZone("Europe/Paris"));

                $codeCurl = ($result !== null) ? $result['code'] : "200 OK";
                $output->writeln('Code: ' . $codeCurl . ' - [' . $date->format('d/m/Y H:i:s') . '] Host : ' . $item['host_to_test'] . ' - Url : ' . $item['to_test']);

                $notify = $this->notify($item, $result, $notify, $eProdNotify);
            }
        } else {
            $message = "Pas d'instance  Running. Veuillez vérifier l'Autoscaling Scaling Group: " . $this->container->getParameter('prod');
            $subject = "ASG Backend API Prod n'a pas trouvé l'instnce de backoffice Prod";
            $this->servicesNotifyer->snsNotif($message . " \n", $subject);
            $output->writeln($message);
        }

        $iNotify = $this->em->getRepository('AppBundle:InstanceNotify')->findOneBySlug('backend_api_preprod');
        $eProdNotify = (null !== $iNotify && $iNotify instanceof InstanceNotify) ? $iNotify->getState() : true;

        $output->writeln('Backend API PREPROD');
        if (isset($items['api_preprod']) && !empty($items['api_preprod'])) {
            $sendNotify = array();
            $notify = $dryrun;
            foreach ($items['api_preprod'] as $item) {
                if (isset($item['details']['privateip'])) {
                    if (!in_array($item['details']['privateip'], $sendNotify)) {
                        $sendNotify[] = $item['details']['privateip'];
                        $notify = $dryrun;
                    }
                }
//                dump($sendNotify, $notify);
                $url = 'http://' . $item['to_test'];
                $result = $this->urlTester->testUrl($url, $item['host_to_test']);
                $date = new \DateTime();
                $date->setTimezone(new \DateTimeZone("Europe/Paris"));

                $codeCurl = ($result !== null) ? $result['code'] : "200 OK";
                $output->writeln('Code: ' . $codeCurl . ' - [' . $date->format('d/m/Y H:i:s') . '] Host : ' . $item['host_to_test'] . ' - Url : ' . $item['to_test']);
                $notify = $this->notify($item, $result, $notify, $eProdNotify);
            }
        } else {
            $message = "Pas d'instance  Running. Veuillez vérifier l'Autoscaling Scaling Group: " . $this->container->getParameter('prod');
            $subject = "ASG Backend API Preprod n'a pas trouvé l'instnce de backoffice Prod";
            $this->servicesNotifyer->snsNotif($message . " \n", $subject);
            $output->writeln($message);
        }
    }

    public function testLockFile(OutputInterface $output, $dryrun = false)
    {
        //Update Data
        $this->$this->container->get('app.instances.retriver')->s3File($prod = true);
        $cronTabs = $this->em->getRepository(LockFile::class)->createQueryBuilder('lock')->groupBy('lock.name')->orderBy('lock.lastModified', 'DESC')->setMaxResults(20)->getQuery()->getResult();
        $time = 1;
        $now = new \DateTime("now");
        $now->setTimezone(new \DateTimeZone("Europe/Paris"));
        dump($cronTabs);
        die;
        /** @var LockFile $cron */
        foreach ($cronTabs as $cron) {
            if ($cron->setState() == LockFile::BLOCKED) {
                if (!$cron->getDateLastNotif() instanceof \DateTime) {
                    $cron->setDateLastNotif($now);
                    $this->cronTabMessage($cron->getName());
                } else {
                    $temp = new \DateTime($cron->getDateLastNotif()->format('Y-m-d H:i'));
                    $temp->add(new \DateInterval('PT' . $time . 'H'));
                    if ($temp <= $now) {
                        $this->cronTabMessage($cron->getName());
                    }
                }

            }
        }

    }


    public function cronTabMessage($filename)
    {
        $customers = explode("-", $filename);
        $name = in_array($customers) ? current($customers) : $filename;
        if (in_array(strtoupper($name), $this->editors)) {
            $subject = "[CRONTAB][" . strtoupper($name) . "] Lockfile Bloqué";
        } else {
            $customers = explode(".", $filename);
            $subject = "[CRONTAB][" . strtoupper(current($customers)) . "] Lockfile Bloqué";
        }

        $message = "Message";

        dump($subject, $message);
        die;

        //$this->servicesNotifyer->snsNotif($message . " \n", $subject);


    }
}