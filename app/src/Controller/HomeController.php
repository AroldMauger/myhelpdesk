<?php

namespace App\Controller;

class HomeController extends AbstractController
{


    public function home(): void {
        echo $this->render('index.html.twig');
    }
}