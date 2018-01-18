<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Instance;
use AppBundle\Entity\URL;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Instance controller.
 *
 * @Route("instance")
 */
class InstanceController extends Controller
{
    /**
     * Lists all instance entities.
     *
     * @Route("/", name="instance_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $instances = $em->getRepository('AppBundle:Instance')->findAll();

        return $this->render('instance/index.html.twig', array(
            'instances' => $instances,
        ));
    }

    /**
     * Creates a new instance entity.
     *
     * @Route("/new", name="instance_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return
     */
    public function newAction(Request $request)
    {
        $instance = new Instance();
        $form = $this->createForm('AppBundle\Form\InstanceType', $instance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($instance);
            $em->flush();

            return $this->redirectToRoute('instance_show', array('id' => $instance->getId()));
        }

        return $this->render('instance/new.html.twig', array(
            'instance' => $instance,
            'form' => $form->createView(),
        ));
    }
    
    /**
     * Displays a form to edit an existing instance entity.
     *
     * @Route("/{id}/edit", name="instance_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Instance $instance)
    {
        $deleteForm = $this->createDeleteForm($instance);
        $editForm = $this->createForm('AppBundle\Form\InstanceType', $instance);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('instance_edit', array('id' => $instance->getId()));
        }

        return $this->render('instance/edit.html.twig', array(
            'instance' => $instance,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a instance entity.
     *
     * @Route("/{id}", name="instance_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Instance $instance)
    {
        $form = $this->createDeleteForm($instance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($instance);
            $em->flush();
        }

        return $this->redirectToRoute('instance_index');
    }

    /**
     * Creates a form to delete a instance entity.
     *
     * @param Instance $instance The instance entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Instance $instance)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('instance_delete', array('id' => $instance->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }


    /**
     * Lists all instance entities.
     *
     * @Route("/data", name="instance_data_index")
     * @Method("GET")
     */
    public function dataAction()
    {
        $em = $this->getDoctrine()->getManager();

//        $instances = $em->getRepository('AppBundle:Instance')->findAll();
        $data = array(
            "varnish" =>
                [
                    ["/prod/hotvideo/app.php/api/catalogs", "HOTVIDEO PROD API Home"],
                    ["/prod/hotvideo/app.php/api/banners/catalog/1", "HOTVIDEO PROD API HOME catalogue TVOD"],
                    ["/prod/hotvideo/app.php/api/videos_rubrics/rubric=1/catalog=1", "HOTVIDEO PROD API liste video dans la rubrique 1 catalogue tvod"],
                    ["/prod/hotvideo/app.php/api/videos/id=2", "HOTVIDEO PROD API Video details"],
                    ["/prod/hotvideo/statics/qml/default/main.qml", "HOTVIDEO PROD Acces Statics S3 test1"],
                    ["/prod/hotvideo/statics/qml/main.qml", "HOTVIDEO PROD Acces Statics S3  qml main.qml"],

                    ["/prod/brazzers/app.php/api/catalogs", "BRAZZERS PROD API Home"],
                    ["/prod/brazzers/app.php/api/banners/catalog/1", "BRAZZERS PROD API HOME catalogue TVOD"],
                    ["/prod/brazzers/app.php/api/videos_rubrics/rubric=1/catalog=1", "BRAZZERS PROD API liste video dans la rubrique 1 catalogue tvod"],
                    ["/prod/brazzers/app.php/api/videos/id=6", "BRAZZERS PROD API Video details"],
                    ["/prod/brazzers/statics/qml/default/main.qml", "BRAZZERS PROD Acces Statics S3 test1"],
                    ["/prod/brazzers/statics/qml/main.qml", "BRAZZERS PROD Acces Statics S3  qml main.qml"],

                    ["/prod/pink/app.php/api/catalogs", "PINK PROD API Home"],
                    ["/prod/pink/app.php/api/banners/catalog/1", "PINK PROD API HOME catalogue TVOD"],
                    ["/prod/pink/app.php/api/videos_rubrics/rubric=1/catalog=1", "PINK PROD API liste video dans la rubrique 1 catalogue tvod"],
                    ["/prod/pink/app.php/api/videos/id=2", "PINK PROD API Video details"],
                    ["/prod/pink/statics/qml/main.qml", "HOTVIDEO PROD Acces Statics S3  qml main.qml"],

                    ["/prod/viacom/mnj_api/", "API RECO PROD WTV MNJ"],

                    ["/preprod/hotvideo/app.php/api/catalogs", "PINK PREPROD API Home"],
                    ["/preprod/pink/app.php/api/catalogs", "PINK PREPROD API Home"],
                    ["/preprod/brazzers/app.php/api/catalogs", "BRAZZERS PREPROD API Home"],

                ],
            "preprod" => [
                ["hotvideo.backoffice.preprod.42cloud.io", "/api/catalogs", "HOTVIDEO BACKOFFICE PREPROD HOME"],
                ["pink.backoffice.preprod.42cloud.io", "/api/catalogs", "PINK BACKOFFICE PREPROD HOME"],
                ["brazzers.backoffice.preprod.42cloud.io", "/api/catalogs", "BRAZZERS BACKOFFICE PREPROD HOME"],
            ],
            "prod" => [
                ["hotvideo.backoffice.prod.42cloud.io", "/api/catalogs", "HOTVIDEO BACKOFFICE PROD HOME"],
                ["hotvideo.backoffice.prod.42cloud.io", "/api/banners/catalog/1", "HOTVIDEO BACKOFFICE PROD HOME catalogue TVOD"],
                ["hotvideo.backoffice.prod.42cloud.io", "/api/videos_rubrics/rubric=1/catalog=1", "HOTVIDEO BACKOFFICE PROD liste video dans la rubrique 1 catalogue tvod"],
                ["hotvideo.backoffice.prod.42cloud.io", "/api/videos/id=2", "HOTVIDEO BACKOFFICE PROD Video details"],

                ["brazzers.backoffice.prod.42cloud.io", "/api/catalogs", "BRAZZERS BACKOFFICE PROD HOME"],
                ["brazzers.backoffice.prod.42cloud.io", "/api/banners/catalog/1", "BRAZZERS BACKOFFICE PROD HOME catalogue TVOD"],
                ["brazzers.backoffice.prod.42cloud.io", "/api/videos_rubrics/rubric=1/catalog=1", "BRAZZERS BACKOFFICE PROD liste video dans la rubrique 1 catalogue tvod"],
                ["brazzers.backoffice.prod.42cloud.io", "/api/videos/id=6", "BRAZZERS BACKOFFICE PROD Video details"],

                ["pink.backoffice.prod.42cloud.io", "/api/catalogs", "PINK BACKOFFICE PROD HOME"],
                ["pink.backoffice.prod.42cloud.io", "/api/banners/catalog/1", "PINK BACKOFFICE PRODHOME catalogue TVOD"],
                ["pink.backoffice.prod.42cloud.io", "/api/videos_rubrics/rubric=1/catalog=1", "PINK BACKOFFICE PROD liste video dans la rubrique 1 catalogue tvod"],
                ["pink.backoffice.prod.42cloud.io", "/api/videos/id=2", "PINK BACKOFFICE PROD Video details"]
            ],
            "apiprod" => [
                ["hotvideo.42cloud.io", "/app.php/api/catalogs", "HOTVIDEO PROD API  HOME"],
                ["hotvideo.42cloud.io", "/app.php/api/banners/catalog/1", "HOTVIDEO PROD API HOME catalogue TVOD"],
                ["hotvideo.42cloud.io", "/app.php/api/videos_rubrics/rubric=1/catalog=1", "HOTVIDEO API PROD liste video dans la rubrique 1 catalogue tvod"],
                ["hotvideo.42cloud.io", "/app.php/api/videos/id=2", "HOTVIDEO API PROD Video details"],

                ["brazzers.42cloud.io", "/app.php/api/catalogs", "BRAZZERS PROD API  HOME"],
                ["brazzers.42cloud.io", "/app.php/api/banners/catalog/1", "BRAZZERS PROD API HOME catalogue TVOD"],
                ["brazzers.42cloud.io", "/app.php/api/videos_rubrics/rubric=1/catalog=1", "BRAZZERS API PROD liste video dans la rubrique 1 catalogue tvod"],
                ["brazzers.42cloud.io", "/app.php/api/videos/id=6", "BRAZZERS API PROD Video details"],

                ["pinktv.42cloud.io", "/app.php/api/catalogs", "Pink PROD API  HOME"],
                ["pinktv.42cloud.io", "/app.php/api/banners/catalog/1", "Pink PROD API HOME catalogue TVOD"],
                ["pinktv.42cloud.io", "/app.php/api/videos_rubrics/rubric=1/catalog=1", "Pink API PROD liste video dans la rubrique 1 catalogue tvod"],
                ["pinktv.42cloud.io", "/app.php/api/videos/id=2", "Pink API PROD Video details"],
            ],
            "apipreprod" => [
                ["hotvideo.preprod.42cloud.io", "/app.php/api/catalogs", "HOTVIDEO PREPROD API HOME"],
                ["pinktv.preprod.42cloud.io", "/app.php/api/catalogs", "Pink PREPROD API HOME"],
                ["brazzers.preprod.42cloud.io", "/app.php/api/catalogs", "Brazzers PREPROD API HOME"]
            ],
            "other_backend" => [
                ["https://mnj.42c.wiztivi.com/mnj-backend/","API RECO PROD WTV MNJ"],
                ["https://api-mymtv-prod.cognik.eu/iptv_free/ping","API MY MTV PROD"],
            ]
        );
        dump($data);
        //die("Stop");

        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("instances_varnish");
        foreach ($data["varnish"] as $d) {
            $url = new URL();
            $url->setUrl($d[0]);
            $url->setDescription($d[1]);
            $url->setTitle($d[1]);
            $url->setInstance($instances);
            $url->setActive(true);
            $em->persist($url);
            $em->flush();
        }  
        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("other_backend");
        foreach ($data["other_backend"] as $d) {
            $url = new URL();
            $url->setUrl($d[0]);
            $url->setDescription($d[1]);
            $url->setTitle($d[1]);
            $url->setInstance($instances);
            $url->setActive(true);
            $em->persist($url);
            $em->flush();
        }

        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_preprod");
        foreach ($data["preprod"] as $d) {
            $url = new URL();
            $url->setHost($d[0]);
            $url->setUrl($d[1]);
            $url->setDescription($d[2]);
            $url->setTitle($d[2]);
            $url->setInstance($instances);
            $url->setActive(true);
            $em->persist($url);
        }
        $em->flush();

        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_api_preprod");
        foreach ($data["apipreprod"] as $d) {
            $url = new URL();
            $url->setHost($d[0]);
            $url->setUrl($d[1]);
            $url->setDescription($d[2]);
            $url->setTitle($d[2]);
            $url->setActive(true);
            $url->setInstance($instances);
            $em->persist($url);

        }
        $em->flush();
        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_prod");
        foreach ($data["prod"] as $d) {

            $url = new URL();
            $url->setHost($d[0]);
            $url->setUrl($d[1]);
            $url->setDescription($d[2]);
            $url->setTitle($d[2]);
            $url->setActive(true);
            $url->setInstance($instances);
            $em->persist($url);
            $em->flush();
        }
        $instances = $em->getRepository('AppBundle:Instance')->findOneBySlug("backend_api_prod");
        foreach ($data["apiprod"] as $d) {
            $url = new URL();
            $url->setHost($d[0]);
            $url->setUrl($d[1]);
            $url->setDescription($d[2]);
            $url->setTitle($d[2]);
            $url->setActive(true);
            $url->setInstance($instances);
            $em->persist($url);
            $em->flush();
        }

        die("Fin import");
        return $this->render('instance/index.html.twig', array(
            'instances' => "",
        ));
    }
}
