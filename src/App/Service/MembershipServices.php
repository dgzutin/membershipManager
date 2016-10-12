<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 07.10.16
 * Time: 16:48
 */

namespace App\Service;

use App\Entity\User;

class MembershipServices
{
    public function __construct($container)
    {
        $this->mailService = $container['mailServices'];
        $this->em = $container['em'];
    }

    public function getBillingInfoForTypeAlias($typeAlias, $userId)
    {
        $repository = $this->em->getRepository('App\Entity\Billing');
        $billingAddress = $repository->createQueryBuilder('billing')
            ->select('billing')
            ->where('billing.typeAlias = :typeAlias')
            ->andwhere('billing.userId = :userId')
            ->setParameter('typeAlias', $typeAlias)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($billingAddress != NULL){

            return array('exception' => false,
                         'billingAddress' => $billingAddress,
                         'message' => 'Billing address found');
        }
        else{
            return array('exception' => true,
                         'message' => 'No billing address for resource type '.$typeAlias.' found');
        }
    }

    public function getBillingInfoForMembership($membershipId)
    {

        //TODO: Complete this

        return array('exception' => true,
            'billingAddress' => '',
            'message' => 'No billing address found');
    }
    
}