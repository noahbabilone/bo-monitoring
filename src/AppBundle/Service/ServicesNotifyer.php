<?php

namespace AppBundle\Service;

use AppBundle\Entity\InstanceStatus;
use AppBundle\Entity\LogNotify;
use Aws\Sns\SnsClient;
use Doctrine\ORM\EntityManager;
use Ovh\Sms\SmsApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServicesNotifyer //implements ContainerAwareInterface
{
//    use ContainerAwareTrait;

    const MaxTime = 25;
    /** @var EntityManager $em */
    private $em;
    /** @var SnsClient $snsClient */
    private $snsClient;
    private $sns = null;
    private $mailer = null;
    /** @var ContainerInterface container */
    private $container;

    /**
     * ServicesNotifyer constructor.
     * @param EntityManager $em
     * @param SnsClient $snsClient
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, SnsClient $snsClient, ContainerInterface $container, Mailer $mailer)
    {
//        $this->container = $container;
        $this->em = $em;
        $this->snsClient = $snsClient;
        $this->mailer = $mailer;
        $this->container = $container;
        $this->sns = ['sms' => $container->getParameter('sns_sms'), 'prod' => $container->getParameter('sns_mail'), 'dev' => $container->getParameter('sns_dev'),];
    }

    /**
     * @param array $item
     * @param array | null $result
     * @param bool $dryrun
     * @param bool $eProdNotify
     * @param bool $otherBackend
     * @return bool
     */
    public function notify($item, $result, $dryrun = false, $eProdNotify = true, $otherBackend = false)
    {
        $check = $this->dbCheck($item);
        $notify = $dryrun;
        
//        $check = true; //Test en Dev: Desactivé le 
        if ($check) {
            $this->logNotify($item, $result);
            $this->updateOnNotify($item, $result, $dryrun);
            
            if (false !== $dryrun) {
                $notify = false;
                if ($eProdNotify == false) {
                    $this->devNotif($item, $result);
                } else {
                    $this->emailNotif($item, $result, $eProdNotify);
                    $this->slackNotif($item, $result, $eProdNotify);
                    if ($otherBackend == false) {
                        $this->smsNotif($item, $result, $eProdNotify);
                    }
                }
            } else {
                // Require Option: --force
                // php bin/console app:service:tester --run --force --all
            }
        } else {
            $obj = $this->em->getRepository('AppBundle:InstanceStatus')->findOneBy(['instanceId' => $item['details']['instanceid']]);
            if (null !== $obj) {
                if (2 == $obj->getStatus()) {
                    $this->logNotify($item, $result);
                    $this->updateOnNotify($item, $result, false);
                }
            }
        }
        return $notify;
    }

    /**
     * @param array $item
     * @return boolean
     */
    private function dbCheck($item)
    {
        /** @var InstanceStatus|null $obj */
        $obj = $this->em->getRepository('AppBundle:InstanceStatus')->findOneBy(['instanceId' => $item['details']['instanceid']]);
//        dump($item);
        if (null === $obj) return true;
        $minutes_to_add = 15;
        $temp = new \DateTime($obj->getDateLastNotif()->format('Y-m-d H:i'));
        $temp->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $now = new \DateTime("now");
        $now->setTimezone(new \DateTimeZone("Europe/Paris"));
//        dump($temp,$now,$temp <= $now);
        return ($temp <= $now) ? true : false;
    }

    /**
     * @param array $item
     * @param $result
     */
    private function logNotify($item, $result)
    {
        $date = new \DateTime('now');
        $date->setTimezone(new \DateTimeZone("Europe/Paris"));
        $log = new LogNotify();
        $log->setInstanceId($item['details']['instanceid']);
        $log->setErrorDate($date);
        $log->setErrorCode($result['raw_code']);
        $log->setErrorContent($result['message']);
        $log->setWhat($item['url']['msg']);
        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param array $item
     * @param $result
     * @param bool $sendNotify
     */
    private function updateOnNotify($item, $result, $sendNotify = false)
    {

        $date = new \DateTime('now');
        $date->setTimezone(new \DateTimeZone("Europe/Paris"));
        /** @var InstanceStatus|null $log */
        $log = $this->em->getRepository('AppBundle:InstanceStatus')->findOneBy(['instanceId' => $item['details']['instanceid']]);

        if (null === $log) {
            $log = new InstanceStatus();
            $log->setDateLastNotif($date);
        }

        $log->setInstanceId($item['details']['instanceid']);
        $log->setErrorDate($date);
        if ($sendNotify) {
            $log->setDateLastNotif($date);
        }
        $log->setErrorDate($date);
        $log->setErrorCode($result['raw_code']);
        $log->setErrorContent($result['message']);
        $log->setWhat($item['url']['msg']);
        $log->setStatus(0);

        $this->em->persist($log);
        $this->em->flush();


    }

    /**
     * @param $item
     * @param $result
     * @return bool
     */
    public function slackNotif($item, $result)
    {
        $url = $this->container->getParameter("hooks_slack");
//        $body = 'MANAGEMENT FREEBOX \n';
        $body = 'Instance : ' . $item['details']['type'] . ' [' . $item['details']['instanceid'] . '] \n';
        $body .= 'Erreur remontée sur : ' . $item['url']['msg'] . ' \n ';
        $body .= 'ERROR CODE ' . $result['code'] . '\n';
        $body .= '<https://management.freebox.42cloud.io|Cliquez ici> pour plus d\'informations';

        $headers = ["Cache-Control: no-cache", 'Content-Type: application/x-www-form-urlencoded'];
        $ch = curl_init();
        $data = 'payload={ "username": "ALERTE PROJET FREEBOX" , "icon_emoji": ":warning:", "text": "' . $body . '"}';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //'x_http_method_override: PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output == 'ok' ? true : false;
    }

    /**
     * @param $item
     * @param $result
     */
    private function devNotif($item, $result)
    {
        $message = "MAINTENACES  \n";
        $message .= "Le service tester a rencontré une erreur dont voici les détails: \n";
        $message .= 'Type : Maintenance ' . $item['details']['type'] . " \n";
        $message .= 'Nom de la machine & instanceID : ' . $item['details']['name'] . ' -- ' . $item['details']['instanceid'] . " \n";
        $message .= 'Ip privée : ' . $item['details']['privateip'] . " \n";
        $message .= 'Qu\'est-ce qui a été testé : ' . $item['url']['msg'] . " \n";

        if (isset($item['host_to_test'])) {
            $message .= 'Host envoyé : ' . $item['host_to_test'] . " \n";
        }

        $message .= 'Url testée : ' . $item['to_test'] . " \n\n";
//        $message .= 'Code d\'erreur retourné : '.$result['code']."\n\n";
        $message .= "Contenu retourné : \n\n";
        $message .= $result['message'];


        $dataToPublish = ['Message' => $message, 'Subject' => 'Maintenance sur la machine : ' . $item['details']['type'] . ' ' . $item['details']['name'], 'TopicArn' => $this->sns['dev'],];

        try {
            $this->snsClient->publish($dataToPublish);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $item
     * @param $result
     * @param bool $prod
     */
    private function emailNotif($item, $result, $prod = true)
    {
        //$message = "Le service tester a rencontré une erreur dont voici les détails: \n";
        $message = 'Type : ' . $item['details']['type'] . " \n";
//        $message .= 'Nom de la machine & instanceID : ' . $item['details']['name'] . ' -- ' . $item['details']['instanceid'] . " \n";
        $message .= 'Instance ID : ' . $item['details']['instanceid'] . " \n";
        $message .= 'Ip Privée : ' . $item['details']['privateip'] . " \n";
        $message .= 'Erreur remontée sur : ' . $item['url']['msg'] . " \n";

        if (isset($item['host_to_test'])) {
            $message .= 'Host envoyé : ' . $item['host_to_test'] . " \n";
        }

        $message .= 'Url: ' . $item['to_test'] . " \n\n";
//        $message .= 'Code d\'erreur retourné : '.$result['code']."\n\n";
        $message .= "Contenu retourné : \n\n";
        $message .= $result['message'];


        $dataToPublish = ['Message' => $message, 'Subject' => 'Erreur ' . $item['details']['type'] . ' - ' . $item['details']['name'], 'TopicArn' => ($prod == true) ? $this->sns['prod'] : $this->sns['dev'],];

        try {
            $this->snsClient->publish($dataToPublish);
        } catch (\Exception $e) {
        }
    }

    function smsNotif($item, $result, $prod = true)
    {

        try {
            $endpoint = 'ovh-eu';

            $Sms = new SmsApi($this->container->getParameter('ovh_application_key'), $this->container->getParameter('ovh_application_secret'), $endpoint, $this->container->getParameter('ovh_customer_key'));

            $accounts = $Sms->getAccounts();
            $Sms->setAccount($accounts[0]);
            $senders = $Sms->getSenders();
            $Message = $Sms->createMessage();
            $Message->setSender($senders[0]);
            $contacts = $this->container->getParameter("sms_contacts");
            if (is_array($contacts)) {
                if ($prod === true) {
                    foreach ($contacts as $contact) {
                        $Message->addReceiver($contact);
                    }
                } else {
                    $Message->addReceiver("+33671893698"); //DEV Yannick
                    //$Message->addReceiver("+212664404095"); //Test Nawfel
                }

                $Message->setIsMarketing(false);
                $body = 'Instance : ' . $item['details']['type'] . ' [' . $item['details']['instanceid'] . '], '; //%0d
                $body .= 'Erreur remontée sur : ' . $item['url']['msg'] . ', ';
                $body .= 'Code: ' . $result['code'];

                $body = strlen($body) > 140 ? substr($body, 0, 137) . '...' : $body;
                $Message->send($body);
                $plannedMessages = $Sms->getPlannedMessages();
                unset($Sms, $Message, $plannedMessages);
            }

//            $subject = 'Account=sms-vd407-1;Login=SMSOVH; Password=Mtvc2016; From=MEDIATVCOM;To=+33652989877,+33689884687,+33786631204,+33632732745';
//            $this->mailer->sendOVH($subject, $body);
        } catch (\Exception $e) {
//            dump($e->getMessage());
//            dump($e->getTraceAsString());
        }
    }

    /**
     * @param $message
     * @param $subject
     * @param bool $prod
     * @internal param $item
     * @internal param $result
     */
    public function snsNotif($message, $subject, $prod = false)
    {
        $dataToPublish = ['Message' => $message, 'Subject' => ($prod === true) ? $subject : '[ DEV ] ' . $subject, 'TopicArn' => ($prod === true) ? $this->sns['prod'] : $this->sns['dev'],];

        try {
            $this->snsClient->publish($dataToPublish);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param array $item
     */
    public function dbUpdate($item)
    {
        /** @var InstanceStatus|null $log */
        $log = $this->em->getRepository('AppBundle:InstanceStatus')->createQueryBuilder('i')->where('i.instanceId = :inst')->andWhere('i.status != 2')->distinct()->setParameter('inst', $item['details']['instanceid'])->getQuery()->setMaxResults(1)->getOneOrNullResult();

        if (null !== $log) {
            $date = new \DateTime('now');
            $date->setTimezone(new \DateTimeZone("Europe/Paris"));

            $log->setErrorDate($date);
            $log->setErrorCode(200);
            $log->setErrorContent('OK');
            $log->setWhat('Clean');
            $log->setStatus(2);

            $this->em->persist($log);
            $this->em->flush();
        }
    }
}
