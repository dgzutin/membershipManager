<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 26.03.17
 * Time: 11:19
 */

namespace App\Service;

use \Httpful\Request;

class LinkedInServices
{
    public function __construct($container)
    {
        //$this->mailService = $container['mailServices'];
        $this->container = $container;
        $this->utilsServices = $container->get('utilsServices');
        $this->userServices = $container->get('userServices');
        $this->shoppingCartServices = $container['shoppingCartServices'];
        $this->membershipServices = $container['membershipServices'];
        $this->em = $container['em'];
        $this->mailServices = $container['mailServices'];

    }

    public function findUserByLinkedInId($linkedInId)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.linkedin_id = :linkedin_id')
            ->setParameter('linkedin_id', $linkedInId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user != NULL){
            $user->setActive(true);
            $this->em->flush();

            $result = array('exception' => false,
                'message' => 'Account found',
                'user' => $user);
        }
        else{
            $result =  array('exception' => true,
                'message' => 'No user account found for LinkedIn ID '.$linkedInId,
                'user' => NULL);
        }
        return $result;

    }

    public function getLinkedInUserProfileData($access_token)
    {
        try{
            $profileResp= Request::get('https://api.linkedin.com/v1/people/~:(location:(country:(code)),first-name,last-name,id,email-address,public-profile-url,picture-url,headline,industry,positions:(id,title,summary,start-date,end-date,is-current,company:(id,name,type,size,industry,ticker)))?format=json ')
                ->addHeader('Connection','Keep-Alive')
                ->addHeader('Authorization','Bearer '.$access_token)
                ->send();
        }
        catch (Exception $e) {
            return array('exception' => true,
                'verified' => false,
                'message' => $e->getMessage());
        }

        return array('exception => false',
            'result' => $profileResp->body);
    }

    public function associateAccountWithLinkedIn($user, $linkedInProfile)
    {
        //check if linkedIn id already exists
        $userResp = $this->findUserByLinkedInId($linkedInProfile->id);

        if ($userResp['exception'] == false){

            if ($userResp['user']->getId() == $user->getId()){
                //in this case user is already associated with linkedin account
                $user->setPictureUrl($linkedInProfile->pictureUrl);

                try{
                    $this->em->persist($user);
                    $this->em->flush();
                }
                catch (\Exception $e){
                    return array('exception' => true,
                        'message' => $e->getMessage());
                }
                
                return array('exception' => false,
                    'message' => 'Local user account has already been associated with your LinkedIn account.');
            }
            return array('exception' => true,
                'message' => 'Another user account is associated with this LinkedIn account.');
        }
        $user->setLinkedinId($linkedInProfile->id);
        $user->setPictureUrl($linkedInProfile->pictureUrl);

        try{
            $this->em->persist($user);
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }

        return array('exception' => false,
            'message' => 'Local user account associated with your LinkedIn account',
            'user' => $user);
    }

    public function searchLocalAccount($access_token)
    {
        $linkedInProfileData = $this->getLinkedInUserProfileData($access_token);
        $userResp = $this->findUserByLinkedInId($linkedInProfileData['result']->id);

        //return $linkedInProfileData['result'];

        if ($userResp['exception']){
            $userResp = $this->userServices->findUserByEmail($linkedInProfileData['result']->emailAddress);

            if ($userResp['exception'] == false){
                //link user account with linkedIn account
                return $this->associateAccountWithLinkedIn($userResp['user'], $linkedInProfileData['result']);
            }

            //Create local user account here

            $user_data['email_1'] = $linkedInProfileData['result']->emailAddress;
            $user_data['last_name'] = $linkedInProfileData['result']->lastName;
            $user_data['first_name'] = $linkedInProfileData['result']->firstName;
            $user_data['country'] = strtoupper($linkedInProfileData['result']->location->country->code);
            $user_data['website'] = $linkedInProfileData['result']->publicProfileUrl;
            $user_data['pictureUrl'] = $linkedInProfileData['result']->pictureUrl;

            foreach ($linkedInProfileData['result']->positions->values as $position){

                if ($position->isCurrent){
                    $user_data['institution'] = $position->company->name;
                    $user_data['position'] = $position->title;
                }
            }

            $createUserResp = $this->userServices->registerNewUser($user_data, true, $linkedInProfileData['result']->id);
            return $createUserResp;
        }

        return array('exception' => false,
            'message' => 'User account found',
            'user' => $userResp['user']);
    }

}