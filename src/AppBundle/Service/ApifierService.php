<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ApifierService
{
    use ContainerAwareTrait;

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Get a nicer errors format.
     */
    public function errors(ConstraintViolationList $errors, $errorMessage = 'One or more fields are invalid.')
    {
        $errorsWithPaths = [];

        foreach ($errors as $error) {
            /** @Ignore */
            $translation = $this->container->get('translator')
                ->trans($error->getMessage());
            $errorsWithPaths[$error->getPropertyPath()] = $translation;
        }

        return [
            'errors' => $errorsWithPaths,
            'error' => [
                'message' => count($errors) === 1
                    ? $errors[0]->getMessage()
                    : $errorMessage,
            ],
        ];
    }

    /**
     * Returns the error text.
     *
     * @param \Exception $e
     */
    public function errorText(\Exception $e)
    {
        $env = $this->container->getParameter('kernel.environment');
        if (
            $env === 'prod' &&
            !is_a($e, 'AppBundle\Exception\UserException')
        ) {
            return $this->container->get('translator')->trans(
                'general.something_went_wrong'
            );
        }

        return $e->getMessage();
    }

    /**
     * Upload the file.
     *
     * @param string $file
     * @param string $prefix
     * @param bool   $includePath
     *
     * @return array
     */
    public function uploadFile($file, $prefix = '', $includePath = false)
    {
        $env = $this->container->getParameter('kernel.environment');
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = str_replace(
            '/app_'.$env.'.php',
            '',
            $request->getBaseUrl()
        );
        $uploadsPath = $this->container->getParameter('uploads_path');
        $uploadsDirectory = $this->container->getParameter('uploads_directory');

        $name = $prefix.md5(uniqid()).'.'.$file->guessExtension();
        $url = $request->getSchemeAndHttpHost().$baseUrl.$uploadsPath.'/'.$name;

        $file->move($uploadsDirectory, $name);

        $output = [
            'name' => $name,
            'url' => $url,
        ];

        if ($includePath) {
            $output['path'] = $uploadsDirectory.'/'.$name;
        }

        return $output;
    }
}
