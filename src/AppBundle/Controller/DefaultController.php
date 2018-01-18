<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Customer;
use AppBundle\Entity\InstanceNotify;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\LockFile;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Ec2\Ec2Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Route("/nodata={nodata}", name="homepage2")
     * @param Request $request
     * @param int $nodata
     * @return
     */
    public function indexAction(Request $request, $nodata = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $data = ['nodata' => $nodata, 'instances' => ['instancesVarnish' => $this->get('app.instances.retriver')->getAllVarnishInstances(), 'backofficeProd' => $this->get('app.instances.retriver')->getBackendInstancesByEnv('prod'), 'backofficePreprod' => $this->get('app.instances.retriver')->getBackendInstancesByEnv('preprod'), 'apiProd' => $this->get('app.instances.retriver')->getBackendInstancesByEnv('api_prod'), 'apiPreprod' => $this->get('app.instances.retriver')->getBackendInstancesByEnv('api_preprod'), 'cloudFronts' => $this->get('app.instances.retriver')->getCloudFronts(), 'viacomPairing' => $this->get('app.instances.retriver')->getViacomPairing()], 'databases' => ['prod' => $this->get('app.instances.retriver')->getBackendInstancesRds('db_prod'), 'preprod' => $this->get('app.instances.retriver')->getBackendInstancesRds('db_preprod')]];

//        $test = $this->get('app.service.tester')->testAPIs(false);
        $instanceNotify = $em->getRepository('AppBundle:InstanceNotify')->findAll();
        if ($instanceNotify) {
            /** @var InstanceNotify $notify */
            foreach ($instanceNotify as $notify) {
                $data['notifies'][$notify->getSlug()] = $notify;
            }
        }

        return $this->render('default/index.html.twig', $data);
    }

    /**
     * Lists all instance entities.
     *
     * @Route("/links", name="link_list")
     */
    public function linkAction()
    {
        $em = $this->getDoctrine()->getManager();

        $instances = $em->getRepository('AppBundle:Instance')->findBy(array(), array('position' => 'ASC'));
        return $this->render('instance/url.html.twig', array('instances' => $instances,));
    }

    /**
     * Lists all instance entities.
     *
     * @Route("/invoices", name="app_invoice")
     * @param Request $request
     * @return
     */
    public function invoicesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $customers = $em->getRepository('AppBundle:Customer')->createQueryBuilder('c')->select('c')->addSelect('i')->leftJoin('c.invoices', 'i')->orderBy('c.id', 'ASC')->addOrderBy('i.id', 'DESC')->getQuery()->getResult();

//        $results = $this->get('knp_paginator')->paginate(
//            $customers,
//            $request->query->getInt('page', 1),
//            3
//        );
        return $this->render('default/invoice.html.twig', array('customers' => $customers,));
    }


    /**
     * @Route("/documentation.html", name="doc_page")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function docAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/doc.html.twig', array());
    }

    /**
     * @Route("/procedure-acces-distant.html", name="procedure_remote_access")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function procedureDistantAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/procedure.html.twig', array());
    }


    /**
     * @Route("/notify_ajax", name="notify_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    function notifyAction(Request $request)
    {
        $type = "danger";
        $success = 'Error';
        $icon = 'fa fa-exclamation-triangle';
        if ($request->isXmlHttpRequest()) {
            $instanceId = intval($request->get("id"));
            $state = $request->get("state") == "true" ? true : false;
            $em = $this->getDoctrine()->getManager();
            /** @var InstanceNotify $instanceNotify */
            $instanceNotify = $em->getRepository('AppBundle:InstanceNotify')->find($instanceId);
            if (null === $instanceNotify) {
                $message = "Error : Instance not found";
            } else {

                if ($state != $instanceNotify->getState()) {
                    $instanceNotify->setState($state);
                    $date = new \DateTime();
                    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
                    $instanceNotify->setDateLastNotify($date);

                    $em->flush();
                    $success = 'Success';
                    if ($instanceNotify->getState() == true) {
                        $message = ' Activée';
                        $icon = ' fa fa-check';
                        $type = "success";
                    } else {
                        $message = ' Désactivée';
                        $type = "warning";
                    }
                    $message = "Notification " . $instanceNotify->getName() . $message;
                } else {
                    $message = "Error: State: " . $state;
                    $type = "Error: State: " . $state;

                }

            }
        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('success' => $success, "message" => $message, "type" => $type, "icon" => $icon,)), 200, ['Content-Type' => 'application/json']);

    }


    /**
     * @Route("/test_ftp", name="test_ftp",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    function testFTPAction(Request $request)
    {
        $result = ['result' => false, "message" => null,];
        if ($request->isXmlHttpRequest()) {
            $customerName = $request->get("customer");
            $em = $this->getDoctrine()->getManager();
            /** @var Customer $customer */
            $customer = $em->getRepository('AppBundle:Customer')->findOneByName(strtoupper($customerName));
            if ($customer instanceof Customer) {
                $url = $customer->getUrl() . "/api/management/ftp/test";
//              $url = 'http://localhost/42C/hotvideo/web/app_dev.php/api/management/ftp/test';
                $response = $this->get('app.url.tester')->curlAPI($url);
                if (isset($response["code"]) && isset($response["body"])) {
                    $date = new \DateTime();
                    $date->setTimezone(new \DateTimeZone("Europe/Paris"));
                    $body = json_decode($response["body"], true);
                    if (isset($body["result"]) && isset($body["directory"])) {
                        if ($body['result'] !== true || $body["directory"] != $customer->getPathInvoice()) {
                            $this->messageTestFTP($customer->getName(), $response["body"], true);
                            $result ["message"] = "Erreur connnexion au FTP";
                        } else {
                            $result ["result"] = true;
                            $result["message"] = "Check FTP: OK";
                        }
                    } else {
                        $result['message'] = $body;
                    }
                } else {
                    $result['message'] = "Erreur API: No body";
                }
            } else {
                $result['message'] = "Customer not found";
            }
        } else {
            $result['message'] = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode($result), 200, ['Content-Type' => 'application/json']);
    }

    public function messageTestFTP($customer, $body, $prod = false)
    {
        $message = "Une erreur s'est produite lors de la connexion au Serveur FTP.\n \n ";
        $message .= "API RESPONSE : \n " . $body;
        $message .= "\n \n Veuillez vérifier le fichier parameters.yml du BO " . $customer;
        $message .= "\n\n Management Freebox.";
        $subject = "ERREUR: Connexion FTP " . $customer;
        $this->get('app.service.notifyer')->snsNotif($message . " \n", $subject, $prod);
    }

    /**
     * @Route("/init_data", name="init_data")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function initDateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $customer = new Customer();
        $customer->setName(Customer::HOTVIDEO);
        $customer->setCreated(new \DateTime('now'));
        $customer->setUpdated(new \DateTime('now'));
        $customer->setUrl('https://hotvideo.backoffice.prod.42cloud.io');
        $customer->setUrlDev('https://hotvideo.backoffice.preprod.42cloud.io');
        $customer->setPathInvoice('../factu/');

        $invoices = range(5, 10);
        foreach ($invoices as $i) {
            $invoice = new Invoice();
            $file = $i < 10 ? '32_20170' . $i . '.txt' : '32_2017' . $i . '.txt';
            $invoice->setFile($file);
            $invoice->setS3(true);
            $invoice->setFtp(true);
            $invoice->setCustomer($customer);
            $invoice->setCreated(new \DateTime('2017-' . ($i + 1) . '-01 01:00:00'));
            $em->persist($customer);
            if ($i % 5 == 0) $em->flush();
            $customer->addInvoice($invoice);

        }
        $em->persist($customer);
        $em->flush();

        $customer = new Customer();
        $customer->setName(Customer::PINKTV);
        $customer->setCreated(new \DateTime('now'));
        $customer->setUpdated(new \DateTime('now'));
        $customer->setUrl('https://pink.backoffice.prod.42cloud.io');
        $customer->setUrlDev('https://pink.backoffice.preprod.42cloud.io');
        $customer->setPathInvoice('../factu/');
        $invoices = range(5, 10);
        foreach ($invoices as $i) {
            $invoice = new Invoice();
            $file = $i < 10 ? '24_20170' . $i . '.txt' : '24_2017' . $i . '.txt';

            $invoice->setFile($file);
            $invoice->setS3(true);
            $invoice->setFtp(true);
            $invoice->setCustomer($customer);
            $invoice->setCreated(new \DateTime('2017-' . ($i + 1) . '-01 01:00:00'));
            $em->persist($customer);
            if ($i % 5 == 0) $em->flush();
            $customer->addInvoice($invoice);

        }

        $em->persist($customer);
        $em->flush();

        $customer = new Customer();
        $customer->setName(Customer::BRAZZERS);
        $customer->setCreated(new \DateTime('now'));
        $customer->setUpdated(new \DateTime('now'));
        $invoices = range(5, 10);
        $customer->setUrl('https://brazzers.backoffice.prod.42cloud.io');
        $customer->setUrlDev('https://brazzers.backoffice.preprod.42cloud.io');
        $customer->setPathInvoice('/factu/');
        $em->persist($customer);

        foreach ($invoices as $i) {
            $invoice = new Invoice();
            $file = $i < 10 ? '1097_20170' . $i . '.txt' : '1097_2017' . $i . '.txt';

            $invoice->setFile($file);
            $invoice->setS3(true);
            $invoice->setFtp(true);
            $invoice->setCustomer($customer);
            $invoice->setCreated(new \DateTime('2017-' . ($i + 1) . '-01 01:00:00'));
            $em->persist($customer);
            if ($i % 5 == 0) $em->flush();
            $customer->addInvoice($invoice);
        }

        $em->flush();
        die("Init Data");
        // replace this example code with whatever you need
        return $this->render('default/doc.html.twig', array());
    }


    /**
     * @param Request $request
     * @param $id
     * @Route("/download/{id}", name="exports_download")
     * @return Response
     */
    public function exportDownload(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('AppBundle:Invoice')->find($id);
        if ($invoice instanceof Invoice) {
            $file_path = $invoice->getCustomer() != null ? $invoice->getCustomer()->getUrl() . '/statics/exports/auto_facture_abonnement/' . $invoice->getFile() : null;
            if ($file_path !== null && $invoice->getS3()) {
                $content = file_get_contents($file_path);
//                $size = filesize($file_path);
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-disposition', 'filename=' . $invoice->getFile());
//                $response->headers->set('Content-Length', $size);
                $response->setContent($content);
                return $response;

            } else {
                $this->get('session')->getFlashBag()->add('error', 'Fichier d\'export non-trouvé');
                return $this->redirectToRoute('app_invoice');
            }


        } else {
            $this->get('session')->getFlashBag()->add('error', 'Export non-trouvé');
            return $this->redirectToRoute('app_invoice');
        }
    }

    /**
     *
     * @Route("/crontab", name="app_cron_tab")
     * @param Request $request
     * @return
     */
    public function cronTabAction(Request $request)
    {
//      UPD Database
        $this->get('app.instances.retriver')->s3File($prod = false);
        $cronTabs = $this->getDoctrine()
            ->getRepository(LockFile::class)
            ->createQueryBuilder('lock')
            ->groupBy('lock.name')
            ->orderBy('lock.lastModified', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        
        return $this->render('default/crontab.html.twig', array('cronTabs' => $cronTabs,));
    }

}
