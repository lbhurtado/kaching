<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TransactionApproval;
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
    public function contact_will_be_persisted_using_bearing_method()
    {
        /*** arrange ***/
        $mobile = phone('09187654321', config('kaching.country'))->formatE164();

        /*** assert ***/
        $this->assertNull(Contact::where(compact('mobile'))->first());

        /*** act ***/
        $contact1 = Contact::bearing($mobile);

        /*** assert ***/
        $this->assertNotNull($contact2 = Contact::where(compact('mobile'))->first());
        $this->assertTrue($contact1->is($contact2));
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

    /** @test */
    public function contact_credit_sends_otp()
    {
        /*** arrange ***/
        Notification::fake();

        /*** act ***/
        $this->contact->credit(100);

        /*** assert ***/
        Notification::assertSentTo($this->contact, TransactionApproval::class);
    }
}
