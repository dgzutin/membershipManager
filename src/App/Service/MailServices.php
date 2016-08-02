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
    public function __construct($mailer, $message, $twig)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        $this->twig = $twig;
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
            'nameOfOrganization' => 'Name of Organization',
            'orgWebsite' => 'http://www.igip.org');

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $emailBody = $template->render($resp);


        $this->message
            ->setSubject('Activate your account')
            ->setFrom(array('office@igip.org' => 'Name of Organization'));
        try{
            $this->message->setTo(array($user->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'Email successfully sent to '.$user->getEmail1());
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                'sent' => $this->mailer->send($this->message),
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
                      'nameOfOrganization' => 'Name of Organization',
                      'orgWebsite' => 'http://www.igip.org');

        $template = $this->twig->loadTemplate('email/emailNotificationWithLink.html.twig');
        $emailBody = $template->render($resp);
        
        $this->message
            ->setSubject('Reset Password request')
            ->setFrom(array('office@igip.org' => 'Name of Organization'));
        try{
            $this->message->setTo(array($user->getEmail1()));
            $this->message->setBody($emailBody, 'text/html');
            $result = array('exception' => false,
                'sent' => $this->mailer->send($this->message),
                'message' => 'Email successfully sent to '.$user->getEmail1());
        }
        catch (\Exception $e){

            $result = array('exception' => true,
                'sent' => $this->mailer->send($this->message),
                'message' => $e->getMessage());
        }
        return $result;
    }

}