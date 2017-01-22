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
        //$this->mailService = $container['mailServices'];
        $this->utilsServices = $container->get('utilsServices');
        $this->shoppingCartServices = $container['shoppingCartServices'];
        $this->em = $container['em'];
        $this->mailServices = $container['mailServices'];

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getSystemInfo()
    {
        try{
            $repository =$this->em->getRepository('App\Entity\Settings');
            $result = $repository->createQueryBuilder('s')
                ->select('s')
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $e){
            $result = array('exception' => true,
                'message' => $e->getMessage());
        }

        $date_now = new DateTime();
        $year = $date_now->format('Y');

        return array('exception' => false,
                     'year' => $year,
                     'settings' => $result);

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
            $newuser->setUserRegDate(new DateTime());
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
                'message' => 'No user account exists for e-mail '.$email_1,
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
        try{
            $repository = $this->em->getRepository('App\Entity\User');
            $usersRes = $repository->findBy($filter, array('id' => 'ASC'));
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        //retrieve all memberships
        try{
            $repository = $this->em->getRepository('App\Entity\Membership');
            $memberships = $repository->findBy(array(), array('ownerId' => 'ASC'));
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        if (count($usersRes) == 0){

           return array('exception' => true,
                        'message' => "No users found that match this criteria");
        }

        $users = array();
        $i = 0;
        foreach ($usersRes as $userRes){

            $membership = $this->utilsServices->searchMembershipsByOwnerId($memberships, $userRes->getId());

            if ($membership != null){

                $users[$i] = array('id' => $userRes->getId(),
                    'country' => $userRes->getCountry(),
                    'firstName' => $userRes->getFirstName(),
                    'lastName' => $userRes->getLastName(),
                    'userRegDate' => $userRes->getUserRegDate(),
                    'email1' => $userRes->getEmail1(),
                    'member' => true);
            }
            else{
                $users[$i] = array('id' => $userRes->getId(),
                    'country' => $userRes->getCountry(),
                    'firstName' => $userRes->getFirstName(),
                    'lastName' => $userRes->getLastName(),
                    'userRegDate' => $userRes->getUserRegDate(),
                    'email1' => $userRes->getEmail1(),
                    'member' => false);
            }
            $i++;
        }

        $numberOfUsers = count($users);

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

    public function generateInvoiceForUser(User $user, Billing $billingInfo, $cartItems, $onPaymentActions, $notifyUser, $request)
    {

        $newInvoice = new Invoice();
        $newInvoice->setUserId($user->getId());
        $date_create = new DateTime();
        $newInvoice->setCreateDate($date_create);

        $date_due = new DateTime();
        $date_due->add(new DateInterval('P1M'));
        $newInvoice->setDueDate($date_due);

        //$newInvoice->setCreateDate(date('d/m/Y'));
        //$newInvoice->setDueDate(date('d/m/Y', strtotime("+30 days")));


        $newInvoice->setCurrency($this->settings->getSystemCurrency());
        $newInvoice->setInvVat($this->settings->getVat());
        $newInvoice->setInvRegistrationNumber($this->settings->getRegistrationNumber());
        $newInvoice->setInvOrganization($this->settings->getNameOfOrganization());
        $newInvoice->setInvStreet($this->settings->getStreet());
        $newInvoice->setInvCity($this->settings->getCity());
        $newInvoice->setInvZip($this->settings->getZip());
        $newInvoice->setInvCountry($this->settings->getCountry());
        $newInvoice->setInvEmail($this->settings->getEmail());
        $newInvoice->setInvPhone($this->settings->getPhone());
        $newInvoice->setVatRate($this->settings->getVatRate());
        $newInvoice->setBillingName($billingInfo->getName());
        $newInvoice->setBillingInstitution($billingInfo->getInstitution());
        $newInvoice->setBillingStreet($billingInfo->getStreet());
        $newInvoice->setBillingCity($billingInfo->getCity());
        $newInvoice->setBillingZip($billingInfo->getZip());
        $newInvoice->setBillingCountry($billingInfo->getCountry());
        $newInvoice->setBillingVat($billingInfo->getVat());
        $newInvoice->setBillingReference($billingInfo->getReference());
        $newInvoice->setPaid(false);
        $newInvoice->setOnPaymentActions($onPaymentActions);
        $newInvoice->setActionsExecuted(false);

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

        //if notify user is true send invoice to user's email

        if ($notifyUser == true){

            $result = $this->mailServices->sendInvoiceToUser($newInvoice->getId(), $user, $request);

            return array('exception' => false,
                         'invoiceId' => $newInvoice->getId(),
                         'userNotified' => $result['sent'],
                         'message' => 'Invoice created. Invoice ID: '.$newInvoice->getId().'. '.$result['message']);

        }

        return array('exception' => false,
                     'invoiceId' => $newInvoice->getId(),
                     'userNotified' => false,
                     'message' => 'Invoice created. Invoice ID: '.$newInvoice->getId());

    }

    public function getInvoiceDataForUser($invoiceId, $userId)
    {

        $repository = $this->em->getRepository('App\Entity\Invoice');
        
        if ($userId != null){
            $invoice = $repository->createQueryBuilder('invoice')
                ->select('invoice')
                ->where('invoice.id = :invoiceId')
                ->andWhere('invoice.userId = :userId')
                ->setParameter('invoiceId', $invoiceId)
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult();
        }
        else{
            $invoice = $repository->createQueryBuilder('invoice')
                ->select('invoice')
                ->where('invoice.id = :invoiceId')
                ->setParameter('invoiceId', $invoiceId)
                ->getQuery()
                ->getOneOrNullResult();            
        }

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

            $invoiceDate = $invoice->getCreateDate();
            $invoiceDate_string = $invoiceDate->format('jS F Y');

            $invoiceDueDate = $invoice->getDueDate();
            $invoiceDueDate_string = $invoiceDueDate->format('l jS F Y');

            $invoicePaidDate = $invoice->getPaidDate();
            $invoicePaidDate_string = null;
            if ($invoicePaidDate != null){
                $invoicePaidDate_string = $invoicePaidDate->format('l jS F Y');
            }

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

            $message = '';
            if ($outstandingAmount <= 0){
                $message = 'Payment in full has been received. Thank you.';
            }
            elseif(($outstandingAmount < $totalPrice) & ($outstandingAmount > 0)){
                $message = 'This invoice has been partially paid.';
            }
            elseif($outstandingAmount == $totalPrice ){
                $message = 'No payment received until the preset moment.';
            }

            $issuerData = array('nameOfOrganization' => $invoice->getInvOrganization(),
                                'street' => $invoice->getInvStreet(),
                                'city' => $invoice->getInvCity(),
                                'zip' => $invoice->getInvZip(),
                                'country' => $invoice->getInvCountry(),
                                'email' => $invoice->getInvEmail(),
                                'phone' => $invoice->getInvPhone(),
                                'vat' => $invoice->getInvVat(),
                                'vat_rate' => $invoice->getVatRate(),
                                'registrationNumber' => $invoice->getInvRegistrationNumber(),
                                'iban' => $this->settings->getIban(),
                                'bic' => $this->settings->getBic(),
                                'bankName' => $this->settings->getBankName(),
                                'bankAddress' => $this->settings->getBankAddress(),
                                'paypalEmail' => $this->settings->getPaypalEmail(),
                                'paypalActive' => $this->settings->getPaypalActive(),
                                'paypalSandboxMode' => $this->settings->getPaypalSandboxModeActive(),
                                'wireTransferActive' => $this->settings->getWireTransferActive());

            return array('exception' => false,
                         'invoiceId' => $invoice->getId(),
                         'invoiceCurrency' => $invoice->getCurrency(),
                         'invoice' => $invoice,
                         'invoiceDate' => $invoiceDate_string,
                         'invoiceDueDate' => $invoiceDueDate_string,
                         'paidDate' => $invoicePaidDate_string,
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

    public function getBillingInfoForUser($userId)
    {
        $repository = $this->em->getRepository('App\Entity\Billing');
        $billingAddress = $repository->createQueryBuilder('billing')
            ->select('billing')
            ->where('billing.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($billingAddress != NULL){

            return array('exception' => false,
                'billing' => $billingAddress,
                'message' => 'Billing address found');
        }
        else{
            return array('exception' => true,
                'message' => 'No billing address for resource user '.$userId.' found');
        }
    }
    
    public function createOrUpdateBillingInfo($billing, User $user)
    {
        $repository = $this->em->getRepository('App\Entity\Billing');
        $existingBillingInfo = $repository->createQueryBuilder('billing')
            ->select('billing')
            ->where('billing.userId = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingBillingInfo == NULL){

            $billingInfo = new Billing();

        }
        else{

            $billingInfo = $existingBillingInfo;
        }
        $billingInfo->setUserId($user->getId());
        $billingInfo->setName($billing['name']);
        $billingInfo->setInstitution($billing['institution']);
        $billingInfo->setStreet($billing['street']);
        $billingInfo->setCountry($billing['country']);
        $billingInfo->setCity($billing['city']);
        $billingInfo->setZip($billing['zip']);
        $billingInfo->setVat($billing['vat']);
        $billingInfo->setReference($billing['reference']);

        $this->em->persist($billingInfo);

        try{
            $this->em->flush();

        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => 'Could not create billing information: '.$e->getMessage());
        }
        return array('exception' => false,
                     'billingInfo' => $billingInfo,
                     'message' => 'New Billing info for user '.$user->getId().' added/updated.');
    }

    public function getInvoices($userId)
    {
        $repository = $this->em->getRepository('App\Entity\Invoice');

        if ($userId != NULL){

            try{
                $invoices = $repository->findBy(array('userId' => $userId), array('id' => 'DESC'));
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }
        }
        else{
            try{
                $invoices = $repository->findBy(array(), array('id' => 'DESC'));
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }
        }

        $openInvoicesArray = null;
        $closedInvoicesArray = null;
            $i = 0;
            $j = 0;
            foreach ($invoices as $invoice){

                $invoiceData = $this->getInvoiceDataForUser($invoice->getId(), $userId);

                if ($invoiceData['outstandingAmount'] > 0){
                    $openInvoicesArray[$i] = $invoiceData;
                    $i++;
                }
                else{
                    $closedInvoicesArray[$j] = $invoiceData;
                    $j++;
                }
            }

        return array ('exception' => false,
                      'numberOfInvoices' => count($openInvoicesArray) + count($closedInvoicesArray),
                      'numberOfOpenInvoices' => count($openInvoicesArray),
                      'numberOfClosedInvoices' => count($closedInvoicesArray),
                      'openInvoicesArray' => $openInvoicesArray,
                      'closedInvoicesArray' => $closedInvoicesArray);
    }
    

}