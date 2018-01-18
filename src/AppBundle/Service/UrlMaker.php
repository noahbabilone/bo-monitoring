<?php

namespace AppBundle\Service;


use AppBundle\Model\Backend;
use AppBundle\Model\Varnish;
use Aws\AutoScaling\AutoScalingClient;
use Aws\Ec2\Ec2Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UrlMaker implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $urlFileNames = null;
//    private $em;
//    private $container;

    /* const ASG_NAMES = [
         'prod' => 'ASG_bo-client_prod_appfreeboxmut',
         'preprod' => 'ASG_bo-client_preprod_appfreeboxmut',
         'api_prod' => 'ASG_backoffice_prod_appfreeboxmut',
         'api_preprod' => 'ASG_backoffice_preprod_appfreeboxmut',
 //        'db_prod' => 'arn:aws:rds:eu-west-1:830240711660:db:database-prod',
 //        'db_preprod' => 'arn:aws:rds:eu-west-1:830240711660:db:database-preprod-freebox',
 
     ];*/

    /**
     * UrlMaker constructor.
     * @param EntityManager $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->urlFileNames = [
            'varnish' => $this->container->getParameter('varnish_url'),
            'backend' => $this->container->getParameter('backend_url'),
            'viacom' => $this->container->getParameter('external_links'),
        ];


    }

    /**
     * @return array
     */
    public function getUrls()
    {
        /** @var Varnish $varnish_actif */
        $varnish_actif = $this->container->get('app.instances.retriver')->getActiveVarnishInstance();

        $backend['prod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('prod');
        $backend['preprod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('preprod');
        $backend['api_prod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('api_prod');
        $backend['api_preprod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('api_preprod');

        $url_set = [];

        $url_set['varnish'] = $this->makeVarnishUrls($varnish_actif);

        $url_set['backend'] = $this->makeBackendsUrls($backend);


        return $url_set;
    }

    /**
     * @return array
     */
    public function getVarnishUrls()
    {
        /** @var Varnish $varnish_actif */
        $varnish_actif = $this->container->get('app.instances.retriver')->getActiveVarnishInstance();

        return $this->makeVarnishUrls($varnish_actif);
    }

    /**
     * @return array
     */
    public function getExternalLinks()
    {
        $continued = true;
        $result = [];
        $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("other_backend");
        if (null !== $instance) {
            $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);
            $continued = count($urls) > 0 ? false : true;
        }
        if (true === $continued) {
            $urls = $this->getUrlSetByType('viacom');
        }


        foreach ($urls as $url) {
            $result[] = [
                'details' => ['name' => $url['msg'],
                    'privateip' => "IP Viacom",
                    'instanceid' => str_replace(" ", "-", $url['msg']),
                    'type' => 'Viacom',
                ],
                'ip' => $url['url'],
                'to_test' => $url['url'],
                'url' => $url,
            ];
        }
        return $result;
    }


    /**
     * @return array
     */
    public function getBackendsUrls()
    {

        $backend['prod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('prod');
        $backend['preprod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('preprod');
        $backend['api_prod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('api_prod');
        $backend['api_preprod'] = $this->container->get('app.instances.retriver')->getBackendInstancesByEnv('api_preprod');

        return $this->makeBackendsUrls($backend);
    }

    /**
     * @param $type
     * @return array
     */
    private function getUrlSetByType($type)
    {
        $filepath = $this->container->getParameter('kernel.root_dir') . '/../web/' . $this->urlFileNames[$type];

        if (!file_exists($filepath))
            return [];

        $url = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                if ($type == 'backend') {
                    $url[] = [
                        'env' => $data[0],
                        'host' => $data[1],
                        'url' => $data[2],
                        'msg' => $data[3],
                    ];

                } else {
                    $url[] = [
                        'url' => $data[0],
                        'msg' => $data[1],
                    ];
                }
            }
            fclose($handle);
        }

        return $url;
    }

    /**
     * @param $varnish Varnish
     * @return array
     */
    private
    function makeVarnishUrls($varnish)
    {
        //$urls = $this->getUrlSetByType('varnish');
        $continued = true;
        $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("instances_varnish");
        if (null !== $instance) {
            $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);
            $continued = count($urls) > 0 ? false : true;
        }

        if (true === $continued) {
            $urls = $this->getUrlSetByType('varnish');
        }
        $details = [
            'name' => $varnish->getInstanceName(),
            'publicip' => $varnish->getPublicIp(),
            'privateip' => $varnish->getPrivateIp(),
            'instanceid' => $varnish->getInstanceId(),
            'type' => 'Varnish',
        ];

        $result = [];

        foreach ($urls as $url) {
            $result[] = [
                'details' => $details,
                'ip' => $varnish->getPublicIp(),
                'to_test' => $varnish->getPublicIp() . $url['url'],
                'url' => $url,
            ];
        }

        return $result;
    }

    /**
     * @param $backends array
     * @return array
     */
    private
    function makeBackendsUrls($backends)
    {
        //$urls = $this->getUrlSetByType('backend');
        $result = [];
        if (isset($backends['prod']) && !empty($backends['prod'])) {
            $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_prod");
            if (null !== $instance) {
                $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);
//                if (count($urls) == 0) {
//                    $urls = $this->getUrlSetByType('backend');
//                }

                /** @var Backend $backend */
                foreach ($backends['prod'] as $backend) {
                    $details = [
                        'name' => $backend->getInstanceName(),
                        'privateip' => $backend->getPrivateIp(),
                        'instanceid' => $backend->getInstanceId(),
                        'tags' => $backend->getTags(),
                        'type' => 'Backoffice Prod',
                    ];

                    foreach ($urls as $url) {

//                        if ($url['env'] != 'prod')
//                            continue;

                        $result['prod'][] = [
                            'details' => $details,
                            'ip' => $backend->getPrivateIp(),
                            'to_test' => $backend->getPrivateIp() . $url['url'],
                            'host_to_test' => $url['host'],
                            'env ' => 'prod',
                            'url' => $url,
                        ];
                    }
                }
            }
        }


        if (isset($backends['preprod']) && !empty($backends['preprod'])) {
            $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_preprod");
            if (null !== $instance) {
                $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);

                /** @var Backend $backend */
                foreach ($backends['preprod'] as $backend) {
                    $details = [
                        'name' => $backend->getInstanceName(),
                        'privateip' => $backend->getPrivateIp(),
                        'instanceid' => $backend->getInstanceId(),
                        'tags' => $backend->getTags(),
                        'type' => 'Backoffice Preprod',
                    ];

                    foreach ($urls as $url) {
//                        if ($url['env'] != 'preprod')
//                            continue;

                        $result['preprod'][] = [
                            'details' => $details,
                            'ip' => $backend->getPrivateIp(),
                            'to_test' => $backend->getPrivateIp() . $url['url'],
                            'host_to_test' => $url['host'],
                            'env ' => 'preprod',
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        if (isset($backends['api_prod']) && !empty($backends['api_prod'])) {


            $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("backend_api_prod");

            if (null !== $instance) {
                $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);
                /** @var Backend $backend */
                foreach ($backends['api_prod'] as $backend) {
                    $details = [
                        'name' => $backend->getInstanceName(),
                        'privateip' => $backend->getPrivateIp(),
                        'instanceid' => $backend->getInstanceId(),
                        'tags' => $backend->getTags(),
                        'type' => 'Backend API Prod',
                    ];

                    foreach ($urls as $url) {
//                        if ($url['env'] != 'apiprod')
//                            continue;

                        $result['api_prod'][] = [
                            'details' => $details,
                            'ip' => $backend->getPrivateIp(),
                            'to_test' => $backend->getPrivateIp() . $url['url'],
                            'host_to_test' => $url['host'],
                            'env ' => 'api_prod',
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        if (isset($backends['api_preprod']) && !empty($backends['api_preprod'])) {
            $instance = $this->em->getRepository('AppBundle:Instance')->findOneBySlug("backoffice_api_preprod");

            if (null !== $instance) {
                $urls = $this->em->getRepository('AppBundle:URL')->getUrlsByInstance($instance);
                /** @var Backend $backend */
                foreach ($backends['api_preprod'] as $backend) {
                    $details = [
                        'name' => $backend->getInstanceName(),
                        'privateip' => $backend->getPrivateIp(),
                        'instanceid' => $backend->getInstanceId(),
                        'tags' => $backend->getTags(),
                        'type' => 'Backend Api preprod',
                    ];

                    foreach ($urls as $url) {
//                        if ($url['env'] != 'apipreprod')
//                            continue;

                        $result['api_preprod'][] = [
                            'details' => $details,
                            'ip' => $backend->getPrivateIp(),
                            'to_test' => $backend->getPrivateIp() . $url['url'],
                            'host_to_test' => $url['host'],
                            'env ' => 'api_preprod',
                            'url' => $url,
                        ];
                    }
                }
            }
        }


        return $result;
    }
}