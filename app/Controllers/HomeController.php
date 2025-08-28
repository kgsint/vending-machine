<?php

namespace App\Controllers;

use Core\View;

class HomeController
{
    public function index()
    {
        View::make("home", ["foo" => "bar"]);
    }
}
