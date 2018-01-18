<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Instance;
use AppBundle\Entity\URL;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * URL controller.
 *
 * @Route("url")
 */
class URLController extends Controller
{

    /**
     * Creates a new instance entity.
     *
     * @Route("/new_ajax", name="instance_new_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function ajaxNewAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $title = $request->get('title');
            $urlHttp = $request->get('url');
            $host = $request->get('host');
            $active = $request->get('active') == "true" ? 1 : 0;
            $instanceID = $request->get('instance');
            $description = $request->get('description');

            if (!empty($urlHttp) && null !== $urlHttp && $instanceID !== null) {
                $em = $this->getDoctrine()->getManager();

                $url = new URL();
                $url->setUrl($urlHttp);

                if (!empty($title) && null !== $title) {
                    $url->setTitle($title);
                }

                if (!empty($host) && null !== $host) {
                    $url->setHost($host);
                }

                if (!empty($description) && null !== $description) {
                    $url->setDescription($description);
                }
                $instance = $em->getRepository('AppBundle:Instance')->find($instanceID);
                if (null !== $instance && $instance instanceof Instance) {
                    $url->setInstance($instance);
                }
                $url->setActive($active);

                $em->persist($url);
                $em->flush();

                if (null !== $url->getId()) {

                    return new Response (json_encode(
                        array('result' => "success",
                            'message' => "L'URL a été créée avec succès",
                            'id' => $url->getId()
                        )
                    ), 200, ['Content-Type' => 'application/json']);
                } else {
                    return new Response (json_encode(
                        array('result' => "error",
                            'message' => "Erreur lors de la création de l'objet",
                        )
                    ), 200, ['Content-Type' => 'application/json']);

                }


            } else {
                $message = 'URL not found';
            }
        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('result' => 'error', "message" => $message)), 200, ['Content-Type' => 'application/json']);

    }

    /**
     * Edit a instance entity.
     *
     * @Route("/edit_ajax", name="instance_edit_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function ajaxEditAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            $title = $request->get('title');
            $urlHttp = $request->get('url');
            $host = $request->get('host');
            $active = $request->get('active') == "true" ? 1 : 0;
            $instanceID = $request->get('instance');
            $description = $request->get('description');

            if (!empty($id) && null !== $id) {
                $em = $this->getDoctrine()->getManager();
                /** @var URL $url */
                $url = $em->getRepository('AppBundle:URL')->find($id);
                if (null == $url) {
                    $message = 'URL not found';
                } else {
                    if ($url->getUrl() !== $urlHttp && $urlHttp !== null && !empty($urlHttp))
                        $url->setUrl($urlHttp);

                    if ($url->getTitle() !== $title && !empty($title) && null !== $title) {
                        $url->setTitle($title);
                    }

                    if ($url->getHost() !== $host && !empty($host) && null !== $host) {
                        $url->setHost($host);
                    }

                    if ($url->getDescription() !== $description && !empty($description) && null !== $description) {
                        $url->setDescription($description);
                    }
                    $instance = $em->getRepository('AppBundle:Instance')->find($instanceID);
                    if ($url->getInstance() !== null && $url->getInstance() !== $instance && null !== $instance && $instance instanceof Instance) {
                        $url->setInstance($instance);
                    }

                    $url->setActive($active);
                    $em->flush();

                    return new Response (json_encode(
                        array('result' => "success",
                            'message' => "L'URL a été modifiée avec succès",
                            'id' => $url->getId()
                        )
                    ), 200, ['Content-Type' => 'application/json']);
                }
            } else {
                $message = 'URL not found';
            }


        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('result' => 'error', "message" => $message)), 200, ['Content-Type' => 'application/json']);

    }

    /**
     * Edit a instance entity.
     *
     * @Route("/remove_ajax", name="instance_remove_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function ajaxRemoveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            if (!empty($id) && null !== $id) {
                $em = $this->getDoctrine()->getManager();
                /** @var URL $url */
                $url = $em->getRepository('AppBundle:URL')->find($id);
                if (null !== $url) {
                    $em->remove($url);
                    $em->flush();

                    return new Response (json_encode(
                        array('result' => "success",
                            'message' => "L'URL a été supprimée.",
                            'remove' => $url->getId() == null ? true : false
                        )
                    ), 200, ['Content-Type' => 'application/json']);
                } else {
                    $message = 'URL not found';
                }
            } else {
                $message = 'URL not found';
            }


        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('result' => 'error', "message" => $message)), 200, ['Content-Type' => 'application/json']);

    }

    /**
     * Edit a instance entity.
     *
     * @Route("/instance_active_ajax", name="instance_active_ajax",  options = {"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function activeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            $active = $request->get('active') == "true" ? true : false;
            if (!empty($id) && null !== $id) {
                $em = $this->getDoctrine()->getManager();
                /** @var URL $url */
                $url = $em->getRepository('AppBundle:URL')->find($id);
                if (null !== $url) {
                    $url->setActive($active);
                    $em->flush();
                    return new Response (json_encode(
                        array('result' => "success",
                            'message' => "L'URL a été modifié.",
                            'active' => $url->getActive(),
                        )
                    ), 200, ['Content-Type' => 'application/json']);
                } else {
                    $message = 'URL not found';
                }
            } else {
                $message = 'URL not found';
            }


        } else {
            $message = "Error: isXmlHttpRequest";
        }
        return new Response (json_encode(array('result' => 'error', "message" => $message)), 200, ['Content-Type' => 'application/json']);

    }
    
}
