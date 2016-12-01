<?php
/**
 * Created by PhpStorm.
 * User: Danilo Zutin
 * Date: 13.05.16
 * Time: 14:38
 */


use App\Controller;
use App\Service;
use App\Middleware\UserAuthenticationMiddleware;
use App\EmFactory;

require '../vendor/autoload.php';
require '../vendor/swiftmailer/swiftmailer/lib/swift_required.php';

//require '../src/App/Middleware/UserAuthenticationMiddleware.php';
//require '../src/App/Controller/AdminController.php';


$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
    ],
];

$container = new \Slim\Container($configuration);
$app = new \Slim\App($container);

// Get container
$container = $app->getContainer();


// Register components on container

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../src/App/views', [
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

$container['em'] = function (){

    $appConfig = file_get_contents(__DIR__."/../src/App/config/config.json");
    $jsonConfig = json_decode($appConfig);
    $em = new EmFactory($jsonConfig, true); //change to false if not in Dev mode
    return $em->createEntityManager();
};


//Register Services
$container['mailServices'] = function ($container) {

    $appConfig = file_get_contents(__DIR__."/../src/App/config/config.json");
    $jsonConfig = json_decode($appConfig);
    // Create the Transport
    $transport = Swift_SmtpTransport::newInstance($jsonConfig->swiftmailer->smtp, $jsonConfig->swiftmailer->port, $jsonConfig->swiftmailer->encryption)
        ->setUsername($jsonConfig->swiftmailer->username)
        ->setPassword($jsonConfig->swiftmailer->password)
        ->setAuthMode($jsonConfig->swiftmailer->authentication);
    // Create the Mailer using the created Transport
    $mailer = Swift_Mailer::newInstance($transport);
    $message = Swift_Message::newInstance();

    $loader = new Twig_Loader_Filesystem('../src/App/views');
    $twig = new Twig_Environment($loader, array(
        'cache' => false,
    ));

    return new Service\MailServices($mailer, $message, $twig, $container['em']);
};

$container['userServices'] = function($container){
    return new Service\UserServices($container);
};

$container['shoppingCartServices'] = function($container){
    return new Service\ShoppingCartServices($container);
};

$container['membershipServices'] = function($container){
    return new Service\MembershipServices($container);
};




//Register Controllers
$container['\AdminController'] = function ($container) {
    return new Controller\AdminController($container);
};
$container['\UserController'] = function ($container) {
    return new Controller\UserController($container);
};
$container['\PublicController'] = function ($container) {
    return new Controller\PublicController($container);
};
$container['\ApiController'] = function ($container) {
    return new Controller\ApiController($container);
};
$container['\MembersAreaController'] = function ($container) {
    return new Controller\MembersAreaController($container);
};


//Define the Routes for admin group

$app->group('/admin', function () use ($app) {
    
    $app->get('/users', '\AdminController:usersAction')->setName('usersTableView');
    $app->get('/usersJson', '\AdminController:users')->setName('usersTableView');
    $app->get('/users/{userId}', '\AdminController:viewUserProfileAction')->setName('adminViewUserProfile');
    $app->post('/users/{userId}', '\AdminController:saveUserProfileAction')->setName('adminSaveUserProfile');
    $app->get('/resetPassword/{userId}', '\AdminController:resetPasswordAction')->setName('resetPasswordByAdmin');
    $app->get('/documents', '\MembersAreaController:documentsAction')->setName('documentsAdmin');
    $app->get('/sounds/{fileName}', '\MembersAreaController:soundsAction')->setName('soundsAdmin');
    $app->get('/createBulkMail', '\AdminController:createBulkMailAction')->setName('createBulkMailAdmin');
    $app->post('/createBulkMail', '\AdminController:verifyBulkMailAction')->setName('verifyBulkMailAdmin');
    $app->map(['GET', 'POST'], '/members', '\AdminController:membersAction')->setName('members');


    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_ADMIN')));

//Define the Routes for user group
$app->group('/user', function () use ($app) {

    $app->get('/home', '\UserController:homeAction')->setName('homeUser');
    $app->get('/profile', '\UserController:viewUserProfileAction')->setName('userProfile');
    $app->post('/profile', '\UserController:saveUserProfileAction')->setName('editUserProfile');
    $app->get('/resetPassword', '\UserController:resetPasswordAction')->setName('resetPasswordByUser');
    $app->map(['GET', 'POST'], '/elFinderConnector', '\MembersAreaController:elFinderConnectorAction')->setName('elFinderConnector');
    $app->get('/documents', '\MembersAreaController:documentsAction')->setName('documentsUser');
    $app->get('/sounds/{fileName}', '\MembersAreaController:soundsAction')->setName('soundsUser');
    $app->get('/selectMembership', '\UserController:yourMembershipAction')->setName('yourMembershipUser');
    $app->get('/membershipData/{membershipTypeId}', '\UserController:membershipDataAction')->setName('membershipData');
    $app->get('/orderSummary', '\UserController:orderSummaryAction')->setName('orderSummaryUser');
    $app->get('/addMembershipToCart/{membershipTypeId}', '\UserController:addMembershipToCartAction')->setName('addMembershipToCartUser');
    $app->get('/removeItemfromCart/{itemId}', '\UserController:removeItemfromCartAction')->setName('removeItemfromCartUser');
    $app->post('/processOrder', '\UserController:processMembershipOrderAction')->setName('processOrder');
    $app->get('/singleInvoice/{invoiceId}', '\UserController:singleInvoiceAction')->setName('singleInvoice');
    $app->post('/confirmOrder', '\UserController:confirmOrderAction')->setName('confirmOrder');

    $app->get('/testRoute', '\UserController:testAction')->setName('testRoute');


    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_USER', 'ROLE_ADMIN')));

//Define the Routes for API

$app->group('/api/v1', function () use ($app) {

    $app->post('/sendSingleMail', '\ApiController:sendSingleMailAction' )->setName('sendSingleMail');
    $app->post('/sendBulkMail', '\ApiController:sendBulkMailAction' )->setName('sendBulkMail');
    $app->get('/getFilteredUsers', '\ApiController:getFilteredUsersAction' )->setName('getFilteredUsers');


    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_ADMIN')));


// Define the public routes
$app->get('/', '\PublicController:homeAction');
$app->get('/login', '\PublicController:loginAction')->setName('login');
$app->post('/login', '\PublicController:processLoginAction')->setName('process_login');
$app->get('/logout', '\PublicController:logoutAction')->setName('logout');
$app->get('/register', '\PublicController:registerAction')->setName('register');
$app->post('/register', '\PublicController:processRegisterAction')->setName('processRegister');
$app->get('/activateAccount/{key}', '\PublicController:activateAccountAction')->setName('activateAccount');
$app->get('/resetPassword/{key}', '\PublicController:resetPasswordAction')->setName('resetPassword');
$app->post('/resetPassword/{key}', '\PublicController:processResetPasswordAction')->setName('processResetPassword');
$app->get('/forgotPassword', '\PublicController:forgotPasswordAction')->setName('forgotPassword');
$app->post('/forgotPassword', '\PublicController:processForgotPasswordAction')->setName('processForgotPassword');
$app->post('/paypal_ipn', '\PublicController:paypalIPnAction')->setName('paypal_ipn');

$app->run();