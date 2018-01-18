<?php

namespace AppBundle\Controller;

use AppBundle\Entity\InstanceStatus;
use AppBundle\Model\Varnish;
use Aws\Ec2\Ec2Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActionController
 * @package AppBundle\Controller
 * @Route("/action")
 */
class ActionController extends Controller
{
    /**
     * @Route("/bascule_eip", name="action_bascule_eip")
     */
    public function indexAction(Request $request)
    {
        $instances= $this->getVarnishInstances();

//        dump($instances) & die;
        /** @var Varnish $actif */
        $actif = null;
        /** @var Varnish $standby */
        $standby = null;
        /** @var Varnish $instance */
        foreach ($instances as $instance) {
            if ($instance->getStatusVarnish() == 'Active' && $instance->getStatusInstance() == 'running')
                $actif = $instance;
            elseif ($instance->getStatusVarnish() == 'Standby' && $instance->getStatusInstance() == 'running')
                $standby = $instance;
        }

        if (null !== $actif && null !== $standby) {

            $tags = $actif->getTags();
            $tags_actif= [];

            for ($i = 0; $i < count($tags); $i++) {
                if ($tags[$i]->Key == 'Statut_Varnish')
                    $tags[$i]->Value = 'Failed';

                $tags_actif[] = [ 'Key' => $tags[$i]->Key, 'Value' => $tags[$i]->Value];
            }

            $tags_2 = $standby->getTags();
            $tags_standby= [];

            for ($i = 0; $i < count($tags_2); $i++) {
                if ($tags_2[$i]->Key == 'Statut_Varnish')
                    $tags_2[$i]->Value = 'Active';

                $tags_standby[] = [ 'Key' => $tags_2[$i]->Key, 'Value' => $tags_2[$i]->Value];
            }
            /** @var Ec2Client $ec2 */
            $ec2 = $this->get('aws.ec2');

            $ec2->createTags([
                'DryRun' => false,
                'Resources' => [$actif->getInstanceId()],
                'Tags' => $tags_actif,
            ]);

            $result = $ec2->describeAddresses([
                'DryRun' => false,
                'PublicIps' => ['52.211.1.46'],
            ])->toArray();
            $eip = (json_decode(json_encode($result, NULL)))->Addresses[0];

            $ec2->createTags([
                'DryRun' => false,
                'Resources' => [$standby->getInstanceId()],
                'Tags' => $tags_standby,
            ]);

            $ec2->associateAddress([
                'AllocationId' => $eip->AllocationId,
                'AllowReassociation' => true,
                'DryRun' => false,
                'InstanceId' => $standby->getInstanceId(),
            ]);
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/ream_status/{instanceid}", name="action_ream_status")
     */
    public function reamStatusAction(Request $request, $instanceid)
    {
        $instances= $this->getVarnishInstances();

        /** @var Varnish $fail */
        $fail = null;

        /** @var Varnish $instance */
        foreach ($instances as $instance){
            if($instance->getInstanceId() == $instanceid)
            {
                $fail = $instance;
                break;
            }
        }

        if(null !== $fail) {
            $tags = $fail->getTags();
            $tags_actif = [];

            for ($i = 0; $i < count($tags); $i++) {
                if ($tags[$i]->Key == 'Statut_Varnish')
                    $tags[$i]->Value = 'Standby';

                $tags_actif[] = ['Key' => $tags[$i]->Key, 'Value' => $tags[$i]->Value];
            }

            /** @var Ec2Client $ec2 */
            $ec2 = $this->get('aws.ec2');

            $ec2->createTags([
                'DryRun' => false,
                'Resources' => [$fail->getInstanceId()],
                'Tags' => $tags_actif,
            ]);
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @return array
     */
    private function getVarnishInstances(){
        /** @var Ec2Client $ec2 */
        $ec2 = $this->get('aws.ec2');

        $result = $ec2->describeInstances([
            'DryRun' => false,
            'Filters' => [
                [
                    'Name' => 'tag-key',
                    'Values' => ['Statut_Varnish'],
                ],
            ],
        ])->toArray();

        $instances = [];

        foreach ($result['Reservations'] as $reservation) {
            $instances[] = new Varnish($reservation);
        }

        return $instances;
    }


    /**
     * @param Request $reques
     * @param $instanceid
     * @Route("/action/reset-status/{instanceid}", name="reset_status_notify")
     */
    public function resetAction(Request $reques, $instanceid){

        /** @var InstanceStatus $instanceStatus */
        $instanceStatus = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:InstanceStatus')->createQueryBuilder('i')
            ->where('i.instanceId = :inst')
            ->setParameter('inst', $instanceid)
            ->distinct()
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if(null === $instanceStatus){
            return $this->redirectToRoute('homepage2', ['nodata'=> '1']);
        }

        $instanceStatus->setErrorDate(new \DateTime());
        $instanceStatus->setErrorCode(200);
        $instanceStatus->setErrorContent('OK');
        $instanceStatus->setWhat('Clean');
        $instanceStatus->setStatus(2);

        $this->getDoctrine()->getEntityManager()->persist($instanceStatus);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('logs_see', ['instanceid'=>$instanceid]);
    }
}
