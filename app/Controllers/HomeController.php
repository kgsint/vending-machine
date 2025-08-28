<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        require_once VIEW_PATH . "home.view.php";
    }
}
