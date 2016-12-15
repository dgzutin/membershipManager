<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 13.12.16
 * Time: 17:08
 */

namespace App\Service;

use App\Entity\MembershipGrade;
use DateTime;

class SiteManagementServices
{
    public function __construct($container)
    {
        $this->mailService = $container['mailServices'];
        $this->utilsServices = $container->get('utilsServices');
        $this->shoppingCartServices = $container['shoppingCartServices'];
        $this->em = $container['em'];

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function addMembershipGrade($name, $description)
    {

        $newMemberGrade = new MembershipGrade();

        $newMemberGrade->setGradeName($name);
        $newMemberGrade->setDescription($description);
        $newMemberGrade->setDateCreated(new DateTime());

        $this->em->persist($newMemberGrade);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        return array('exception' => false,
                     'membershipGrade' => $newMemberGrade,
                     'message' => 'New membership grade added');
    }

}
