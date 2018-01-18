<?php

namespace AppBundle\Controller;

use AppBundle\Entity\InstanceStatus;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Ec2\Ec2Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogsController extends Controller
{
    /**
     * @Route("/logs/{instanceid}", name="logs_see")
     * @param Request $request
     * @param $instanceid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $instanceid)
    {
        $em = $this->getDoctrine()->getManager();

        if ($instanceid == "other_backend") {
            $instanceStatus = $em->getRepository('AppBundle:InstanceStatus')
                ->createQueryBuilder('i')
                ->where('i.instanceId LIKE :inst')
                ->setParameter('inst', '%API%')
                ->orderBy("i.id", "DESC")
                ->getQuery()
                ->getResult();

            if (null === $instanceStatus) {
                $this->setFlash("custom-alerts alert alert-danger fade in",
                    '<i class="fa fa-warning"></i> <strong>Aucune</strong> donnée pour l\'Instance <b>' . $instanceid . '</b>');
                return $this->redirectToRoute('homepage');
            }

            $params = array();
            $where = '';
            /** @var InstanceStatus $instance */
            foreach ($instanceStatus as $key => $instance) {
                if ($key == 0) {
                    $where .= "i.instanceId = :inst_" . $key;
                } else {
                    $where .= " or i.instanceId = :inst_" . $key;
                }
                $params['inst_' . $key] = $instance->getInstanceId();
            }
            $data['backend'] = "backend";
            $logs = $em->getRepository('AppBundle:LogNotify')->createQueryBuilder('i')
                ->where($where)
                ->setParameters($params)
                ->orderBy('i.errorDate', 'desc')
                ->getQuery();

        } else {
            /** @var InstanceStatus $instanceStatus */
            $instanceStatus = $em->getRepository('AppBundle:InstanceStatus')->createQueryBuilder('i')
                ->where('i.instanceId = :inst')
                ->setParameter('inst', $instanceid)
                ->distinct()
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if (null === $instanceStatus) {
                $this->setFlash("custom-alerts alert alert-warning fade in",
                    '<i class="glyphicon glyphicon-info-sign"></i> <strong>Aucune</strong> donnée trouvée pour l\'Instance <b>' . $instanceid . '</b>');
                return $this->redirectToRoute('homepage');
            }

//        dump($instanceStatus); die;
            $data['ec2'] = $this->get('app.instances.retriver')->getInstanceByID($instanceid);
            $logs = $em->getRepository('AppBundle:LogNotify')->createQueryBuilder('i')
                ->where('i.instanceId = :inst')
                ->setParameter('inst', $instanceid)
                ->orderBy('i.errorDate', 'desc')
                ->getQuery();
            // ->getResult();


        }

        $arrShow = array(10, 20, 50, 100);
        $limitPage = ($request->get('show') !== null && in_array($request->get('show'), $arrShow)) ? $request->get('show') : 10;

        $results = $this->get('knp_paginator')->paginate(
            $logs,
            $request->query->getInt('page', 1),
            $limitPage
        );

        if (empty($results)) {
            $this->setFlash("custom-alerts alert alert-danger fade in",
                '<i class="fa fa-warning"></i> <strong>Aucunes</strong> données pour l\'Instance <b>' . $instanceid . '</b>');
            return $this->redirectToRoute('homepage');
        }

        $data = [
            'instanceStatus' => $instanceStatus,
            'logs' => $results,
            'shows' => $arrShow,
        ];

        // replace this example code with whatever you need
        return $this->render('default/logs.html.twig', $data);
    }

    /**
     * @Route("/notifier.log", name="log_file")
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function fileAction()
    {

        $filepath = $this->container->getParameter('kernel.root_dir') . '/../web/log/notifier.log';

        if (!file_exists($filepath)) {
            $this->setFlash("custom-alerts alert alert-danger fade in",
                '<i class="fa fa-warning"></i> <strong>Aucun</strong> de log trouvé.');
            return $this->redirectToRoute('homepage');
        }
        $logs = array();

        if (($handle = fopen($filepath, 'r')) !== false) {
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    //$logs = explode("\r", $line);
                    $logs[] = $line;
                }
                if (!feof($handle)) {
                    $this->setFlash("custom-alerts alert alert-danger fade in",
                        '<i class="fa fa-warning"></i> <strong>Erreur:</strong> fgets() a échoué\n.');
                    return $this->redirectToRoute('homepage');
                }
            } else {
                $this->setFlash("custom-alerts alert alert-danger fade in",
                    '<i class="fa fa-warning"></i> <strong>Error:</strong> opening the file.');
                return $this->redirectToRoute('homepage');
            }
        } else {
            $this->setFlash("custom-alerts alert alert-danger fade in",
                '<i class="fa fa-warning"></i> <strong>Error:</strong> opening the file.');
            return $this->redirectToRoute('homepage');
        }
        // replace this example code with whatever you need
        return $this->render('default/logFile.html.twig', array(
                "logs" => $logs
            )
        );

    }

    /**
     * @Route("/log_file_ajax", name="log_file_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    public
    function logFileAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
//            $em = $this->getDoctrine()->getManager();
            $filepath = $this->container->getParameter('kernel.root_dir') . '/../web/log/notifier.log';


            if (!file_exists($filepath)) {
                return new Response (json_encode(
                    array('response' => "error",
                        'message' => "Aucun fichier de log trouvé.",
                        'data' => $filepath,
                    )
                ), 200, ['Content-Type' => 'application/json']);
            }
            $logs = array();

            if (($handle = fopen($filepath, 'r')) !== false) {
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        //$logs = explode("\r", $line);
                        $logs[] = $line;
                    }
                    if (!feof($handle)) {
                        return new Response (json_encode(
                            array('response' => "error",
                                'message' => "<strong>Erreur:</strong> fgets() a échoué\n.",
                                'data' => null,
                            )
                        ), 200, ['Content-Type' => 'application/json']);
                    }
                } else {
                    return new Response (json_encode(
                        array('response' => "error",
                            'message' => "<strong>Error:</strong> opening the file.",
                            'data' => null,
                        )
                    ), 200, ['Content-Type' => 'application/json']);
                }
            } else {
                return new Response (json_encode(
                    array('response' => "error",
                        'message' => "<strong>Error:</strong> opening the file.",
                        'data' => $logs,
                    )
                ), 200, ['Content-Type' => 'application/json']);
            }


            return new Response (json_encode(
                array('response' => "success",
                    'message' => "Fichier log a été bien chargé",
                    'data' => $logs,
                )
            ), 200, ['Content-Type' => 'application/json']);

        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('result' => 'error', "data" => null, "message" => $message)), 200, ['Content-Type' => 'application/json']);

    }

    /*
  * @doc custom-alerts alert alert-danger fade in
  */
    private
    function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }
}
