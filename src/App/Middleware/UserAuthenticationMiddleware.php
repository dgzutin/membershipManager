<?php
namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UserAuthenticationMiddleware
{
    /**
     * Example Middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next Middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private $roles_allowed;

    public function __construct($roles_allowed, $container)
    {
        $this->userServices = $this->userServices = $container->get('userServices');
        $this->roles_allowed = $roles_allowed;
        $this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
        session_start();
        //Authorizes request here
        if (isset($_SESSION['user_id'])){

            $user = $this->userServices->getUserById((int)$_SESSION['user_id']);

            if ($user['exception']){
                return $this->container->view->render($response, 'userNotificationMail.twig', $user['exception']);
            }
            //get user role

            $user_role = $_SESSION['user_role'];

            $twigEnv = $this->container['view']->getEnvironment();
            $twigEnv->addGlobal('user_role', $user_role);

            foreach ($this->roles_allowed as $role_allowed){
                if($role_allowed == $user_role){
                    return $next($request, $response);
                }
            }
        }

        session_start();

        if ($_SERVER['HTTPS'] != NULL){
            $baseUrl = $request->getUri()->getScheme().'://'.$request->getUri()->getHost();
            $loginUrl =$baseUrl.'/login';
        }
        else{
            $baseUrl = $request->getUri()->getBaseUrl();
            $loginUrl = $request->getUri()->withPath($this->container->router->pathFor('login'));
        }
        $_SESSION['orig_uri'] = $baseUrl.$_SERVER['REQUEST_URI'];

        return $response->withRedirect($loginUrl, 403);
        
    }
}