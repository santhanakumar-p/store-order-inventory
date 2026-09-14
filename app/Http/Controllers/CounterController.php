<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CounterController extends Controller
{
    public function index(): View
    {
        return view('counter');
    }
}
