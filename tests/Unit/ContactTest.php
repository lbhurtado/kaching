<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Contact;

class ContactTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

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

    /** @test */
    public function contact_has_zero_balance_at_the_onset()
    {
        /*** assert ***/
        $this->assertNotNull($this->contact->balance);
        $this->assertTrue($this->contact->balance == 0);
    }

    /** @test */
    public function contact_can_increase_balance()
    {
        /*** arrange ***/
        $amount = rand(100, 1000);

        /*** act ***/
        $this->contact->deposit($amount);

        /*** assert ***/
        $this->assertTrue($this->contact->balance == $amount);
    }

    /** @test */
    public function contact_can_decrease_balance()
    {
        /*** arrange ***/
        $this->contact->deposit($initial_amount = 1000);
        $amount = rand(100, 1000);

        /*** act ***/
        $this->contact->withdraw($amount);

        /*** assert ***/
        $this->assertTrue($this->contact->balance == $initial_amount - $amount);
    }

    /** @test */
    public function contact_balance_is_never_negative()
    {
        /*** arrange ***/
        $amount = rand(-1000, -100);;

        /*** assert ***/
        $this->expectException(\Bavix\Wallet\Exceptions\AmountInvalid::class);

        /*** act ***/
        $this->contact->deposit($amount);
    }
}
