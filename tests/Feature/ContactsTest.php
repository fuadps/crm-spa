<?php

namespace Tests\Feature;

use App\Contact;
use Carbon\Carbon;
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
        $this->assertEquals('23-07-1997', $contact->birthday->format('d-m-Y'));
        $this->assertEquals('Foo Company', $contact->company);
    }

    /** @test */
    public function fields_are_required()
    {
        collect(['name', 'email', 'birthday', 'company'])
            ->each(function ($field) {
                $response = $this->post('/api/contacts', array_merge($this->data(), [$field => '']));

                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
            });
    }

    /** @test */
    public function email_must_be_valid_format()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['email' => 'Not an email']));

        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function birthday_are_properly_stored()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/api/contacts', array_merge($this->data(),['birthday' => '17 May 2020']));

        $this->assertCount(1, Contact::all());
        $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);
        $this->assertEquals('17-05-2020', Contact::first()->birthday->format('d-m-Y'));
    }

    /** @test */
    public function contact_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create();
        
        $response = $this->get('/api/contacts/'. $contact->id );

        $response->assertJson([
            'name' => $contact->name,
            'email' => $contact->email,
            'birthday' => $contact->birthday,
            'company' => $contact->company,
        ]);
    }

    /** @test */
    public function contact_can_be_patched()
    {
        $contact = factory(Contact::class)->create();
        
        $response = $this->patch('/api/contacts/'. $contact->id, $this->data());

        $contact = $contact->fresh();

        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('johndoe@email.com', $contact->email);
        $this->assertEquals('23-07-1997', $contact->birthday->format('d-m-Y'));
        $this->assertEquals('Foo Company', $contact->company);
    }

    protected function data()
    {
        return [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'birthday' => '23-07-1997',
            'company' => 'Foo Company'
        ];
    }
}