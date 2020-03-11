<?php

namespace Tests\Feature;

use App\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function a_contact_can_be_added()
    {
        $this->withoutExceptionHandling();
        $this->post('/api/contacts', $this->data());

        $contact = Contact::first();
        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('johndoe@email.com', $contact->email);
        $this->assertEquals('23/07/1997', $contact->birthday);
        $this->assertEquals('Foo Company', $contact->company);
    }

    /** @test */
    public function fields_are_required()
    {
        collect(['name', 'email', 'birthday', 'company'])
            ->each(function($field) {
                $response = $this->post('/api/contacts', array_merge($this->data(), [$field => '']));
        
                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
            });
    }

    protected function data() 
    {
        return [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'birthday' => '23/07/1997',
            'company' => 'Foo Company'
        ];
    }
}