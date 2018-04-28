<?php

/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 22.07.16
 * Time: 12:31
 */
namespace App\Service;

class MailServices
{
    public function __construct($mailer, $message, $twig, $container)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        $this->twig = $twig;
        $this->em = $container['em'];
        $this->container = $container;

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();

        $this->from = $this->settings->getAcronym().' Secretariat';

    }

    public function sendSingleMail($email, $recipient, $subject, $emailBody, $sender_email, $sender_name)
    {
        $this->message
            ->setSubject($subject)
            ->setFrom(array($sender_email => $sender_name));
        try{
            $this->message->setTo(array($email => $recipient));
            $this->message->setBody($emailBody);
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'Email successfully sent to '.$email);
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                'sent' => $this->mailer->send($this->message),
                'message' => $e->getMessage());
        }
        return $result;
    }

    public function sendInformSiteAdminMail($user, $request)
    {
        $utilsServices = $this->container->get('utilsServices');
        $userProfileLink = $utilsServices->getBaseUrl($request). '/admin/user/'.$user->getId();

        $resp = array('exception' => false,
            'salutation' => 'Dear System Owner,',
            'message' => 'A new user registered with the '.$this->settings->getNameOfOrganization().'.',
            'userId' => $user->getId(),
            'name' => $user->getFirstName().' '.$user->getLastName(),
            'email' => $user->getEmail1(),
            'profileUrl' => $userProfileLink,
            'comments' => $user->getComments(),
            'nameOfOrganization' => $this->settings->getNameOfOrganization(),
            'orgWebsite' => $this->settings->getOrgWebsite());

        $template = $this->twig->loadTemplate('email/newUserNotificationAdmin.html.twig');
        $emailBody = $template->render($resp);


        $this->message
            ->setSubject('New user Registration')
            ->setFrom(array( $this->settings->getEmail() =>  $this->from));
        try{
            $this->message->setTo(array($this->settings->getEmail()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message));
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                'sent' => false,
                'message' => $e->getMessage());
        }
        return $result;
    }

    public function sendActivateAccountMail($user, $request)
    {
        $utilsServices = $this->container->get('utilsServices');
        $activateAccountLink = $utilsServices->getBaseUrl($request). '/activateAccount/'.$user->getProfileKey();

        $resp = array('exception' => false,
            'salutation' => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName().',',
            'message' => 'To activate your account please confirm your email address by clicking the link below.',
            'link' => $activateAccountLink,
            'buttonLabel' => 'Confirm Email And Activate Account',
            'nameOfOrganization' => $this->settings->getNameOfOrganization(),
            'orgWebsite' => $this->settings->getOrgWebsite());

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $emailBody = $template->render($resp);


        $this->message
            ->setSubject('Activate your account')
            ->setFrom(array( $this->settings->getEmail() =>  $this->from));
        try{
            $this->message->setTo(array($user->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'An e-mail was sent to '.$user->getEmail1().'. Follow the instructions in the e-mail to activate your account');
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                'sent' => false,
                'message' => $e->getMessage());
        }
        return $result;
    }

    public function sendUserAddedByAdminEmail($user, $request)
    {
        $utilsServices = $this->container->get('utilsServices');
        $resetPasswordLink = $utilsServices->getBaseUrl($request). '/resetPassword/'.$user->getProfileKey();

        $resp = array('exception' => false,
            'salutation' => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName().',',
            'message' => 'A user account with the '.$this->settings->getNameOfOrganization().' was created for you. To set the password and activate your account please follow the link below.',
            'link' => $resetPasswordLink,
            'buttonLabel' => 'Create password and activate your account',
            'nameOfOrganization' => $this->settings->getNameOfOrganization(),
            'orgWebsite' => $this->settings->getOrgWebsite());

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $emailBody = $template->render($resp);


        $this->message
            ->setSubject('Activate your account')
            ->setFrom(array( $this->settings->getEmail() =>  $this->from));
        try{
            $this->message->setTo(array($user->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'Email successfully sent to '.$user->getEmail1());
        }
        catch (\Exception $e){
            $result = array('exception' => true,
                            'sent' => false,
                            'message' => $e->getMessage());
}
        return $result;
    }

    public function sendResetPasswordMail($user, $request)
    {
        $utilsServices = $this->container->get('utilsServices');
        $recoverPasswordLink = $utilsServices->getBaseUrl($request). '/resetPassword/'.$user->getProfileKey();

        $resp = array('exception' => false,
                      'salutation' => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName().',',
                      'message' => 'To reset your password follow the link below. If you have not requested a password reset please ignore this message.',
                      'link' => $recoverPasswordLink,
                      'buttonLabel' => 'Reset password',
                      'nameOfOrganization' => $this->settings->getNameOfOrganization(),
                      'orgWebsite' => $this->settings->getOrgWebsite());

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $emailBody = $template->render($resp);
        
        $this->message
            ->setSubject('Reset Password request')
            ->setFrom(array( $this->settings->getEmail() => $this->from));
        try{
            $this->message->setTo(array($user->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'emailRecipient' => $user->getEmail1(),
                'message' => 'An e-mail was sent to '.$user->getEmail1().' with instructions to reset your password.');
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                            'notificationType' => 'EMAIL_SENT',
                            'sent' => $this->mailer->send($this->message),
                            'message' => $e->getMessage());
        }
        return $result;
    }

    // $userIds is an array of user ids of the email recipients
    public function sendBulkEmails($userIds, $emailSubject, $emailBody, $replyTo, $request)
    {

        $users = $this->em->getRepository('App\Entity\User')->findById($userIds);

        $results = NULL;
        $i = 0;
        foreach ($users as $user){

            $newMail = $this->replacePlaceholders($emailSubject, $emailBody, $user, null, $request);

            $template = $this->twig->loadTemplate('email/bulkEmail.html.twig');
            $htmlMail = $template->render(array('mailSubject' => $newMail['subject'],
                                                'mailBody' => $newMail['body'],
                                                'nameOfOrganization' => $this->settings->getNameOfOrganization(),
                                                'orgWebsite' => $this->settings->getOrgWebsite()));

            $this->message
                ->setSubject($newMail['subject'])
                ->setFrom(array( $this->settings->getEmail() => $this->from));

            try{
                $this->message->setTo(array($user->getEmail1() => $user->getFirstName().' '.$user->getLastName()));
                $this->message->setBody($htmlMail, 'text/html');
                $this->message->setReplyTo($replyTo);

                $result = array('exception' => false,
                    'userId' => $user->getId(),
                    'sent' => $this->mailer->send($this->message),
                    'message' => 'Email successfully sent to '.$user->getEmail1());
            }
            catch (\Exception $e){

                $result = array('exception' => true,
                    'userId' => $user->getId(),
                    'sent' => $this->mailer->send($this->message),
                    'message' => $e->getMessage());
            }
            $results[$i] = $result;
            $i++;
        }
        return $results;
    }

    // $members is a JSON array of users and memberships
    //Send personalised e-mails
    public function sendBulkEmailsMembers($members, $emailSubject, $emailBody, $replyTo, $request)
    {

        $results = NULL;
        $i = 0;
        foreach ($members as $member){

            $newMail = $this->replacePlaceholdersMembers($emailSubject, $emailBody, $member, $request);
            $template = $this->twig->loadTemplate('email/bulkEmail.html.twig');
            $htmlMail = $template->render(array('mailSubject' => $newMail['subject'],
                'mailBody' => $newMail['body'],
                'nameOfOrganization' => $this->settings->getNameOfOrganization(),
                'orgWebsite' => $this->settings->getOrgWebsite()));

            $this->message
                ->setSubject($newMail['subject'])
                ->setFrom(array( $this->settings->getEmail() => $this->from));

            try{
                $this->message->setTo(array($member->user->email_1 => $member->user->first_name.' '.$member->user->last_name));
                $this->message->setBody($htmlMail, 'text/html');
                $this->message->setReplyTo($replyTo);

                $result = array('exception' => false,
                    'userId' => $member->user->id,
                    'membershipId' => $member->membership->id,
                    'memberId' => $member->membership->memberId,
                    'sent' => $this->mailer->send($this->message),
                    'message' => 'Email successfully sent to '.$member->user->email_1);
            }
            catch (\Exception $e){

                $result = array('exception' => true,
                    'userId' => $member->user->id,
                    'membershipId' => $member->membership->id,
                    'memberId' => $member->membership->memberId,
                    'sent' => $this->mailer->send($this->message),
                    'message' => $e->getMessage());
            }
            $results[$i] = $result;
            $i++;
        }
        return $results;
    }

    //Send newsletter e-mail
    public function sendGenericMassMailMembers($members, $emailSubject, $emailHtmlBody, $replyTo)
    {

        $results = NULL;
        $i = 0;
        foreach ($members as $member){


            $this->message
                ->setSubject($emailSubject)
                ->setFrom(array( $this->settings->getEmail() => $this->from));

            try{
                $this->message->setTo(array($member->user->email_1 => $member->user->first_name.' '.$member->user->last_name));
                $this->message->setBody($emailHtmlBody, 'text/html');
                $this->message->setReplyTo($replyTo);

                $result = array('exception' => false,
                    'userId' => $member->user->id,
                    'membershipId' => $member->membership->id,
                    'memberId' => $member->membership->memberId,
                    'sent' => $this->mailer->send($this->message),
                    'message' => 'Email successfully sent to '.$member->user->email_1);
            }
            catch (\Exception $e){

                $result = array('exception' => true,
                    'userId' => $member->user->id,
                    'membershipId' => $member->membership->id,
                    'memberId' => $member->membership->memberId,
                    'sent' => $this->mailer->send($this->message),
                    'message' => $e->getMessage());
            }
            $results[$i] = $result;
            $i++;
        }
        return $results;
    }

    public function sendCancelMembershipEmail($members_json, $request)
    {
        $subject = 'Membership termination confirmation';
        $body = "<strong>{formalSalutation_en},</strong> <br><br> We are sorry that you decided to leave us and confirm that the following membership has been terminated:<br><br>
                  <strong>Membership Type: </strong> {membershipType} <br>
                  <strong>Member ID:</strong> {memberId} <br><br>
                  Best Regards, <br><br>
                  {nameOfOrganization} - {orgAcronym}";

        return $this->sendBulkEmailsMembers($members_json, $subject, $body, $this->settings->getEmail(), $request);
    }

    public function sendInvoiceToUser($invoiceId, $userId, $request)
    {

        $userServices = $this->container->get('userServices');
        $respInvoiceData = $userServices->getInvoiceDataForUser($invoiceId, $userId);

        if ($respInvoiceData['exception']){

            return array('exception' => true,
                'sent' => false,
                'message' => 'Invoice not found');
        }

        $utilsServices = $this->container->get('utilsServices');
        $userResp = $userServices->getUserById($respInvoiceData['invoice']->getUserId());

        $resp = array(
            'user' => $userResp['user'],
            'exception' => $respInvoiceData['exception'],
            'invoiceData' => $respInvoiceData['invoice'],
            'invoiceDate' => $respInvoiceData['invoiceDate'],
            'invoiceDueDate' => $respInvoiceData['invoiceDueDate'],
            'paidDate' => $respInvoiceData['paidDate'],
            'items' => $respInvoiceData['invoiceItems'],
            'issuerData' => $respInvoiceData['issuerData'],
            'totalPrice' =>  $respInvoiceData['totalPrice'],
            'totalPrice_nett' =>  $respInvoiceData['totalPrice_nett'],
            'totalTaxes' => $respInvoiceData['totalTaxes'],
            'amountPaid' => $respInvoiceData['amountPaid'],
            'outstandingAmount' => $respInvoiceData['outstandingAmount'],
            'outstandingAmount_paypal' => $respInvoiceData['outstandingAmount'],
            'logo' =>  $resetPasswordLink = $utilsServices->getBaseUrl($request). '/assets/images/logo_invoice.png',
            'imgPaid' =>  $resetPasswordLink = $utilsServices->getBaseUrl($request). '/assets/images/paid.png',
            'invoiceLink' =>  $utilsServices->getBaseUrl($request).'/user/singleInvoice/'.$respInvoiceData['invoice']->getId(),
            'message' => $respInvoiceData['message']);

        $template = $this->twig->loadTemplate('email/eMailInvoice.html.twig');
        $emailBody = $template->render($resp);

        if ($respInvoiceData['outstandingAmount'] > 0){

            $this->message
                ->setSubject('Invoice Nr.'.$respInvoiceData['invoice']->getId())
                ->setFrom(array( $this->settings->getEmail() =>  $this->from));
        }
        else{
            $this->message
                ->setSubject('RECEIPT for Invoice Nr.'.$respInvoiceData['invoice']->getId())
                ->setFrom(array( $this->settings->getEmail() =>  $this->from));
        }

        try{
            $this->message->setTo(array($userResp['user']->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'Email successfully sent to '.$userResp['user']->getEmail1());
        }
        catch (\Exception $e){
            $result = array('exception' => true,
                'sent' => false,
                'message' => $e->getMessage());
        }
        return $result;
    }

    public function highlightPlaceholders($emailSubject, $emailBodyText)
    {
        //Highlight all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => '<mark>{resetPasswordLink}</mark>',
            "{formalSalutation_en}" => '<mark>{formalSalutation_en}</mark>',
            "{firstName}" => '<mark>{firstName}</mark>',
            "{lastName}" => '<mark>{lastName}</mark>',
            "{memberId}" =>  '<mark>{memberId}</mark>',
            "{membershipExpiryDate}" =>  '<mark>{membershipExpiryDate}</mark>',
            "{membershipType}" =>  '<mark>{membershipType}</mark>',
            "{memberGrade}" =>  '<mark>{memberGrade}</mark>',
            "{nameOfOrganization}" => '<mark>{nameOfOrganization}</mark>',
            "{orgAcronym}" => '<mark>{orgAcronym}</mark>',
            "{institution}" => '<mark>{institution}</mark>',
        );

        $body_mod = strtr($emailBodyText, $placeholders);
        $subject_mod = strtr($emailSubject, $placeholders);

        return array('body' => $body_mod,
            'subject' => $subject_mod);
    }

    public function createHtmlNewsletter($newsletterData)
    {
        $template = $this->twig->loadTemplate('email/newsletter.html.twig');
        $htmlNewsletter = $template->render($newsletterData);

        return $htmlNewsletter;
    }

    public function createHtmlNewsletterOnlyLink($newsletterData, $user)
    {
        $resp = array('exception' => false,
            'salutation' => 'Dear '.$user->title.' '.$user->first_name.' '.$user->last_name.',',
            'message' => 'The '.$this->settings->getNameOfOrganization().' has just published its latest newsletter issue.',
            'link' => $newsletterData['publicLink'],
            'buttonLabel' => 'Click here to open the newsletter',
            'nameOfOrganization' => $this->settings->getNameOfOrganization(),
            'orgWebsite' => $this->settings->getOrgWebsite());

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $htmlNewsletter = $template->render($resp);

        return $htmlNewsletter;
    }

    private function replacePlaceholdersMembers($emailSubject, $emailBodyText, $member, $request)
    {
        $utilsServices = $this->container->get('utilsServices');
        //Replace all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => $utilsServices->getBaseUrl($request). '/resetPassword/'.$member->user->profileKey,
            "{formalSalutation_en}" => 'Dear '.$member->user->title.' '.$member->user->first_name.' '.$member->user->last_name,
            "{firstName}" => $member->user->first_name,
            "{lastName}" => $member->user->last_name,
            "{institution}" => $member->user->institution,
            "{memberId}" => $member->membership->memberId,
            "{membershipExpiryDate}" => $member->validity_string,
            "{membershipType}" => $member->membershipTypeName,
            "{memberGrade}" => $member->memberGrade,
            "{nameOfOrganization}" => $this->settings->getNameOfOrganization(),
            "{orgAcronym}" => $this->settings->getAcronym()
        );

        $body_mod = strtr($emailBodyText, $placeholders);
        $subject_mod = strtr($emailSubject, $placeholders);

        return array('body' => $body_mod,
            'subject' => $subject_mod);
    }

    private function replacePlaceholders($emailSubject, $emailBodyText, $user, $membership, $request)
    {
        $utilsServices = $this->container->get('utilsServices');

        //Replace all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => $utilsServices->getBaseUrl($request). '/resetPassword/'.$user->getProfileKey(),
            "{formalSalutation_en}" => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName(),
            "{firstName}" => $user->getFirstName(),
            "{lastName}" => $user->getLastName()
        );

        $body_mod = strtr($emailBodyText, $placeholders);
        $subject_mod = strtr($emailSubject, $placeholders);

        return array('body' => $body_mod,
                     'subject' => $subject_mod);
    }

}