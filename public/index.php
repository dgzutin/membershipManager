<?php
/**
 * Created by PhpStorm.
 * User: Danilo Zutin
 * Date: 13.05.16
 * Time: 14:38
 */


use App\Controller;
use App\Service;
use App\Handler;
use App\Middleware\UserAuthenticationMiddleware;
use App\Middleware\ApiAuthenticationMiddleware;
use App\EmFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Handler\DoctrineDBHandler;

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

// Add constant of Supported Log Types
include_once(__DIR__."/../src/App/config/LogTypes.php");

//Crete loggers
$container['appLogger'] = function(){
    // create a log channel
    $logger = new Logger('appLogger');
    $logger->pushHandler(new StreamHandler('../logs/error.log'));
    return $logger;
};
$container['userLogger'] = function($container){
    // create a log channel
    $logger = new Logger('userLogger');
    $logger->pushHandler(new DoctrineDBHandler($container));
    return $logger;
};

// Error Handlers
$container['phpErrorHandler'] = function($container){
    return new Handler\ErrorHandler($container);
};
$container['errorHandler'] = function($container){
    return new Handler\ErrorHandler($container);
};


$app = new \Slim\App($container);

// Get container
$container = $app->getContainer();

//create Doctrine Entity Manager
$container['em'] = function (){

    $appConfig = file_get_contents(__DIR__."/../src/App/config/config.json");
    $jsonConfig = json_decode($appConfig);
    $em = new EmFactory($jsonConfig);
    return $em->createEntityManager();
};

$container['view'] = function ($container) {

    $appConfig = file_get_contents(__DIR__."/../src/App/config/config.json");
    $jsonConfig = json_decode($appConfig);

    $view = new \Slim\Views\Twig('../src/App/views', [
        'cache' => $jsonConfig->twig->viewsCache
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    // retrieve System Info and pass it as a global variable to all templates
    $systemInfo = $container['userServices']->getSystemInfo();
    $twigEnv = $view->getEnvironment();
    $twigEnv->addGlobal('systemInfo', $systemInfo);
    $twigEnv->addGlobal('base_url', $container['utilsServices']->getBaseUrl( $container['request']));

    $convertAmount = new Twig_SimpleFunction('formatAmount', function ($amount) {

        return number_format($amount,2,".","");
    });

    $twigEnv->addFunction($convertAmount);

    return $view;
};

//Register Services

$container['utilsServices'] = function($container){
    return new Service\UtilsServices($container);
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

$container['siteManagementServices'] = function($container){
    return new Service\SiteManagementServices($container);
};

$container['billingServices'] = function($container){
    return new Service\BillingServices($container);
};

$container['pdfGenerationServices'] = function($container){
    return new Service\PdfGenerationServices($container);
};

$container['linkedInServices'] = function($container){
    return new Service\LinkedInServices($container);
};


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
        'cache' =>  $jsonConfig->twig->emailTemplateCache
    ));

    $convertAmount = new Twig_SimpleFunction('formatAmount', function ($amount) {

        return number_format($amount,2,".","");
    });

    $twig->addFunction($convertAmount);
    
    return new Service\MailServices($mailer, $message, $twig, $container);
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


//Define the Routes for admin role

$app->group('/admin', function () use ($app) {

    $app->map(['GET', 'POST'],'/membershipTypes', '\AdminController:membershipTypesAction')->setName('membershipTypes');
    $app->map(['GET', 'POST'],'/membershipType[/{typeId}]', '\AdminController:membershipTypeAction')->setName('membershipType');
    $app->get('/users', '\AdminController:usersAction')->setName('usersTableView');
    $app->get('/usersJson', '\AdminController:users')->setName('usersTableView');
    $app->get('/user/{userId}', '\AdminController:viewUserProfileAction')->setName('adminViewUserProfile');
    $app->post('/user/{userId}', '\AdminController:saveUserProfileAction')->setName('adminSaveUserProfile');
    $app->get('/resetPassword/{userId}', '\AdminController:resetPasswordAction')->setName('resetPasswordByAdmin');
    $app->get('/documents', '\MembersAreaController:documentsAction')->setName('documentsAdmin');
    $app->get('/sounds/{fileName}', '\MembersAreaController:soundsAction')->setName('soundsAdmin');
    $app->get('/createBulkMailUsers', '\AdminController:createBulkMailUsersAction')->setName('createBulkMailUsersAdmin');
    $app->get('/createBulkMailMembers', '\AdminController:createBulkMailMembersAction')->setName('createBulkMailMembersAdmin');
    $app->post('/verifyBulkMailUsers', '\AdminController:verifyBulkMailUsersAction')->setName('verifyBulkMailUsersAdmin');
    $app->post('/verifyBulkMailMembers', '\AdminController:verifyBulkMailMembersAction')->setName('verifyBulkMailMembersAdmin');
    $app->map(['GET', 'POST'], '/registerNewUser', '\AdminController:registerNewUserAction')->setName('registerNewUser');
    $app->map(['GET', 'POST'], '/members[/{userId}]', '\AdminController:membersAction')->setName('members');
	$app->map(['GET', 'POST'], '/membersExport[/{userId}]', '\AdminController:membersExportAction')->setName('membersExport');
    $app->map(['GET', 'POST'], '/member/{memberId}', '\AdminController:memberAction')->setName('adminMember');
    $app->map(['GET', 'POST'], '/addMember/{userId}', '\AdminController:addMemberAction')->setName('addMemberAdmin');
    $app->get('/manageRenewals/{membershipId}', '\AdminController:manageRenewalsAction')->setName('manageRenewals');
    $app->map(['GET', 'POST'],'/userInvoices/{userId}', '\AdminController:userInvoicesAction')->setName('userInvoicesAdmin');
    $app->get('/invoicePayments/{invoiceId}', '\AdminController:invoicePaymentsAction')->setName('invoicePayments');
    $app->map(['GET', 'POST'],'/singleInvoice/{invoiceId}', '\AdminController:singleInvoiceAction')->setName('singleInvoiceAdmin');
    $app->get('/createBulkMailNewsletter/{key}', '\AdminController:createBulkMailNewsletterAction')->setName('createBulkMailNewsletter');
    $app->get('/sendInvoiceToUser/{invoiceId}', '\AdminController:sendInvoiceToUserAction')->setName('sendInvoiceToUserAdmin');
    $app->map(['GET', 'POST'],'/deleteUser/{userId}', '\AdminController:deleteUserAction')->setName('deleteUser');
    $app->get('/impersonateUser/{userId}', '\AdminController:impersonateUserAction')->setName('impersonateUser');
    $app->map(['GET', 'POST'],'/systemSettings', '\AdminController:systemSettingsAction')->setName('systemSettings');
    $app->map(['GET', 'POST'],'/invoice/{userId}[/{invoiceId}]', '\AdminController:invoiceAction')->setName('invoiceAdmin');
    $app->get('/downloadPdfInvoice/{invoiceId}', '\AdminController:downloadPdfInvoiceAction')->setName('downloadPdfInvoiceAdmin');
    $app->get('/downloadPdfReceipt/{invoiceId}', '\AdminController:downloadPdfReceiptAction')->setName('downloadPdfReceiptAdmin');
    $app->get('/userLog/{userId}', '\AdminController:userLogAction')->setName('userLog');
    $app->get('/membershipLog/{membershipId}', '\AdminController:membershipLogAction')->setName('membershipLog');
    $app->get('/errorLog', '\AdminController:errorLogAction')->setName('errorLog');

    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_ADMIN'), $container));

//Define the Routes for editor role
$app->group('/editor', function () use ($app) {

    $app->map(['GET', 'POST'], '/newsletterArticle/{articleId}', '\AdminController:newsletterArticleAction')->setName('newsletterArticleAdmin');
    $app->map(['GET', 'POST'], '/newsletters', '\AdminController:newslettersAction')->setName('newslettersAdmin');
    $app->get('/newsletter/{newsletterId}', '\AdminController:newsletterAction')->setName('newsletterAdmin');
    $app->post('/newsletter/{newsletterId}', '\AdminController:saveNewsletterAdminAction')->setName('saveNewsletterAdmin');
    $app->map(['GET', 'POST'], '/createNewsletter/', '\AdminController:createNewsletterAction')->setName('createNewsletter');
    $app->get('/newsletterPreview/{key}', '\AdminController:newsletterPreviewAction')->setName('newsletterPreview');
    $app->map(['GET', 'POST'], '/addNewsletterArticleEditor/{newsletterId}', '\AdminController:addNewsletterArticleEditorAction')->setName('addNewsletterArticleEditor');
    $app->get('/createBulkMailNewsletterEditor/{key}', '\AdminController:createBulkMailNewsletterAction')->setName('createBulkMailNewsletterEditorMode');

    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_EDITOR', 'ROLE_ADMIN'), $container));

//Define the Routes for user role
$app->group('/user', function () use ($app) {
    
    $app->get('/home', '\UserController:homeAction')->setName('homeUser');
    $app->get('/profile', '\UserController:viewUserProfileAction')->setName('userProfile');
    $app->post('/profile', '\UserController:saveUserProfileAction')->setName('editUserProfile');
    $app->get('/resetPassword', '\UserController:resetPasswordAction')->setName('resetPasswordByUser');
    $app->map(['GET', 'POST'], '/elFinderConnector', '\MembersAreaController:elFinderConnectorAction')->setName('elFinderConnector');
    $app->get('/documents', '\MembersAreaController:documentsAction')->setName('documentsUser');
    $app->get('/sounds/{fileName}', '\MembersAreaController:soundsAction')->setName('soundsUser');
    $app->get('/selectMembership', '\UserController:selectMembershipAction')->setName('selectMembership');
    $app->get('/membershipData/{membershipTypeId}', '\UserController:membershipDataAction')->setName('membershipData');
    $app->get('/membershipOrderSummary/{membershipTypeId}', '\UserController:membershipOrderSummaryAction')->setName('orderSummaryUser');
    $app->get('/addMembershipToCart/{membershipTypeId}', '\UserController:addMembershipToCartAction')->setName('addMembershipToCartUser');
    $app->get('/removeItemfromCart/{itemId}', '\UserController:removeItemfromCartAction')->setName('removeItemfromCartUser');
    $app->post('/processMembershipOrder/{membershipTypeId}', '\UserController:processMembershipOrderAction')->setName('processMembershipOrder');
    $app->map(['GET', 'POST'], '/singleInvoice/{invoiceId}', '\UserController:singleInvoiceAction')->setName('singleInvoice');
    $app->post('/confirmMembershipOrder/{membershipTypeId}', '\UserController:confirmMembershipOrderAction')->setName('confirmOrder');
    $app->map(['GET', 'POST'], '/manageMembership/{memberId}', '\UserController:manageMembershipAction')->setName('manageMembership');
    $app->map(['GET', 'POST'], '/cancelMembership/{memberId}', '\UserController:cancelMembershipAction')->setName('cancelMembership');
    $app->get('/sendInvoiceToUser/{invoiceId}', '\UserController:sendInvoiceToUserAction')->setName('sendInvoiceToUser');
    $app->map(['GET', 'POST'], '/newsletterArticle', '\UserController:newsletterArticleAction')->setName('newsletterArticle');
    $app->get('/newsletters', '\UserController:newslettersAction')->setName('userNewsletters');
    $app->get('/myInvoices', '\UserController:invoicesAction')->setName('userInvoices');
    $app->map(['GET', 'POST'], '/verifyingPayment', '\UserController:verifyingPaymentAction')->setName('verifyingPayment');
    $app->get('/downloadPdfInvoice/{invoiceId}', '\UserController:downloadPdfInvoiceAction')->setName('downloadPdfInvoice');
    $app->get('/downloadPdfReceipt/{invoiceId}', '\UserController:downloadPdfReceiptAction')->setName('downloadPdfReceipt');
    $app->get('/downloadMemberCertificate/{membershipId}', '\UserController:downloadPdfMemberCertificateAction')->setName('downloadPdfMemberCertificate');

    $app->get('/testRoute/{param}', '\UserController:testAction')->setName('testRoute');


    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new UserAuthenticationMiddleware(array('ROLE_USER', 'ROLE_EDITOR', 'ROLE_ADMIN'), $container));

//Define the Routes for admin API

$app->group('/api/v1', function () use ($app) {

    $app->post('/sendSingleMail', '\ApiController:sendSingleMailAction' )->setName('sendSingleMail');
    $app->post('/sendBulkMailUsers', '\ApiController:sendBulkMailUsersAction' )->setName('sendBulkMailUsers');
    $app->post('/sendBulkMailMembers', '\ApiController:sendBulkMailMembersAction' )->setName('sendBulkMailMembers');
    $app->get('/getFilteredUsers', '\ApiController:getFilteredUsersAction' )->setName('getFilteredUsers');
    $app->post('/getFilteredMembers', '\ApiController:getFilteredMembersAction' )->setName('getFilteredMembers');
    $app->post('/membershipQuickRenew', '\ApiController:membershipQuickRenewAction' )->setName('membershipQuickRenew');
    $app->post('/renewMembership', '\ApiController:renewMembershipAction' )->setName('renewMembership');
    $app->post('/deleteValidities', '\ApiController:deleteValiditiesAction' )->setName('deleteValidities');
    $app->post('/deletePayments', '\ApiController:deletePaymentsAction' )->setName('deletePayments');
    $app->post('/addPayment', '\ApiController:addPaymentAction' )->setName('addPayment');
    $app->post('/assignArticlesToNewsletter', '\ApiController:assignArticlesToNewsletterAction' )->setName('assignArticlesToNewsletter');
    $app->post('/sendNewsletter', '\ApiController:sendNewsletterAction' )->setName('sendNewsletter');
    $app->post('/deleteNewsletterArticle', '\ApiController:deleteNewsletterArticleAction' )->setName('deleteNewsletterArticle');
    $app->post('/deleteNewsletter', '\ApiController:deleteNewsletterAction' )->setName('deleteNewsletter');
    $app->post('/deleteMembershipType', '\ApiController:deleteMembershipTypeAction')->setName('deleteMembershipType');
    $app->post('/deleteInvoices', '\ApiController:deleteInvoicesAction')->setName('deleteInvoices');


    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new ApiAuthenticationMiddleware(array('ROLE_ADMIN', 'ROLE_EDITOR'), $container));

$app->group('/api-user/v1', function () use ($app) {

    $app->post('/saveImage', '\ApiController:saveImageAction' )->setName('saveImage');
    $app->post('/cropImage', '\ApiController:cropImageAction' )->setName('cropImage');
    $app->post('/verifyInvoiceFullPayment', '\ApiController:verifyInvoiceFullPayment' )->setName('verifyInvoiceFullPayment');

    //Attach the Middleware to authenticate requests to this group and pass the accepted user roles for this route or group of routes
})->add(new ApiAuthenticationMiddleware(array('ROLE_USER', 'ROLE_EDITOR', 'ROLE_ADMIN'), $container));

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
$app->get('/newsletter/{key}', '\PublicController:publicNewsletterAction')->setName('publicNewsletter');
$app->get('/oauth/v2/redirect', '\PublicController:linkedInOauth2Action')->setName('linkedInOauth2Redirect');
$app->get('/getInstitutionOfActiveMembers/{typeId}', '\PublicController:getInstitutionOfActiveMembersAction')->setName('getActiveMembers');
$app->get('/publicNewsletters', '\PublicController:publicNewslettersAction')->setName('publicNewsletters');
$app->get('/updateDataPrivacyPreferences/{key}', '\PublicController:updateDataPrivacyPreferencesAction')->setName('updateDataPrivacyPreferences');
$app->post('/updateDataPrivacyPreferences/{key}', '\PublicController:processUpdateDataPrivacyPreferencesAction')->setName('processUpdateDataPrivacyPreferences');

$app->run();