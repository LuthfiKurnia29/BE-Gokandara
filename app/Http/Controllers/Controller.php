<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function __construct() {
        ini_set('post_max_size', '124M');
        ini_set('upload_max_filesize', '124M');
    }
}
