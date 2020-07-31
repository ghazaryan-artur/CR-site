<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    function __construct() {
        $data = array();
        $params = array(
            'admin' => true,
            'admin_login' => true
        );

        if(isset($_SESSION['user'])) {
            header('Location: /admin/dashboard');
        } else {
            $this->view->render('admin/AdminIndex', $data, $params);
        }
    }
}
