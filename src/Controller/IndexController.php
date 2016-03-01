<?php
declare(strict_types = 1);

namespace AppBundle\Controller;

use \PommProject\Foundation\Pomm;
use \Symfony\Component\HttpFoundation\JsonResponse;

class IndexController
{
    private $pomm;

    public function __construct(Pomm $pomm)
    {
        $this->pomm = $pomm;
    }

    public function indexAction(): JsonResponse
    {
        return new JsonResponse();
    }
}
