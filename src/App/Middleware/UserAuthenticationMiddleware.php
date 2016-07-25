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

    public function __construct($roles_allowed)
    {
        $this->roles_allowed = $roles_allowed;
    }

    public function __invoke($request, $response, $next)
    {
        session_start();
        //Authorizes request here
        if (isset($_SESSION['user_id'])){

            //get user role
            $user_role = $_SESSION['user_role'];

            foreach ($this->roles_allowed as $role_allowed){
                if($role_allowed == $user_role){
                    return $next($request, $response);
                }
            }
        }

        session_start();

        $baseUrl = $request->getUri()->getBaseUrl();
        $route = $request->getAttribute('route');

        $_SESSION['original_route'] = $route->getName();

        return $response = $response->withRedirect($baseUrl, 403);

        //return $next($request, $response);

        //return $response;
    }
}