<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Add this if you have a User model

class DashboardController extends Controller
{
    public function index()
    {
        // Get Blog statistics
        return view('admin.dashboard');
    }
}