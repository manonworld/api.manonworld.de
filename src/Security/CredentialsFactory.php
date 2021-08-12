<?php declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use App\Repository\UserRepository;
use App\Entity\User;

class CredentialsFactory
{

    private UserRepository $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * 
     * Returns an instance of the passport authentication
     * 
     * @param string $method custom|password
     * @param User $user
     * @param string $email
     * @param string $credentials Represents Password or API Token
     */
    public function getCredentials(string $method, User $user, string $email, string $credentials): Passport
    {
        switch($method):

            case 'password':

                $credentials = new PasswordCredentials($credentials);

                $badge = new UserBadge($email, function (  ) use ( $email ) {
                    return $this->repo->findOneBy(['email' => $email]);
                });

                break;
            
            case 'custom':

                $credentials = new CustomCredentials(
                    function () use ( $credentials, $user ) {
                        return $user->getApiToken() === $credentials;
                    },
                    $credentials
                );

                $badge = new UserBadge($email);

                break;

            default:

                throw new CustomUserMessageAuthenticationException('Invalid Credentials');

                break;

        endswitch;

        return new Passport( $badge, $credentials );
    }

}