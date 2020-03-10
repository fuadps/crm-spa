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
        $this->post('/api/contacts', [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'birthday' => '23/07/1997',
            'company' => 'Foo Company'
        ]);

        $contact = Contact::first();
        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('johndoe@email.com', $contact->email);
        $this->assertEquals('23/07/1997', $contact->birthday);
        $this->assertEquals('Foo Company', $contact->company);
    }

    /** @test */
    public function a_name_is_required()
    {
        $response = $this->post('/api/contacts', [
            'email' => 'johndoe@email.com',
            'birthday' => '23/07/1997',
            'company' => 'Foo Company'
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function email_is_required()
    {
        $response = $this->post('/api/contacts', [
            'name' => 'John Doe',
            'birthday' => '23/07/1997',
            'company' => 'Foo Company'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());
    }
}