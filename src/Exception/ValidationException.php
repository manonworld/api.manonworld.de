<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ValidationException extends \Exception
{

    private $violations;

    public function __construct()
    {
        parent::__construct('Validation Errors', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function setViolations($violations): self
    {
        $this->violations = $violations;

        return $this;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}