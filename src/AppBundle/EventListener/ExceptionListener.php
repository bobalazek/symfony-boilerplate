<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ExceptionListener
{
    use ContainerAwareTrait;

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (strpos($event->getRequest()->getPathInfo(), '/api') !== false) {
            $exception = $event->getException();

            $response = new JsonResponse();
            $data = [
                'error' => [
                    'code' => method_exists($exception, 'getStatusCode')
                        ? $exception->getStatusCode()
                        : $exception->getCode(),
                    'message' => $exception->getMessage(),
                ],
            ];
            $env = $this->container->getParameter('kernel.environment');
            if (in_array($env, ['dev', 'test'])) {
                $data['error']['stack_trace'] = explode(
                    "\n",
                    $exception->getTraceAsString()
                );
            }
            $response->setData($data);

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $event->setResponse($response);
        }
    }
}
