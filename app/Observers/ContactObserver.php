<?php

namespace App\Observers;

use App\Models\Contact;

class ContactObserver
{
    /**
     * Handle the Contact "creating" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function creating(Contact $contact)
    {
        $phone = phone($contact->getAttribute('mobile'), 'PH')
            ->formatE164();

        $contact->setAttribute('mobile', $phone);
    }

    /**
     * Handle the Contact "created" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function created(Contact $contact)
    {
        $contact->createWallet(config('wallet.wallet.default'));

        foreach (config('wallet.wallet.wallets') as $wallet) {
            $contact->createWallet($wallet);
        }
    }

    /**
     * Handle the Contact "updated" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function updated(Contact $contact)
    {
        //
    }

    /**
     * Handle the Contact "deleted" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function deleted(Contact $contact)
    {
        //
    }

    /**
     * Handle the Contact "restored" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function restored(Contact $contact)
    {
        //
    }

    /**
     * Handle the Contact "force deleted" event.
     *
     * @param Contact $contact
     * @return void
     */
    public function forceDeleted(Contact $contact)
    {
        //
    }
}
