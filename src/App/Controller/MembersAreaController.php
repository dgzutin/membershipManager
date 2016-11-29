<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 02.08.16
 * Time: 11:05
 */
namespace App\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MembersAreaController{

    protected $container;

    //Constructor
    public function __construct($container){

        $this->container = $container;
        $this->systemInfo = $container->get('userServices')->getSystemInfo();
    }

    public function elFinderConnectorAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        //TODO: Define different access rights based on the Membership Type if needed. At the moment only ADMIN has all rights

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($_SESSION['user_id']);
        $url_admin = $request->getUri()->getBaseUrl().'/files/';
        $url_members = $request->getUri()->getBaseUrl()."/files/Member's Area";

        if ($resp['exception'] != true){
            $user = $resp['user'];

            switch ($user->getRole()){
                case 'ROLE_ADMIN':

                    $opts = array(
                        // 'debug' => true,
                        'roots' => array(
                            array(
                                'driver'        => 'LocalFileSystem',            // driver for accessing file system (REQUIRED)
                                'path'          => realpath(dirname(__DIR__).'/../../public/files/'),                 // path to files (REQUIRED)
                                'URL'           => $url_admin,                         // URL to files (REQUIRED)
                                'uploadDeny'    => array('application/x-msdownload'),                // All Mimetypes not allowed to upload
                                'uploadAllow'   => array('image/png'),          // Mimetype `image` and `text/plain` allowed to upload
                                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                                'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
                                'attributes' => array(
                                    array(
                                        'pattern' => '/.+/',
                                        'read'    => true,
                                        'write'   => true,
                                        'locked'  => false,
                                        'hidden'  => false
                                    ),
                                    array(
                                        'pattern' => '/(.tmb+)/',
                                        'read'    => true,
                                        'write'   => true,
                                        'locked'  => false,
                                        'hidden'  => true
                                    ),
                                    array(
                                        'pattern' => '/(.quarantine+)/',
                                        'read'    => true,
                                        'write'   => true,
                                        'locked'  => false,
                                        'hidden'  => true
                                    )
                                )
                            )
                        )
                    );
                    break;

                default:
                    $opts = array(
                        // 'debug' => true,
                        'roots' => array(
                            array(
                                'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                                'path'          => realpath(dirname(__DIR__)."/../../public/files/Member's Area/"),                 // path to files (REQUIRED)
                                'URL'           => $url_members, // URL to files (REQUIRED)
                                'uploadDeny'    => array('application/x-msdownload'),                // All Mimetypes not allowed to upload
                                'uploadAllow'   => array('image/png'),// Mimetype `image` and `text/plain` allowed to upload
                                'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                                'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
                                'attributes' => array(
                                    array(
                                        'pattern' => '/.+/',
                                        'read'    => true,
                                        'write'   => false,
                                        'locked'  => true,
                                        'hidden'  => false
                                    ),
                                    array(
                                        'pattern' => '/(.tmb+)/',
                                        'read'    => true,
                                        'write'   => true,
                                        'locked'  => false,
                                        'hidden'  => true
                                    ),
                                    array(
                                        'pattern' => '/(.quarantine+)/',
                                        'read'    => true,
                                        'write'   => true,
                                        'locked'  => false,
                                        'hidden'  => true
                                    )
                                )
                            )
                        )
                    );

                    break;

            }
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', array ('exception' => true,
                                                                                             'systemInfo' => $this->systemInfo,
                                                                                             'message' => $resp['message']));
        }

        // run elFinder
        $connector = new \elFinderConnector(new \elFinder($opts));
        $connector->run();
    }

    public function soundsAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $filename = $args['fileName'];
        $filePath = realpath(dirname(__DIR__).'/../../public/assets/elFinder-2.1.14/sounds/'.$filename);

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename="'.basename($filename).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

    }


    public function documentsAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        return $this->container->view->render($response, 'members/documents.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                             'user_role' => $_SESSION['user_role']));
    }

}