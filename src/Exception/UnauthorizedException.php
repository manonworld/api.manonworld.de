<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unauthorized User', Response::HTTP_UNAUTHORIZED);
    }
}