<?php

namespace App\Controller;

use App\Logger\LoggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommonController extends AbstractController
{

    public function __construct(
        protected LoggerService $log
    ) {}

    public function log(...$args)
    {
        $this->log->log(...$args);
    }

}
