<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;

class UserUpdater
{
    private UserRepository $repo;
    private ValidatorInterface $validator;
    private DataUriNormalizer $normalizer;

    public function __construct(
        UserRepository $repo, 
        ValidatorInterface $validator, 
        DataUriNormalizer $normalizer
    ){
        $this->repo         = $repo;
        $this->validator    = $validator;
        $this->normalizer   = $normalizer;
    }

    public function update(string $data, User $user): User
    {
        $data = json_decode($data);
        
        if ( isset( $data->newEmail ) )
            $user->setEmail( $data->newEmail );

        if ( isset( $data->newPassword ) )
            $user->setPassword( $data->newPassword );

        if ( isset( $data->image ) )
            $user->setImage( $this->normalizer->denormalize( $data->image, \SplFileObject::class ) );

        $errors = $this->validator->validate($user);

        if ( count( $errors ) )
            throw ( new ValidationException )->setViolations( $errors );

        return $this->repo->save( $user );
    }
}