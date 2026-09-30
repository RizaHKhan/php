<?php

namespace App\Controller;

use App\Attribute\Route;

class LandingController
{
    #[Route('/', name: 'landing', methods: ['GET'])]
    public function index(): string
    {
        return 'Landing page';
    }
}
