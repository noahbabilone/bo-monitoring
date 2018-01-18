<?php

namespace AppBundle\Service;


use AppBundle\Entity\Customer;
use AppBundle\Entity\Invoice;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Ec2\Ec2Client;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InvoiceService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ServicesNotifyer $servicesNotifyer */
    private $servicesNotifyer;
    private $em;
    private $urlTester;
    private $customers = ['hotvideo', 'pinktv', 'brazzers',];

    /**
     * InvoiceService constructor.
     * @param EntityManager $em
     * @param ContainerInterface $container
     * @param UrlTester $urlTester
     * @param ServicesNotifyer $servicesNotifyer
     */
    public function __construct(EntityManager $em, ContainerInterface $container, UrlTester $urlTester, ServicesNotifyer $servicesNotifyer)
    {
        $this->em = $em;
        $this->container = $container;
        $this->servicesNotifyer = $servicesNotifyer;
        $this->urlTester = $urlTester;
    }


    /**
     * @param OutputInterface $output
     * @return bool
     */
    public function testAllInvoices(OutputInterface $output, $prod = true)
    {
        $output->writeln(sprintf('Test Invoice'));
        foreach ($this->customers as $customer) {
            if ($customer != 'local') {
                $this->testInvoice($output, $customer, $prod);
            }
        }
        $output->writeln(sprintf('End TEST'));

        return true;

    }


    /**
     * @param OutputInterface $output
     * @param $customer
     * @param bool $prod
     * @return bool
     */
    public function testInvoice(OutputInterface $output, $customer, $prod = true)
    {
//        $backend = strtoupper($customer);
        $date = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $baseId = null;
        $customerFree = null;
        //HOT VIDEO
        if (strtoupper($customer) === Customer::HOTVIDEO) {
            $baseId = Customer::BASEIDHOTVIDEO;
            /** @var Customer $customerFree */
            $customerFree = $this->em->getRepository('AppBundle:Customer')->findOneByName(Customer::HOTVIDEO);

        } else if (strtoupper($customer) === Customer::PINKTV) {
            $baseId = Customer::BASEIDPINKTV;
            /** @var Customer $customerFree */
            $customerFree = $this->em->getRepository('AppBundle:Customer')->findOneByName(Customer::PINKTV);
        } else if (strtoupper($customer) === Customer::BRAZZERS) {
            $baseId = Customer::BASEIDBRAZZERS;
            /** @var Customer $customerFree */
            $customerFree = $this->em->getRepository('AppBundle:Customer')->findOneByName(Customer::BRAZZERS);
        }

        if ($baseId !== null && $customerFree instanceof Customer) {
//            $output->writeln(sprintf('Run %s', $backend));
            $fileName = $baseId . '_' . (new \DateTime($date))->format('Ym') . '.txt';
            //$fileName = "32_201705.txt";
            $invoice = $this->em->getRepository('AppBundle:Invoice')->findOneByFile($fileName);
            if ($invoice === null || !$invoice instanceof Invoice) {
                $invoice = new Invoice();
                $invoice->setFile($fileName);
                $invoice->setS3(false);
                $invoice->setFtp(false);
                $invoice->setCustomer($customerFree);
                $this->em->persist($invoice);
                $this->em->flush();
            }

            $baseUrl = $customerFree->getUrl();
            //$baseUrl = $prod === true ? $customerFree->getUrl() : $customerFree->getUrlDev();
            //AMAZON S3 AND BO
            $response = $this->urlTester->curlAPI($baseUrl . "/api/management/s3?file=" . $fileName);
            if (isset($response["code"]) && isset($response["body"])) {
                $body = json_decode($response["body"], true);
                if ($response["code"] == 200 && isset($body['result'])) {
                    $invoice->setS3(boolval($body['result']));
                }
                $invoice->setS3ApiError(json_encode(["code" => $response["code"], "body" => $response["body"]], true));
            } else {
                $invoice->setS3(false);
            }

            //FTP FREE
            $response = $this->urlTester->curlAPI($baseUrl . "/api/management/ftp?file=" . $fileName);
            if (isset($response["code"]) && isset($response["body"])) {
                $body = json_decode($response["body"], true);
                if ($response["code"] == 200 && isset($body['result'])) {
                    $invoice->setFtp(boolval($body['result']));
                    $message = "CLIENT : " . strtoupper($customer) . "\n";
                    $message .= "Fichier: " . $fileName . "\n\n";
                    if ($invoice->getFtp() === false) {
                        if ($invoice->getS3() == true) {
                            $message .= "Une erreur s'est produite lors du transfert ou de la copie du fichier de facture vers le FTP Free .\n \n";
                            $message .= "Veuillez récupérer le fichier directement sur le S3 et puis il faudra le déposer manuellement sur le FTP FREE.\n";
                            $message .= "Chemin du fichier sur le bucket S3 :\n Amazon S3 / " . $customer . "-prod / exports / auto_facture_abonnement / " . $fileName . " \n";
//                            $message .= "Veuillez déposer le fichier sur le FTP FREE manuellement " . strtoupper($customer);
                        } else {
                            $message .= "Une erreur s'est produite lors de la création du fichier de facture " . strtoupper($customer) . "\n";
                            $message .= "La facture de " . strtoupper($customer) . " n'est ni sur le S3 et ni sur le FTP Free.";
                        }
                        $subject = " [ERREUR][" . strtoupper($customer) . "] Fichier de Facture non déposé sur le ftp free";

                        $userName = "[ERREUR] Facture FTP Free : " . strtoupper($customer);
                        $this->slackNotif($userName, 'warning', $message);
                    } else {
                        $message .= "Facture sur le FTP Free:  OK \n";
                        $message .= "Facture sur le S3: OK";
                        $subject = "[REUSSI][" . strtoupper($customer) . "] Fichier de Facture OK";

                        $userName = "Confirmation Facture FTP Free : " . strtoupper($customer);
                        $icon = 'information';
                        $body = 'Le fichier de facture a été bien déposé sur le ftp free.';
                        $this->slackNotif($userName, $icon, $body);
                    }
                    $message .= "\n\n Management Freebox.";
                    $this->servicesNotifyer->snsNotif($message . " \n", $subject, $prod);
                }
                $invoice->setFtpApiError(json_encode(["code" => $response["code"], "body" => $response["body"]], true));
            } else {
                $output->writeln('[API ERROR ]');
                $invoice->setFtp(false);
            }

            $s3 = $invoice->getS3() === true ? 'true' : 'false';
            $ftp = $invoice->getFtp() === true ? 'true' : 'false';

            $dateLog = new \DateTime();
            $dateLog->setTimezone(new \DateTimeZone("Europe/Paris"));
            $output->writeln('[' . $dateLog->format('d/m/Y H:i:s') . '] Test ' . $customer . ' ' . $invoice->getFile() . ' S3 => ' . $s3 . ' FTP => ' . $ftp);
            $this->em->flush();
        } else {
            $output->writeln('[API ERROR 159]');
        }
        return true;
    }

    /**
     * @param $body
     * @param $userName
     * @param $icon
     * @return bool
     */
    public function slackNotif($userName, $icon, $body)
    {
        $url = $this->container->getParameter("hooks_slack");
        if (!empty($url) && !empty($icon) && !empty($userName) && !empty($body)) {
            $body .= '\n<https://management.freebox.42cloud.io/invoices|Cliquez ici> pour plus d\'informations';
            $headers = ["Cache-Control: no-cache", 'Content-Type: application/x-www-form-urlencoded'];
            $ch = curl_init();
            //, "icon_emoji": ":' . $icon . ':"
            $data = 'payload={ "username": "' . $userName . '" , "text": "' . $body . '"}';

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //'x_http_method_override: PUT');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $output = curl_exec($ch);
            curl_close($ch);
            return $output == 'ok' ? true : false;
        } else {
            return false;
        }
    }

    /**
     * @param OutputInterface $output
     * @param bool $prod
     * @return bool
     */
    public function testAllFTP(OutputInterface $output, $prod = true)
    {
        $output->writeln(sprintf('Test FTP'));
        foreach ($this->customers as $customer) {
            if ($customer != 'local') {
                $this->testFTP($output, $customer, $prod);
            }
        }
        $output->writeln(sprintf('End TEST'));
        return true;

    }

    /**
     * @param OutputInterface $output
     * @param $customer
     * @param bool $prod
     * @return bool
     */
    public function testFTP(OutputInterface $output, $customer, $prod = true)
    {
        $output->writeln(sprintf('Run %s', strtoupper($customer)));
        /** @var Customer $customerFree */
        $customerFree = $this->em->getRepository('AppBundle:Customer')->findOneByName(strtoupper($customer));
        if ($customerFree instanceof Customer) {
//            $baseUrl = 'http://localhost/42C/hotvideo/web/app_dev.php';
            $baseUrl = $customerFree->getUrl();
            $url = $baseUrl . "/api/management/ftp/test";
            $response = $this->urlTester->curlAPI($url);
            if (isset($response["code"]) && isset($response["body"])) {
                $date = new \DateTime();
                $date->setTimezone(new \DateTimeZone("Europe/Paris"));
                $body = json_decode($response["body"], true);
                if (isset($body["result"]) && isset($body["directory"])) {
                    if ($body['result'] !== true || $body["directory"] != $customerFree->getPathInvoice()) {
                        $output->writeln('[' . $date->format('d/m/Y H:i:s') . '] Error FTP ( Message => ' . $body['message'] . ' - Invoice Directory => ' . $body['directory'] . ' )');
                        $apiResponse = "Message: " . $body['message'] . "\n Répertoire de dépôt: " . $body["directory"];
                        $this->messageTestFTP(strtoupper($customer), $apiResponse, false, $prod);
                    } else {
                        $apiResponse = "Message: " . $body['message'] . "\n Répertoire de dépôt: " . $body["directory"];
                        $this->messageTestFTP(strtoupper($customer), $apiResponse, true, $prod);
                        $output->writeln('[' . $date->format('d/m/Y H:i:s') . '] FTP ( Message => ' . $body['message'] . ' - Invoice Directory => ' . $body['directory'] . ' )');
                    }
                } else {
                    $output->writeln('Code: ' . $response["code"] . ' - [' . $date->format('d/m/Y H:i:s') . '] Url : ' . $url);
                    $this->messageTestFTP(strtoupper($customer), $response["body"], false, $prod);
                }
            }
        } else {
            $output->writeln("Customer not found");
        }
        return true;
    }

    public function messageTestFTP($customer, $response, $result, $prod = false)
    {
        if ($result == false) {
            $message = "Une erreur s'est produite lors de la connexion au serveur FTP.\n \n ";
            $message .= "Réponse API : \n " . $response;
            $message .= "\n \n Veuillez vérifier le fichier parameters.yml du BO " . $customer;
            $subject = "[ERREUR][" . strtoupper($customer) . "] Test de Connexion FTP";
        } else {
            $message = "Test de connexion au serveur FTP de free réussi.\n \n";
            $message .= "Réponse API : \n " . $response;
            $subject = "[REUSSI][" . strtoupper($customer) . "] Test de Connexion FTP";
        }
        $message .= "\n\n Management Freebox.";

        $this->servicesNotifyer->snsNotif($message . " \n", $subject, $prod);
        return true;
    }

//0 1 1 * * sh /home/ec2-user/scripts_hotvideo/hotvideo-factu.sh
//10 1 1 * * sh /home/ec2-user/scripts_brazzers/brazzers-factu.sh
// 15 1 1 * * php /var/www/html/aws_status/bin/console app:service:invoice --customer all -p dev > /home/ec2-user/log/cron_invoice.log 2>&1 >/dev/null 2>&1
}