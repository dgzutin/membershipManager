<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 07.10.16
 * Time: 16:48
 */

namespace App\Service;

use App\Entity\Membership;
use App\Entity\MembershipValidity;
use App\Entity\User;
use App\Entity\UserToMemberRelation;
use DateTime;
use DateInterval;

class MembershipServices
{
    public function __construct($container)
    {
        $this->mailService = $container['mailServices'];
        $this->em = $container['em'];
    }


    public function getBillingInfoForMembership($membershipId)
    {

        //TODO: Complete this

        return array('exception' => true,
            'billingAddress' => '',
            'message' => 'No billing address found');
    }

   // If $representativeUserIdsArray is NULL, only owner will be associated with membership
    public function addMember($ownerUserId, $representativeUserIdsArray, $membershipTypeId)
    {
        
        //retrieve information about membership type
        try{
            $repository = $this->em->getRepository('App\Entity\MembershipType');
            $MembershipType = $repository->createQueryBuilder('MembershipType')
                ->select('MembershipType')
                ->where('MembershipType.id = :id')
                ->setParameter('id', $membershipTypeId)
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        //if membershipTypeId does not exist, returns exception
        if ($MembershipType == NULL){

           return array('exception' => true,
                        'message' => 'Invalid membershipTypeId ('.$membershipTypeId.')');
        }

        //verify if user is already a member of the same membership type
        try{
            $repository = $this->em->getRepository('App\Entity\Membership');
            $Membership = $repository->createQueryBuilder('Membership')
                ->select('Membership')
                ->where('Membership.membershipTypeId = :membershipTypeId')
                ->andWhere('Membership.ownerId = :ownerId')
                ->setParameter('membershipTypeId', $membershipTypeId)
                ->setParameter('ownerId', $ownerUserId)
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        if ($Membership != NULL){
            // In this case it is a renewal
               return  array('exception' => false,
                             'renewal' => true,
                             'membership' => $Membership,
                             'message' => "Membership renew request processed.");
        }

        // Determine the MEMBER ID of the new member
        if ($MembershipType->getUseGlobalMemberNumberAssignment() == true){

            try{
                $repository = $this->em->getRepository('App\Entity\Membership');
                $highestMemberId = $repository->createQueryBuilder('membership')
                    ->select('MAX(membership.memberId)')
                    ->getQuery()
                    ->getSingleScalarResult();
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }
        }
        else{

            try{
                $repository = $this->em->getRepository('App\Entity\Membership');
                $highestMemberId = $repository->createQueryBuilder('membership')
                    ->select('MAX(membership.memberId)')
                    ->where('membership.membershipTypeId = :membershipTypeId')
                    ->setParameter('membershipTypeId', $membershipTypeId)
                    ->getQuery()
                    ->getSingleScalarResult();
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }
        }

        $newMembership = new Membership();

        if($highestMemberId == NULL){

            //TODO: Get this from settings
            $newMembership->setMemberId(10000);
        }
        else{
            $newMembership->setMemberId($highestMemberId + 1);
        }

        $now = new DateTime();
        $newMembership->setMemberRegDate($now);
        $newMembership->setQuickRenewKey(sha1(microtime().rand()));
        $newMembership->setMembershipTypeId($membershipTypeId);
        $newMembership->setOwnerId($ownerUserId);
        $newMembership->setCancelled(false);
        $newMembership->setMembershipGrade(NULL);

        $this->em->persist($newMembership);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        $newUserToMembershipRelation = new UserToMemberRelation();
        $newUserToMembershipRelation->setCreateDate(new DateTime());
        $newUserToMembershipRelation->setMembershipTypeId($membershipTypeId);
        $newUserToMembershipRelation->setMembershipId($newMembership->getId());
        $newUserToMembershipRelation->setUserId($ownerUserId);
        $newUserToMembershipRelation->setIsOwner(true);

        $this->em->persist($newUserToMembershipRelation);
        try{
            $this->em->flush();
            $result =  array('exception' => false,
                             'renewal' => false,
                             'membership' => $newMembership,
                             'message' => "Membership was created");
        }
        catch (\Exception $e){
            $result = array('exception' => true,
                            'message' => $e->getMessage());
        }

        //associate with other representatives (if any)
        if ($representativeUserIdsArray != NULL){

            foreach ($representativeUserIdsArray as $representativeUserId) {

                $newUserToMembershipRelation = new UserToMemberRelation();
                $newUserToMembershipRelation->setCreateDate(date('d/m/Y h:i:s a'));
                $newUserToMembershipRelation->setMemberId($newMembership->getId());
                $newUserToMembershipRelation->setMembershipTypeId($membershipTypeId);
                $newUserToMembershipRelation->setUserId($representativeUserId);
                $newUserToMembershipRelation->setIsOwner(false);

                $this->em->persist($newUserToMembershipRelation);
                try{
                    $this->em->flush();
                }
                catch (\Exception $e){
                    return array('exception' => true,
                                 'message' => $e->getMessage());
                }
            }
        }
        return $result;
    }
    
    //Retrieve all membershipTypes available, including those for which the user is not a member, but can select from
    // It additionally appends information regarding the membership status of the user, provided he/she is a member.
    // If $returnOnlyOwnedMemberships is FALSE also memberships not owned by the user will be returned.
    public function getMembershipTypeAndStatusOfUser(User $user, $membershipTypeId, $returnOnlyOwnedMemberships)
    {
        $repository = $this->em->getRepository('App\Entity\MembershipType');

        if ($membershipTypeId == null){

            if ($user->getRole() == 'ROLE_ADMIN'){

                $membershipTypes = $repository->createQueryBuilder('memberships')
                    ->select('memberships')
                    ->getQuery()
                    ->getResult();
            }
            else{
                $membershipTypes = $repository->createQueryBuilder('memberships')
                    ->select('memberships')
                    ->where('memberships.selectable = :selectable')
                    ->setParameter('selectable', true)
                    ->getQuery()
                    ->getResult();
            }
        }
        else{
            $membershipTypes = $repository->createQueryBuilder('memberships')
                ->select('memberships')
                ->where('memberships.id = :id')
                ->setParameter('id', $membershipTypeId)
                ->getQuery()
                ->getResult();
        }

        $i = 0;
        $membershipTypesArray = NULL;

        foreach ($membershipTypes as $membershipType){

            $repository = $this->em->getRepository('App\Entity\Membership');
            $membership = $repository->createQueryBuilder('Membership')
                ->select('Membership')
                ->where('Membership.ownerId = :ownerId')
                ->andWhere('Membership.membershipTypeId = :membershipTypeId')
                ->setParameter('ownerId', $user->getId())
                ->setParameter('membershipTypeId', $membershipType->getId())
                ->getQuery()
                ->getOneOrNullResult();

            if ($membership  != NULL){

                //check membership validity
                $validity = $this->getMembershipValidity($membership->getId());
                $validUntil_string = null;
                $valid = null;

                if (($validity['exception'] == false) AND ($validity['validity'] != NULL)){

                    $validUntil = $validity['validity']->getValidUntil();
                    $valid = $validity['valid'];
                    $validUntil_string = $validUntil->format('l jS F Y');
                }


                $repository = $this->em->getRepository('App\Entity\MembershipGrade');
                $grade = $repository->createQueryBuilder('MembershipGrade')
                    ->select('MembershipGrade')
                    ->where('MembershipGrade.id = :id')
                    ->setParameter('id', $membership->getMembershipGrade())
                    ->getQuery()
                    ->getOneOrNullResult();

                //Check the membership grade of the member
                if ($grade != Null){
                    $membershipGrade = $grade->getGradeName();
                }
                else{
                    $membershipGrade = null;
                }
                $membershipTypesArray[$i] = array('owner' => true,
                                              //    'user' => $user,
                                                  'membershipGrade' => $membershipGrade,
                                                  'cancelled' => $membership->getCancelled(),
                                                  'memberId' => $membership->getMemberId(),
                                                  'valid' => $valid,
                                                  'validUntil' => $validUntil_string,
                                                  'id' => $membershipType->getId(),
                                                  'typeName' => $membershipType->getTypeName(),
                                                  'description' => $membershipType->getDescription(),
                                                  'terms' => $membershipType->getTerms(),
                                                  'selectable' => $membershipType->getSelectable(),
                                                  'fee' => $membershipType->getFee(),
                                                  'currency' => $membershipType->getCurrency(),
                                                  'recurrence' => $membershipType->getRecurrence());
                $i++;
            }
            else{
                if ($returnOnlyOwnedMemberships == false){

                    $membershipTypesArray[$i] = array('owner' => false,
                     //   'user' => $user,
                        'membershipGrade' => null,
                        'cancelled' => null,
                        'memberId' => null,
                        'validUntil' => null,
                        'valid' => null,
                        'id' => $membershipType->getId(),
                        'typeName' => $membershipType->getTypeName(),
                        'description' => $membershipType->getDescription(),
                        'terms' => $membershipType->getTerms(),
                        'selectable' => $membershipType->getSelectable(),
                        'fee' => $membershipType->getFee(),
                        'currency' => $membershipType->getCurrency(),
                        'recurrence' => $membershipType->getRecurrence());
                    $i++;
                }
            }
        }
        if ($membershipTypesArray != null){
            $message = count($membershipTypesArray).' membership(s) found.';
        }
        else{
            $message = 'No available memberships found';
        }
        return array('exception' => false,
                     'membershipTypes' => $membershipTypesArray,
                     'message' => $message);
    }

    // $threshold should be a string 'MM-DAY'
    // $recurrence should be 'year'
    // if $validUntil == NULL, create it automatically. If not NULL, overrides $threshold and $recurrence
    public function addNewMembershipValidity($membershipId, $recurrence, $threshold, $validFrom, $validUntil)
    {
        //Determine the current year and month
        $now = new DateTime();
        $current_year = (int)$now->format("Y");
        //$current_month = (int)$now->format("M");

        $repository = $this->em->getRepository('App\Entity\Membership');
        $membership = $repository->createQueryBuilder('Membership')
            ->select('Membership')
            ->where('Membership.id = :id')
            ->setParameter('id', $membershipId)
            ->getQuery()
            ->getOneOrNullResult();

        //Check if members exists. Throw an exception if it does not.
        if ($membership == NULL){
            return array('exception' => true,
                         'message' => 'Membership ID '.$membershipId.' does not exist');
        }

        if ($validUntil == NULL){

            $current_validity = $this->getMembershipValidity($membershipId);

            if (($current_validity['exception'] == false) AND ($current_validity['valid'] == true)){
                //In this case membership is still valid, so add one year to it

                $validUntil = $current_validity['validity']->getValidUntil();
                $validUntil->add(new DateInterval('P1Y'));
            }
            else{

                $thresholdDate = new DateTime($current_year.'-'.$threshold.'T23:59:59');
                switch ($recurrence){

                    case 'year':
                        if ($now > $thresholdDate){
                            $current_year = $current_year + 1;
                            $validUntil = new DateTime($current_year.'-12-31T23:59:59');
                        }
                        else{
                            $validUntil = new DateTime($current_year.'-12-31T23:59:59');
                        }
                        break;
                    case 'month':
                        //not supported at the moment
                }
            }
        }
        $validity = new MembershipValidity();
        $validity->setMembershipId($membershipId);

        if ($validFrom == NULL){

            $validity->setValidFrom(new DateTime());
        }
        else{
            $validity->setValidFrom($validFrom);
        }

        //$validUntil = new DateTime();
        //$validUntil = $validUntil->add(new DateInterval('P3Y'));

        $validity->setValidUntil($validUntil);
        $validity->setDate(new DateTime());

        $this->em->persist($validity);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        return array('exception' => false,
                     'validity' => $validity);

    }

    function getMembershipValidity($membershipId)
    {
        $repository = $this->em->getRepository('App\Entity\MembershipValidity');

        try{
            $maxValidity = $repository->createQueryBuilder('Validity')
                ->select('MAX(Validity.validUntil)')
                ->where('Validity.membershipId = :membershipId')
                ->setParameter('membershipId', $membershipId)
                ->getQuery()
                ->getSingleScalarResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        try{
            $validity = $repository->createQueryBuilder('Validity')
                ->select('Validity')
                ->where('Validity.validUntil = :validUntil')
                ->andWhere('Validity.membershipId = :membershipId')
                ->setParameter('validUntil', $maxValidity)
                ->setParameter('membershipId', $membershipId)
                ->getQuery()
                ->getResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'valid' => false,
                         'validity' => null,
                         'message' => $e->getMessage());
        }

        $maxIndex = max(array_keys($validity));

        if (count($validity) > 0){

            $now = new DateTime();

            if ($validity[$maxIndex]->getValidUntil() < $now){
                $valid = false;
            }
            else{
                $valid = true;
            }
            return array('exception' => false,
                         'valid' => $valid,
                         'validity' => $validity[$maxIndex],
                         'message' => 'Validity found for membership ID '.$membershipId);
        }

        return array('exception' => true,
                     'valid' => false,
                     'validity' => null,
                     'message' => 'No validity date found for membership ID '.$membershipId);
    }

    public function getMembershipsForUser($userId){

        $repository = $this->em->getRepository('App\Entity\Membership');
        $memberships = $repository->createQueryBuilder('Membership')
            ->select('Membership')
            ->where('Membership.ownerId = :ownerId')
            ->setParameter('ownerId', $userId)
            ->getQuery()
            ->getResult();

        $membershipsArray = null;
        $validMembershipsFound = false;
        $i = 0;
        foreach ($memberships as $membership)
        {
            if ($membership  != NULL){

                try{
                    $repository = $this->em->getRepository('App\Entity\MembershipType');
                    $membershipType = $repository->createQueryBuilder('MembershipType')
                        ->select('MembershipType')
                        ->where('MembershipType.id = :id')
                        ->setParameter('id', $membership->getMembershipTypeId())
                        ->getQuery()
                        ->getOneOrNullResult();
                }
                catch (\Exception $e){
                    return array('exception' => true,
                        'message' => $e->getMessage());
                }

                //check membership validity
                $validity = $this->getMembershipValidity($membership->getId());
                $validUntil_string = null;
                $valid = false;

                if (($validity['exception'] == false) AND ($validity['validity'] != NULL)){

                    $validUntil = $validity['validity']->getValidUntil();
                    $valid = $validity['valid'];

                    if ($valid == true){
                        $validMembershipsFound = true;
                    }
                    $validUntil_string = $validUntil->format('jS F Y');
                }

                try{
                    $repository = $this->em->getRepository('App\Entity\MembershipGrade');
                    $grade = $repository->createQueryBuilder('MembershipGrade')
                        ->select('MembershipGrade')
                        ->where('MembershipGrade.id = :id')
                        ->setParameter('id', $membership->getMembershipGrade())
                        ->getQuery()
                        ->getOneOrNullResult();
                }
                catch (\Exception $e){
                    return array('exception' => true,
                        'message' => $e->getMessage());
                }

                //Check the membership grade of the member
                if ($grade != Null){
                    $membershipGrade = $grade->getGradeName();
                }
                else{
                    $membershipGrade = null;
                }
                $membershipsArray[$i] = array('owner' => true,
                    //    'user' => $user,
                    'membershipGrade' => $membershipGrade,
                    'cancelled' => $membership->getCancelled(),
                    'memberId' => $membership->getMemberId(),
                    'valid' => $valid,
                    'validUntil' => $validUntil_string,
                    'id' => $membershipType->getId(),
                    'typeName' => $membershipType->getTypeName(),
                    'typeId' => $membershipType->getId(),
                    'description' => $membershipType->getDescription(),
                    'terms' => $membershipType->getTerms(),
                    'selectable' => $membershipType->getSelectable(),
                    'fee' => $membershipType->getFee(),
                    'currency' => $membershipType->getCurrency(),
                    'recurrence' => $membershipType->getRecurrence());
                $i++;
            }
        }

        if ($membershipsArray != null){
            $message = count($membershipsArray).' membership(s) found.';
        }
        else{
            $message = 'No available memberships found';
        }
        return array('exception' => false,
                     'validMembershipFound' => $validMembershipsFound,
                     'memberships' => $membershipsArray,
                     'message' => $message);

    }


}