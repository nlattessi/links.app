<?php

namespace App\Http\Response;

use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;

class FractalResponse
{
    private $manager;
    private $serializer;

    public function __construct(Manager $manager, SerializerAbstract $serializer)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;

        $this->manager->setSerializer($serializer);
    }
}
