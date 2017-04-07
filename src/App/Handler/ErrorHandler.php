<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 07.04.17
 * Time: 13:59
 */

namespace App\Handler;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ErrorHandler extends \Slim\Handlers\Error
{

    public function __construct($container)
    {
        $this->container = $container;
        $this->settings = $container->get('settings');
        $this->appLogger = $container->get('appLogger');
    }

    public function __invoke($request, $response, $exception) {

        $this->appLogger->error($exception->getMessage().' in '.$exception->getFile().', line '.$exception->getLine());


        if ($this->settings['displayErrorDetails']) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($exception);
            }
        } else {
            $html = '<div class="alert alert-danger">An error has occurred with this membership system. Sorry for the temporary inconvenience.</div>';
        }

        
        return $this->container->view->render($response, 'errorHandler.html.twig', array(
            'exception' => true,
            'title' => 'Application Error',
            'html' => $html));
        
    }

    protected function renderHtmlException($exception)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if (($code = $exception->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $exception->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $exception->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $exception->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $exception->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

}
