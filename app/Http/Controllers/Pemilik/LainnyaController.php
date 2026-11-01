<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;

class LainnyaController extends Controller
{
    public function index()
    {
        return view('pemilik.lainnya');
    }
}