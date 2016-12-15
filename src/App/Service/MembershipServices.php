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
        $this->mailService = $container->get('mailServices');
        //$this->utilsServices = $container->get('utilsServices');
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
                    ->orderBy('memberships.listingOrder', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
            else{
                $membershipTypes = $repository->createQueryBuilder('memberships')
                    ->select('memberships')
                    ->where('memberships.selectable = :selectable')
                    ->setParameter('selectable', true)
                    ->orderBy('memberships.listingOrder', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
        }
        else{
            if ($user->getRole() == 'ROLE_ADMIN'){

                $membershipTypes = $repository->createQueryBuilder('memberships')
                    ->select('memberships')
                    ->where('memberships.id = :id')
                    ->setParameter('id', $membershipTypeId)
                    ->orderBy('memberships.listingOrder', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
            else {
                $membershipTypes = $repository->createQueryBuilder('memberships')
                    ->select('memberships')
                    ->where('memberships.id = :id')
                    ->andWhere('memberships.selectable = :selectable')
                    ->setParameter('id', $membershipTypeId)
                    ->setParameter('selectable', true)
                    ->orderBy('memberships.listingOrder', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
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

            $isMembershipFree = false;
            if ($membershipType->getFee() == 0){

                $isMembershipFree = true;
            }

            if ($membership  != NULL){

                //check membership validity
                $validity = $this->getMembershipValidity($membership->getId(), $membershipType);
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
                                                  'free' => $isMembershipFree,
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
                        'free' => $isMembershipFree,
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
    public function addNewMembershipValidity($membershipId, $validFrom, $validUntil)
    {
        //get membership type for provided memebrship ID
        $membershipTypeRes = $this->getMembershipType($membershipId);

        if ($membershipTypeRes['exception'] == true){

            return $membershipTypeRes;
        }

        if ($membershipTypeRes['membershipType']->getFee() == 0.00){

            return array('exception' => true,
                         'validity' => null,
                         'message' => 'This membership if free');
        }
        //If passed the tests above, continue ...
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


        $threshold = $membershipTypeRes['membershipType']->getRenewalThreshold();
        $recurrence = $membershipTypeRes['membershipType']->getRecurrence();

        if ($validUntil == NULL){

            $current_validity = $this->getMembershipValidity($membershipId, Null);

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

                        break;

                    default:
                        if ($now > $thresholdDate){
                            $current_year = $current_year + 1;
                            $validUntil = new DateTime($current_year.'-12-31T23:59:59');
                        }
                        else{
                            $validUntil = new DateTime($current_year.'-12-31T23:59:59');
                        }
                        break;
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
                     'validity' => $validity,
                     'id' => $validity->getId(),
                     'validFrom' => $validity->getValidFrom()->format('jS F Y'),
                     'validUntil' => $validity->getValidUntil()->format('jS F Y'),
                     'dateCreated' => $validity->getDate()->format('jS F Y'),
                     'message' => 'New expiry date added');

    }

    function getMembershipValidity($membershipId, $membershipType)
    {
        // if membership fee is free, return valid
        if ($membershipType != Null){
            if ($membershipType->getFee() == 0){

                return array('exception' => false,
                    'valid' => true,
                    'free' => true,
                    'validity' => null,
                    'message' => 'This membership is free');
            }
        }

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
                         'valid' => false,
                         'free' => false,
                         'validity' => null,
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
                         'free' => false,
                         'validity' => null,
                         'message' => $e->getMessage());
        }


        if (count($validity) > 0){

            $maxIndex = max(array_keys($validity));
            $now = new DateTime();
            if ($validity[$maxIndex]->getValidUntil() < $now){
                $valid = false;
            }
            else{
                $valid = true;
            }
            return array('exception' => false,
                         'valid' => $valid,
                         'free' => false,
                         'validity' => $validity[$maxIndex],
                         'message' => 'Validity found for membership ID '.$membershipId);
        }


        return array('exception' => true,
                     'valid' => false,
                     'validity' => null,
                     'message' => 'No validity date found for membership ID '.$membershipId);

    }


    public function getMembershipsForUser($userId)
    {

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

                $validity = $this->getMembershipValidity($membership->getId(), $membershipType);

                $validUntil_string = 'n/a';
                $validUntil = null;
                if (($validity['exception'] == false) AND ($validity['validity'] != NULL)){

                    $validUntil = $validity['validity']->getValidUntil();
                    $validUntil_string = $validUntil->format('jS F Y');
                }
                if ($validity['valid'] == true){
                    $validMembershipsFound = true;
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
                    'valid' => $validity['valid'],
                    'validUntil' => $validUntil,
                    'validUntil_string' => $validUntil_string,
                    'id' => $membershipType->getId(),
                    'typeName' => $membershipType->getTypeName(),
                    'typeId' => $membershipType->getId(),
                    'description' => $membershipType->getDescription(),
                    'terms' => $membershipType->getTerms(),
                    'selectable' => $membershipType->getSelectable(),
                    'free' => $validity['free'],
                    'fee' => $membershipType->getFee(),
                    'currency' => $membershipType->getCurrency(),
                    'recurrence' => $membershipType->getRecurrence());
                $i++;
            }
        }

        if ($membershipsArray != null){
            $message = count($membershipsArray).' membership(s) found';
        }
        else{
            $message = 'No membership(s) associated with your user account found ';
        }
        return array('exception' => false,
                     'validMembershipFound' => $validMembershipsFound,
                     'membershipFound' => count($membershipsArray),
                     'memberships' => $membershipsArray,
                     'message' => $message);

    }

    public function getAllMembershipTypes()
    {
        try{
            $repository = $this->em->getRepository('App\Entity\MembershipType');
            $membershipTypes = $repository->findBy(array(), array('id' => 'ASC'));
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'membershipTypes' => null,
                         'count' => null,
                         'message' => $e->getMessage());
        }
        return array('exception' => false,
                     'count' => count($membershipTypes),
                     'membershipTypes' => $membershipTypes);

    }

    public function getAllMemberGrades()
    {
        try{
            $repository = $this->em->getRepository('App\Entity\MembershipGrade');
            $grades = $repository->findBy(array(), array('id' => 'ASC'));

        }
        catch (\Exception $e){
            return array('exception' => true,
                         'memberGrades' => null,
                         'count' => null,
                         'message' => $e->getMessage());
        }

        return array('exception' => true,
                     'memberGrades' => $grades,
                     'count' => count($grades),
                     'message' => count($grades). ' membership grade(s) found');
    }

    public function getMembers($filter_member, $filter_user, $filter_validity, $onlyValid, $onlyexpired, $never_validated)
    {
        try{
            $repository = $this->em->getRepository('App\Entity\Membership');
            $memberships = $repository->findBy($filter_member, array('id' => 'ASC'));
        }
        catch (\Exception $e){
            return array('exception' => true,
                'count' => null,
                'members' => null,
                'memberGrade' => null,
                'valid' => null,
                'validity' => null,
                'validity_string' => 'n/a',
                'membershipTypePrefix' => null,
                'message' => $e->getMessage());
        }

        $i = 0;
        $memberIds = null;

        try{
            $repository = $this->em->getRepository('App\Entity\User');
            $users = $repository->findBy($filter_user, array('id' => 'ASC'));

        }
        catch (\Exception $e){
            return array('exception' => true,
                         'count' => null,
                         'members' => null,
                         'memberGrade' => null,
                         'valid' => null,
                         'validity' => null,
                         'validity_string' => 'n/a',
                         'membershipTypePrefix' => null,
                         'message' => $e->getMessage());
        }

        //get MembershipTypes and Grades
        $gradesRes = $this->getAllMemberGrades();
        $membershipTypes = $this->getAllMembershipTypes();

        if ($membershipTypes['exception'] == true){
            return array('exception' => true,
                'count' => null,
                'members' => null,
                'memberGrade' => null,
                'valid' => null,
                'validity' => null,
                'validity_string' => 'n/a',
                'membershipTypePrefix' => null,
                'message' => $membershipTypes['message']);
        }

        $members = null;
        //aggregate information from both arrays
        $i = 0;
        foreach ($memberships as $membership){

            // First argument MUST be a SORTED array!
            $memberGrade = $this->searchArrayById($gradesRes['memberGrades'], $membership->getMembershipGrade());

            if ($memberGrade != null){
                $memberGrade = $memberGrade->getGradeName();
            }

            // First argument MUST be a SORTED array!
            $membershipType =  $this->searchArrayById($membershipTypes['membershipTypes'], $membership->getMembershipTypeId());

            // Find the user owner of this membership
            // First argument MUST be a SORTED array!
            $user =  $this->searchArrayById($users, $membership->getOwnerId());

            if ($user != null){

                //find the membership validity
                $validityResp = $this->getMembershipValidity($membership->getId(), $membershipType);

                //convert validity to string to be serialized to JSON
                if ($validityResp['validity'] != null){
                    $validity_string = $validityResp['validity']->getValidUntil()->format('jS F Y');
                }
                else{
                    $validity_string = 'n/a';
                }

                //if condition is match, consider the filter
                if ($filter_validity != null AND $onlyValid == false AND $onlyexpired == false AND $never_validated == false){

                    if ($validityResp['validity'] != null){

                        $validUntil = $validityResp['validity']->getValidUntil();
                        if ($validUntil == $filter_validity){

                            $members[$i] = array('membership' => $membership,
                                'user' => $user,
                                'memberGrade' => $memberGrade,
                                'valid' => $validityResp['valid'],
                                'validity' => $validityResp['validity'],
                                'validity_string' => $validity_string,
                                'membershipTypePrefix' => $membershipType->getPrefix(),
                                'membershipTypeName' => $membershipType->getTypeName());
                            $i++;
                        }
                    }
                }
                elseif ($filter_validity == null AND $onlyValid == true AND $onlyexpired == false AND $never_validated == false){

                    if ($validityResp['valid'] == true){
                        $members[$i] = array('membership' => $membership,
                            'user' => $user,
                            'memberGrade' => $memberGrade,
                            'valid' => $validityResp['valid'],
                            'validity' => $validityResp['validity'],
                            'validity_string' => $validity_string,
                            'membershipTypePrefix' => $membershipType->getPrefix(),
                            'membershipTypeName' => $membershipType->getTypeName());
                        $i++;
                    }
                }
                elseif ($filter_validity == null AND $onlyValid == false AND $onlyexpired == true AND $never_validated == false){

                    if ($validityResp['valid'] == false AND $validityResp['exception'] == false){
                        $members[$i] = array('membership' => $membership,
                            'user' => $user,
                            'memberGrade' => $memberGrade,
                            'valid' => $validityResp['valid'],
                            'validity' => $validityResp['validity'],
                            'validity_string' => $validity_string,
                            'membershipTypePrefix' => $membershipType->getPrefix(),
                            'membershipTypeName' => $membershipType->getTypeName());
                        $i++;
                    }
                }
                elseif ($filter_validity == null AND $onlyValid == false AND $onlyexpired == false AND $never_validated == true){

                    if ($validityResp['exception'] == true){
                        $members[$i] = array('membership' => $membership,
                            'user' => $user,
                            'memberGrade' => $memberGrade,
                            'valid' => $validityResp['valid'],
                            'validity' => $validityResp['validity'],
                            'validity_string' => $validity_string,
                            'membershipTypePrefix' => $membershipType->getPrefix(),
                            'membershipTypeName' => $membershipType->getTypeName());
                        $i++;
                    }

                }
                // do not consider filter
                else{
                    $members[$i] = array('membership' => $membership,
                        'user' => $user,
                        'memberGrade' => $memberGrade,
                        'valid' => $validityResp['valid'],
                        'validity' => $validityResp['validity'],
                        'validity_string' => $validity_string,
                        'membershipTypePrefix' => $membershipType->getPrefix(),
                        'membershipTypeName' => $membershipType->getTypeName());
                    $i++;
                }
            }

        }

        return array('exception' => false,
                     'count' => count($members),
                     'members' => $members,
                     'message' => count($members).' members found');
    }

    public function getMemberByMemberId($memberId)
    {
        try{
            $repository = $this->em->getRepository('App\Entity\Membership');
            $membership = $repository->createQueryBuilder('Membership')
                ->select('Membership')
                ->where('Membership.memberId = :memberId')
                ->setParameter('memberId', $memberId)
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        if($membership != null){

            try{
                $repository = $this->em->getRepository('App\Entity\User');
                $user = $repository->createQueryBuilder('User')
                    ->select('User')
                    ->where('User.id = :id')
                    ->setParameter('id', $membership->getOwnerId())
                    ->getQuery()
                    ->getOneOrNullResult();
            }
            catch (\Exception $e){
                return array('exception' => true,
                             'message' => $e->getMessage());
            }

            if($user != null){

                //get MembershipTypes and Grades
                $gradesRes = $this->getAllMemberGrades();
                $memberGrade = $this->searchArrayById($gradesRes['memberGrades'], $membership->getMembershipGrade());
                $membershipTypes = $this->getAllMembershipTypes();
                $membershipType =  $this->searchArrayById($membershipTypes['membershipTypes'], $membership->getMembershipTypeId());

                //find the membership validity
                $validityResp = $this->getMembershipValidity($membership->getId(), $membershipType);

                //convert validity to string to be serialized to JSON
                if ($validityResp['validity'] != null){
                    $validity_string = $validityResp['validity']->getValidUntil()->format('jS F Y');
                }
                else{
                    $validity_string = 'n/a';
                }


                return  array('exception' => false,
                              'member' => array(
                                  'membership' => $membership,
                                  'user' => $user,
                                  'memberGrade' => $memberGrade,
                                  'valid' => $validityResp['valid'],
                                  'validity' => $validityResp['validity'],
                                  'validity_string' => $validity_string,
                                  'membershipTypePrefix' => $membershipType->getPrefix(),
                                  'membershipTypeName' => $membershipType->getTypeName()
                              ));
                
            }
            return array('exception' => true,
                         'message' => 'User for member with ID '.$memberId.' does not exist');
        }

        return array('exception' => true,
                     'message' => 'Member with ID '.$memberId.' does not exist');
        
    }


    public function getValidities()
    {
        try{
            $repository = $this->em->getRepository('App\Entity\MembershipValidity');
            $validitiy = $repository->createQueryBuilder('MembershipValidity')
                ->select('MAX(MembershipValidity.validUntil), MIN(MembershipValidity.validUntil)')
                ->getQuery()
                ->getSingleResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        return $validitiy;

    }

    public function getMembershipType($membershipId)
    {
        try{
            $repository = $this->em->getRepository('App\Entity\Membership');
            $membership = $repository->createQueryBuilder('Membership')
                ->select('Membership')
                ->where('Membership.id = :id')
                ->setParameter('id', $membershipId)
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        if ($membership != null){

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

            return array ('exception' => false,
                          'membershipType' => $membershipType);
        }
        return array('exception' => true,
                     'message' => 'Membership with ID '.$membershipId.' not found');


    }

    public function getValiditiesForMembershipId($membershipId)
        {

            try{
                $repository = $this->em->getRepository('App\Entity\MembershipValidity');
                $membershipValidity = $repository->createQueryBuilder('MembershipValidity')
                    ->select('MembershipValidity')
                    ->where('MembershipValidity.membershipId = :membershipId')
                    ->setParameter('membershipId', $membershipId)
                    ->getQuery()
                    ->getResult();
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }
            return array('exception' => false,
                         'membershipId' => $membershipId,
                         'membershipValidities' => $membershipValidity);

        }

    public function deleteValidities($ids)
    {

        $results = null;
        $i = 0;
        $deletedCount = 0;
        foreach ($ids as $id){

            try{
                $repository = $this->em->getRepository('App\Entity\MembershipValidity');
                $membershipValidity = $repository->createQueryBuilder('MembershipValidity')
                    ->select('MembershipValidity')
                    ->where('MembershipValidity.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
            }
            catch (\Exception $e){
                $results[$i] = array('exception' => true,
                                     'validityId' => $id,
                                     'message' => $e->getMessage());
                $membershipValidity = -1;
            }

            if (($membershipValidity != null) AND ($membershipValidity != -1)){
                //delete validity

                try{
                    $this->em->remove($membershipValidity);
                    $this->em->flush();

                    $results[$i] = array('exception' => false,
                                         'validityId' => $id,
                                         'message' => 'Item successfully deleted');
                    $deletedCount ++;
                }
                catch (\Exception $e){
                    $results[$i] = array('exception' => true,
                        'validityId' => $id,
                        'message' => $e->getMessage());
                }
            }
            elseif ($membershipValidity == null){
                $results[$i] = array('exception' => true,
                                     'validityId' => $id,
                                     'message' => 'validity with id '.$id.' not found');
            }
            $i++;
        }

        if ($deletedCount == 0){

            return array('exception' => true,
                         'results' => $results,
                         'message' => $deletedCount.' item(s) deleted');
        }
        return array('exception' => false,
                     'results' => $results,
                     'message' => $deletedCount.' item(s) deleted');
    }
    

    //implements binary search to find object by ID in array: ARRAY MUST BE SORTED BY ID !!!
    public function searchArrayById($sortedArray, $id)
    {
        $l = 0;
        $r = count($sortedArray) - 1;

        while ($l <= $r){

            $m = round(($l + $r)/ 2);
            if ($sortedArray[$m]->getId() < $id){
                $l = $m +1;
            }
            elseif ($sortedArray[$m]->getId() > $id){
                $r = $m - 1;
            }
            elseif ($sortedArray[$m]->getId() == $id){

                return $sortedArray[$m];
            }
        }
        return null;
    }
    
}