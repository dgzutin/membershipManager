<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 22.07.16
 * Time: 17:07
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Billing;
use App\Entity\ShoppingCartItem;
use DateTime;
use DateInterval;

class UserServices
{
    public function __construct($container)
    {
        $this->mailService = $container['mailServices'];
        $this->shoppingCartServices = $container['shoppingCartServices'];
        $this->em = $container['em'];

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function authenticateUser($email_1, $password)
    {

        $repository =$this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.email_1 = :email')
            ->setParameter('email', $email_1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user != NULL){

            if (password_verify($password, $user->getPassword())){
                $result = array('exception' => false,
                    'message' => 'User authenticated',
                    'user_id' => $user->getId(),
                    'user_role' => $user->getRole());
            }
            else{
                $result = array('exception' => true,
                                'message' => 'Wrong Password');
            }

            return $result;
        }

        $result = array('exception' => true,
                        'message' => 'Wrong e-mail address');
        return $result;
    }

    public function registerNewUser($user_data)
    {

        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.email_1 = :email_1')
            ->setParameter('email_1', $user_data['email_1'])
            ->getQuery()
            ->getOneOrNullResult();


        if ($user == NULL){

            $newuser = new User();
            $newuser->setTitle($user_data['title']);
            $newuser->setLastName($user_data['last_name']);
            $newuser->setFirstName($user_data['first_name']);
            $newuser->setCity($user_data['city']);
            $newuser->setCountry($user_data['country']);
            $newuser->setDepartment($user_data['department']);
            $newuser->setInstitution($user_data['institution']);
            $newuser->setEmail1($user_data['email_1']);
            $newuser->setEmail2($user_data['email_2']);
            $newuser->setPhone($user_data['phone']);
            $newuser->setPosition($user_data['position']);
            $newuser->setRole('ROLE_USER');

            $hash = password_hash($user_data['password'], PASSWORD_BCRYPT);

            $newuser->setPassword($hash);
            $newuser->setStreet($user_data['street']);
            $newuser->setUserRegDate(date('d/m/Y h:i:s a'));
            $newuser->setWebsite($user_data['website']);
            $newuser->setZip($user_data['zip']);
            $newuser->setActive(false);
            $newuser->setProfileKey(sha1(microtime().rand()));


            $this->em->persist($newuser);

            try{
                $this->em->flush();
                $result = array('exception' => false,
                                'user' => $newuser,
                                'message' => "User account was created. An e-mail was sent to ".$user_data['email_1'].". Follow the instructions in the e-mail to activate your account");
            }
            catch (\Exception $e){
                $result = array('exception' => true,
                    'message' => $e->getMessage());
            }
        }
        else{
            $result = array('exception' => true,
                            'message' => "An account already exists for the e-mail address ".$user_data['email_1'].". Please try retrieving your password");
        }

        return $result;
    }

    public function updateUserProfile($userId, $user_data)
    {

        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();


        if ($user != NULL){

            $user->setTitle($user_data['title']);
            $user->setLastName($user_data['last_name']);
            $user->setFirstName($user_data['first_name']);
            $user->setCity($user_data['city']);
            $user->setCountry($user_data['country']);
            $user->setDepartment($user_data['department']);
            $user->setInstitution($user_data['institution']);
            $user->setEmail1($user_data['email_1']);
            $user->setEmail2($user_data['email_2']);
            $user->setPhone($user_data['phone']);
            $user->setPosition($user_data['position']);
            $user->setStreet($user_data['street']);
            $user->setUserRegDate(date('d/m/Y h:i:s a'));
            $user->setWebsite($user_data['website']);
            $user->setZip($user_data['zip']);
            $user->setActive(true);
            //$user->setProfileKey(sha1(microtime().rand()));

            try{
                $this->em->flush();
                $result = array('exception' => false,
                                'user' => $user,
                                'message' => "User account was updated");
            }
            catch (\Exception $e){
                $result = array('exception' => true,
                                'user' => $user_data,
                                'message' => $e->getMessage());
            }
        }
        else{
            $result = array('exception' => true,
                            'user' => $user_data,
                            'message' => "User account could not be update");
        }

        return $result;
    }

    public function activateAccount($key)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.profileKey = :profileKey')
            ->setParameter('profileKey', $key)
            ->getQuery()
            ->getOneOrNullResult();

        //var_dump($user);

        if ($user != NULL){
            $user->setActive(true);
            $user->setProfileKey(sha1(microtime().rand()));
            $this->em->flush();

            return array('exception' => false,
                         'message' => 'Your account bas been activated');
        }
        else{
            return array('exception' => true,
                         'message' => 'This account has already been activated or the key is invalid.');
        }
    }

    public function resetPassword($userId, $password)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        //var_dump($user);

        if ($user != NULL){
            $user->setActive(true);
            $user->setProfileKey(sha1(microtime().rand()));
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $user->setPassword($hash);

            $this->em->flush();

            return array('exception' => false,
                         'message' => 'Password has been changed');
        }
        else{
            return array('exception' => true,
                         'message' => 'User not found for the provided id');
        }
    }

    public function findUserByKey($key)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.profileKey = :profileKey')
            ->setParameter('profileKey', $key)
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
                             'message' => 'Invalid Key',
                             'user' => NULL);
        }
        return $result;

    }

    public function findUserByEmail($email_1)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.email_1 = :email_1')
            ->setParameter('email_1', $email_1)
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
                'message' => 'No user account exist for the e-mail '.$email_1,
                'user' => NULL);
        }
        return $result;

    }

    public function getUserById($userId)
    {
        $repository = $this->em->getRepository('App\Entity\User');
        $user = $repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user != NULL){

            return array('exception' => false,
                         'user' => $user,
                         'message' => 'User found');
        }
        else{
            return array('exception' => true,
                         'message' => 'User not found. Provided Id is invalid');
        }

    }

    public function findUsersFiltered($filter)
    {
        //TODO: Apply filter here when implemented

        $users = $this->em->getRepository('App\Entity\User')->findAll();
        
        if ($users == null){

            $result = array('exception' => true,
                            'message' => "No users found that match this criteria");
            return $result;
        }
        $numberOfUsers = sizeof($users);

        $result = array('exception' => false,
                        'users' => $users,
                        'numberOfrecords' => $numberOfUsers,
                        'message' => "Users found");

        return $result;
    }

    public function getUserBillingInfo($userId)
    {
        $repository = $this->em->getRepository('App\Entity\Billing');
        $billinInfo = $repository->createQueryBuilder('billing')
            ->select('billing')
            ->where('billing.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        if (count($billinInfo) != 0){

            return array('exception' => false,
                'billinAddress' => $billinInfo,
                'message' => 'User found');
        }
        else{
            return array('exception' => true,
                         'message' => 'No billing address found');
        }
    }

    public function generateInvoiceForUser(User $user, Billing $billingInfo, $cartItems)
    {

        $newInvoice = new Invoice();

        $newInvoice->setUserId($user->getId());
        $newInvoice->setCreateDate(date('d/m/Y h:i:s a'));
        $newInvoice->setDueDate(date('d/m/Y h:i:s a', strtotime("+30 days")));
        $newInvoice->setCurrency($this->settings->getSystemCurrency());
        $newInvoice->setName($billingInfo->getName());
        $newInvoice->setInstitution($billingInfo->getInstitution());
        $newInvoice->setStreet($billingInfo->getStreet());
        $newInvoice->setCity($billingInfo->getCity());
        $newInvoice->setZip($billingInfo->getZip());
        $newInvoice->setCountry($billingInfo->getCountry());
        $newInvoice->setVat($billingInfo->getVat());
        $newInvoice->setReference($billingInfo->getReference());

        $this->em->persist($newInvoice);

        try{
            $this->em->flush();

        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        foreach ($cartItems as $cartItem){

            $invoiceItem = new InvoiceItem();

            $invoiceItem->setInvoiceId($newInvoice->getId());
            $invoiceItem->setName($cartItem->getName());
            $invoiceItem->setDescription($cartItem->getDescription());
            $invoiceItem->setQuantity($cartItem->getQuantity());
            $invoiceItem->setUnitPrice($cartItem->getUnitPrice());
            $invoiceItem->setTotalPrice($cartItem->getTotalPrice());

            $this->em->persist($invoiceItem);

            try{
                $this->em->flush();

            }
            catch (\Exception $e){
                return array('exception' => true,
                             'message' => $e->getMessage());
            }

        }

        return array('exception' => false,
                     'invoiceId' => $newInvoice->getId(),
                     'message' => 'Invoice created. Invoice ID: '.$newInvoice->getId());

    }

    public function getInvoiceDataForUser($invoiceId, $userId)
    {

        $repository = $this->em->getRepository('App\Entity\Invoice');
        $invoice = $repository->createQueryBuilder('invoice')
            ->select('invoice')
            ->where('invoice.id = :invoiceId')
            ->andWhere('invoice.userId = :userId')
            ->setParameter('invoiceId', $invoiceId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($invoice != NULL){

            //retrieve Invoice items
            $repository = $this->em->getRepository('App\Entity\InvoiceItem');
            $invoiceItems = $repository->createQueryBuilder('items')
                ->select('items')
                ->where('items.invoiceId = :invoiceId')
                ->setParameter('invoiceId', $invoiceId)
                ->getQuery()
                ->getResult();

            $totalPrice = $this->shoppingCartServices->getTotalPrice($invoiceItems);

            //get date of invoice

            $invoiceDate = date_create($invoice->getCreateDate());

            $date_formatted = $invoiceDate->format('d/m/Y');


            //Retrieve invoice payment (if any)
            $repository = $this->em->getRepository('App\Entity\InvoicePayment');
            $invoicePayments = $repository->createQueryBuilder('payment')
                ->select('payment')
                ->where('payment.invoiceId = :invoiceId')
                ->setParameter('invoiceId', $invoiceId)
                ->getQuery()
                ->getResult();

            //calculate the outstanding amount
            $amountPaid = 0;

            foreach ($invoicePayments as $invoicePayment){
                $amountPaid = $amountPaid + $invoicePayment->getAmountPaid();
            }
            $outstandingAmount = $totalPrice - $amountPaid;

            if ($outstandingAmount <= 0){
                $message = 'Payment in full has been received. Thank you.';
            }
            elseif(($outstandingAmount < $totalPrice) & ($outstandingAmount > 0)){
                $message = 'This invoice has been partially paid.';
            }
            elseif($outstandingAmount == $totalPrice ){
                $message = 'No payment received until the preset moment.';
            }

            $issuerData = array('nameOfOrganization' => $this->settings->getNameOfOrganization(),
                                'street' => $this->settings->getStreet(),
                                'city' => $this->settings->getCity(),
                                'zip' => $this->settings->getZip(),
                                'country' => $this->settings->getCountry(),
                                'email' => $this->settings->getEmail(),
                                'orgWebsite' => $this->settings->getOrgWebsite(),
                                'vat' => $this->settings->getVat(),
                                'registrationNumber' => $this->settings->getRegistrationNumber(),
                                'iban' => $this->settings->getIban(),
                                'bic' => $this->settings->getBic(),
                                'bankAddress' => $this->settings->getBankAddress(),
                                'paypalEmail' => $this->settings->getPaypalEmail(),
                                'paypalActive' => $this->settings->getPaypalActive(),
                                'paypalSandboxMode' => $this->settings->getPaypalSandboxModeActive(),
                                'wireTransferActive' => $this->settings->getWireTransferActive());

            return array('exception' => false,
                         'invoice' => $invoice,
                         'invoiceDate' => $date_formatted,
                         'invoiceItems' => $invoiceItems,
                         'issuerData' => $issuerData,
                         'totalPrice' => $totalPrice,
                         'amountPaid' => $amountPaid,
                         'outstandingAmount' => $outstandingAmount,
                         'message' => $message);

        }
        else{
            return array('exception' => true,
                         'message' => 'Invoice not found');
        }

    }

}