<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function index()
    {
        return request()->user()->contacts;
    }

    public function store()
    {
        Contact::create($this->validatedData());
    }

    public function show(Contact $contact) 
    {
        return $contact;
    }

    public function update(Contact $contact) 
    {
        $contact->update($this->validatedData());
    }

    public function destroy(Contact $contact) 
    {
        $contact->delete();
    }

    public function validatedData()
    {
        return request()->validate([
            'name' => 'required',
            'email' => 'required|email',
            'birthday' => 'required',
            'company' => 'required',
        ]);
    }
}