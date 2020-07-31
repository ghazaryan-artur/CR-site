<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\TestEmail;
class someController extends Controller
{
    //for testing mail sending
    public function sendEmail(){

        $data = ['message' => 'This is a test!'];

        \Mail::to('ghazaryan.artur1@gmail.com')->send(new TestEmail($data));
    }

}




