<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
/**
 * Customer Controller 
 */
class CustomerController extends Controller
{

    public function viewDashboard(){
        return view('customer_dashboard.home');
    }
    
}
