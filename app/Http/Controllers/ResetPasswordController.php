<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller; 

class ResetPasswordController extends Controller
{
    public function __construct()
    {
       $this->middleware('guest');
    }
}
