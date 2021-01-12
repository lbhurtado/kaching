<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected $mobile = '09171234567';

    protected $contact;

    public function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory(['mobile' => $this->mobile])->create();
    }

    /** @test */
    public function contact_can_be_instantiated_using_mobile_number()
    {
        /*** arrange ***/
        $mobile = $this->mobile;

        /*** act ***/
        $contact = Contact::bearing($mobile);

        /*** assert ***/
        $this->assertTrue($this->contact->is($contact));
    }
}
