<?php

namespace Tests\Feature;

use App\Contact;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function a_list_of_contacts_can_be_fetched_for_the_authenticated_user()
    {
        $user = factory(User::class)->create();
        $anotherUser = factory(User::class)->create();

        $contact = factory(Contact::class)->create(['user_id' => $user->id]);
        $anotherContact = factory(Contact::class)->create(['user_id' => $anotherUser->id]);

        $response = $this->get('/api/contacts?api_token='. $user->api_token);
        $response->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    ['contact_id' => $contact->id]
                ]
            ]);
    }
    
    
    /** @test */
    public function an_unauthenticated_user_should_redirect_to_login()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['api_token' => '']));

        $response->assertRedirect('/login');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function a_contact_can_be_added()
    {
        $this->withoutExceptionHandling();
        $this->post('/api/contacts', array_merge($this->data()));

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
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        
        $response = $this->get('/api/contacts/'. $contact->id . '?api_token=' . $this->user->api_token);

        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'company' => $contact->company,
                'birthday' => $contact->birthday->format('d/m/Y'),
                'last_updated' => $contact->updated_at->diffForHumans(),
            ]
        ]);
    }

    /** @test */
    public function only_the_user_contact_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotherUser = factory(User::class)->create();
        
        $response = $this->get('/api/contacts/'. $contact->id . '?api_token=' . $anotherUser->api_token);

        $response->assertStatus(403);
    }
    

    /** @test */
    public function contact_can_be_patched()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        
        $response = $this->patch('/api/contacts/'. $contact->id, $this->data());

        $contact = $contact->fresh();

        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('johndoe@email.com', $contact->email);
        $this->assertEquals('23-07-1997', $contact->birthday->format('d-m-Y'));
        $this->assertEquals('Foo Company', $contact->company);
    }

    /** @test */
    public function only_owner_of_the_contact_can_be_patched()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotherUser = factory(User::class)->create();
        
        $response = $this->patch('/api/contacts/'. $contact->id, array_merge($this->data(), ['api_token' => $anotherUser->api_token]));

        $response->assertStatus(403);
    }

    /** @test */
    public function contact_can_be_delete()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        
        $response = $this->delete('/api/contacts/'. $contact->id . '?api_token=' . $this->user->api_token);

        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function only_owner_of_the_contact_can_be_delete()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotherUser = factory(User::class)->create();
        
        $response = $this->delete('/api/contacts/'. $contact->id . '?api_token=' . $anotherUser->api_token);

        $response->assertStatus(403);
    }

    protected function data()
    {
        return [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'birthday' => '23-07-1997',
            'company' => 'Foo Company',
            'api_token' => $this->user->api_token
        ];
    }
}