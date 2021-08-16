<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Security\CredentialsFactory;
use App\Repository\UserRepository;
use App\Entity\User;

class TokenAuthenticator extends AbstractAuthenticator
{

    private UserRepository $repo;
    private CredentialsFactory $credsFactory;

    public function __construct(UserRepository $repo, CredentialsFactory $credsFactory)
    {
        $this->repo         = $repo;
        $this->credsFactory = $credsFactory;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');

        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $content    = json_decode($request->getContent());
        $user       = $this->getUserByToken($apiToken);

        if ( ! $user ) {
            throw new CustomUserMessageAuthenticationException('Invalid API Token');
        }

        if ( ! isset( $content->email ) || ! isset( $content->password ) ) {
            $email = $user->getEmail();
            
            return $this->credsFactory->getCredentials('custom', $user, $email, $apiToken);
        } else {
            $email      = $content->email;
            $password   = $content->password;

            return $this->credsFactory->getCredentials('password', $user, $email, $password);
        }
    }

    private function getUserByToken(string $apiToken): ?User
    {
        return $this->repo->findOneBy([
            'apiToken'  => $apiToken
        ]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {

        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
