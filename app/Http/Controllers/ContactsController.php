<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function store()
    {
        $validated = request()->validate([
            'name' => 'required',
            'email' => 'required',
            'birthday' => '',
            'company' => '',
        ]);

        Contact::create($validated);
    }
}