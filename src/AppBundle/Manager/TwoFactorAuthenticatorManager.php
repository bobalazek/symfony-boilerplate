<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * Some methods are taken from https://github.com/scheb/two-factor-bundle/blob/master/Security/TwoFactor/Provider/Google/GoogleAuthenticator.php.
 *
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class TwoFactorAuthenticatorManager
{
    use ContainerAwareTrait;

    /**
     * Validates the code, which was entered by the user.
     *
     * @param User   $user
     * @param string $code
     *
     * @return bool
     */
    public function checkCode(User $user, $code)
    {
        $secret = $user->getTFAAuthenticatorSecret();

        return $this->container->get('google_authenticator')
            ->checkCode(
                $secret,
                $code
            );
    }

    /**
     * Generate the URL of a QR code, which can be scanned by an two factor authenticator app.
     *
     * @param User $user
     *
     * @return string
     */
    public function getUrl(User $user)
    {
        $encoder = 'https://chart.googleapis.com'.
            '/chart?chs=200x200&chld=M|0&cht=qr&chl=';

        return $encoder.urlencode($this->getQRContent($user));
    }

    /**
     * Generate the content for a QR-Code to be scanned by the tow-factor authenticator.
     * Use this method if you don't want to use google charts to display the qr-code.
     *
     * @param User $user
     *
     * @return string
     */
    public function getQRContent(User $user)
    {
        $tfaParameters = $this->container
            ->getParameter('two_factor_authentication');
        $hostname = $tfaParameters['authenticator_hostname'];
        $issuer = $tfaParameters['authenticator_issuer'];
        $secret = $user->getTFAAuthenticatorSecret();

        $userAndHost = rawurlencode($user->getUsername()).
            ($hostname ? '@'.rawurlencode($hostname) : '');

        if ($issuer) {
            $qrContent = sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode($issuer),
                $userAndHost,
                $secret,
                rawurlencode($issuer)
            );
        } else {
            $qrContent = sprintf(
                'otpauth://totp/%s?secret=%s',
                $userAndHost,
                $secret
            );
        }

        return $qrContent;
    }

    /**
     * Generate a new secret for the two-factor authenticator.
     *
     * @return string
     */
    public function generateSecret()
    {
        return $this->container->get('google_authenticator')
            ->generateSecret();
    }
}
