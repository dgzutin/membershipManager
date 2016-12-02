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
    public function __construct($mailer, $message, $twig, $em)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        $this->twig = $twig;
        $this->em = $em;

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
    
    public function sendActivateAccountMail($user, $request)
    {
        $activateAccountLink = $request->getUri()->getBaseUrl(). '/activateAccount/'.$user->getProfileKey();

        $resp = array('exception' => false,
            'salutation' => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName(),
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
        $recoverPasswordLink = $request->getUri()->getBaseUrl(). '/resetPassword/'.$user->getProfileKey();

        $resp = array('exception' => false,
                      'salutation' => 'Dear '.$user->getTitle().' '.$user->getFirstName().' '.$user->getLastName().',',
                      'message' => 'To reset your password follow the link below. If you have not not requested a password reset please ignore this message.',
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

    // $userIds is an array of user ids of the email recipients
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


    public function highlightPlaceholders($emailSubject, $emailBodyText)
    {
        //Highlight all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => '<mark>{resetPasswordLink}</mark>',
            "{formalSalutation_en}" => '<mark>{formalSalutation_en}</mark>',
            "{firstName}" => '<mark>{firstName}</mark>',
            "{lastName}" => '<mark>{lastName}</mark>'
        );

        $body_mod = strtr($emailBodyText, $placeholders);
        $subject_mod = strtr($emailSubject, $placeholders);

        return array('body' => $body_mod,
            'subject' => $subject_mod);
    }

    private function replacePlaceholdersMembers($emailSubject, $emailBodyText, $member, $request)
    {
        //Replace all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => $request->getUri()->getBaseUrl(). '/resetPassword/'.$member->user->profileKey,
            "{formalSalutation_en}" => 'Dear '.$member->user->title.' '.$member->user->first_name.' '.$member->user->last_name,
            "{firstName}" => $member->user->first_name,
            "{lastName}" => $member->user->last_name
        );

        $body_mod = strtr($emailBodyText, $placeholders);
        $subject_mod = strtr($emailSubject, $placeholders);

        return array('body' => $body_mod,
            'subject' => $subject_mod);
    }

    private function replacePlaceholders($emailSubject, $emailBodyText, $user, $membership, $request)
    {
        //Replace all placeholdes by the actual data
        $placeholders = array("{resetPasswordLink}" => $request->getUri()->getBaseUrl(). '/resetPassword/'.$user->getProfileKey(),
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