<?php

namespace App\Controller;

use Twig\Environment;

class HomeController extends AbstractController
{


    public function test(): void {
        echo $this->render('index.html.twig');
    }
}