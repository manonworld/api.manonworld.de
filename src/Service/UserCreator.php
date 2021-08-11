<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Exception\ValidationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserCreator
{

    private UserRepository $repo;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        UserRepository $repo, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator
    ){
        $this->repo         = $repo;
        $this->serializer   = $serializer;
        $this->validator    = $validator;
    }
    
    public function create(array $denorm, string $data)
    {
        $denorm['groups'] = ['write'];

        $user = $this->serializer->deserialize($data, User::class, 'json', $denorm);

        $errors = $this->validator->validate($user);

        if ( count( $errors ) ) {
            throw (new ValidationException)->setViolations($errors);
        }

        try {
            $savedUser = $this->repo->save($user);
        } catch (\Exception $e) {
            throw $e;
        }

        $denorm['groups'] = ['read'];

        return $this->serializer->serialize($savedUser, 'json', $denorm);
    }

}