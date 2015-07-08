<?php namespace AndyJessop\HubbubBilling;

use Cartalyst\Stripe\Stripe;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Subscription;
use App\User;
use App\Student;

/**
 * trait BillableTrait
 * 
 * A user trait that provides cashier-like methods for cartalyst's stripe-laravel
 * package, specific to Hubbub. Hubbub has a system where users can have multiple
 * subscriptions, which is not possible with Laravel Cashier. This trait makes
 * this project possible.
 * 
 * @package AndyJessop\HubbubBilling
 */
trait BillableTrait {

    /**
     * STRIPE
     * ======
     */

    public function getStripeKey()
    {
    	return Config::get('services.stripe.secret');
    }

    public function getStripe()
    {
        return (new Stripe($this->getStripeKey()));
    }

    /**
     * ACCOUNT
     * =============
     */

    /**
     * Find out whether or not there is a stripe account for this user
     * User table must have a stripe_id column
     * @return boolean
     */
	public function readyForBilling()
    {
        return ! is_null($this->stripe_id);
    }

    /**
     * Create a new stripe account for this user
     * @param  string $token    A card token string
     * @param  array  $metadata
     * @return array 
     */
    public function createCustomer($token, $metadata = [])
    {
        // Create account with stripe
        $stripe = $this->getStripe();
        $customer = $stripe->customers()->create([
            'source' => $token,
            'metadata' => $metadata
        ]);

        // Add stripe_id to users table
        $this->stripe_id = $customer['id'];
        $this->save();

        return $customer;
    }

    /**
     * Retrieve stripe details for this user
     * @return array           
     */
    public function getCustomer()
    {
        $stripe = $this->getStripe();
        return $stripe->customers()->find($this->stripe_id);
    }

    /**
     * Add a card to account
     * @param string|Array $card - either a token or an array
     */
    public function addCard($card)
    {
        $stripe = $this->getStripe();
        return $stripe->cards()->create($this->stripe_id, $card);
    }

    /**
     * Retrieve card details
     * @param  string $card - stripe card unique identifier
     * @return array
     */
    public function getCard($card)
    {
        $stripe = $this->getStripe();
        return $stripe->cards()->find($this->stripe_id, $card);
    }

    /**
     * Retrieve cards details
     * @return array
     */
    public function getCards()
    {
        $stripe = $this->getStripe();
        return $stripe->cards()->all($this->stripe_id);
    }

    /**
     * Update card details
     * @param  string $card - stripe card unique identifier
     * @param  array $details - details to update
     * @return array
     */
    public function updateCard($card, $details)
    {
        $stripe = $this->getStripe();
        return $stripe->cards()->update($this->stripe_id, $card, $details);
    }

    /**
     * Delete card details
     * @param  string $card - stripe card unique identifier
     * @return array
     */
    public function deleteCard($card)
    {
        $stripe = $this->getStripe();
        return $stripe->cards()->delete($this->stripe_id, $card);
    }

    /**
     * Make a card the default card for the customer
     * @param  string $card - stripe card unique identifier
     * @return array
     */
    public function makeDefaultCard($card)
    {
        $stripe = $this->getStripe();
        return $stripe->customers()->update($this->stripe_id, ['default_source' => $card]);
    }

    /**
     * Get the default card for this customer
     * @return array
     */
    public function getDefaultCard()
    {
        $stripe = $this->getStripe();
        $customer = $stripe->customers()->find($this->stripe_id);
        $card = $customer['default_source'];
        return $this->getCard($card);
    }

    /**
     * Get the last four digits of a given card
     * @param  string $card - stripe identifier
     * @return string
     */
    public function getLastFourDigits($card)
    {
        return $card['last4'];
    }
    
    /**
     * Find an invoice by invoice id.
     *
     * @param  string  $invoice - stripe unique identifier
     * @return array
     */
    public function findInvoice($invoice)
    {
        $stripe = $this->getStripe();
        $invoice = $this->invoices()->find($id);
        if ($invoice && $invoice['customer'] == $this->stripe_id) {
            return $invoice;
        }

        return null;
    }

    /**
     * Get an array of the entity's invoices.
     * @return array
     */
    public function invoices()
    {
        $stripe = $this->getStripe();
        $params = [$this->stripe_id];
        return $this->invoices()->all($params);
    }

    /**
     *  Get the entity's upcoming invoice.
     *
     * @return array
     */
    public function upcomingInvoice()
    {
        $stripe = $this->getStripe();
        $params = [$this->stripe_id];
        return $this->invoices()->upcomingInvoice($params);
    }


    /**
     * SUBSCRIPTIONS
     * =============
     */
    
    /**
     * Get all subscriptions for this customer
     * @return array
     */
    public function getSubscriptions()
    {
        $stripe = $this->getStripe();
        return $stripe->subscriptions()->all($this->stripe_id);
    }

    /**
     * Get subscription
     * @param  string $subscription - stripe unique identifier
     * @return array
     */
    public function getSubscription($subscription)
    {
        $stripe = $this->getStripe();
        return $stripe->subscriptions()->find($this->stripe_id, $subscription);
    }

    /**
     * Update a subscription
     * @param  string $subscription - stripe unique identifier
     * @param  array $params      
     * @return array
     */
    public function updateSubscription($subscription, $params)
    {
        $stripe = $this->getStripe();
        return $stripe->subscriptions()->update($this->stripe_id, $subscription, $params);
    }

    /**
     * Get subscription trial start date
     * @param  string $subscription - stripe unique identifier
     * @return array
     */
    public function getTrialStartDate($subscription)
    {
        $stripe = $this->getStripe();
        $subscription = $stripe->subscriptions()->find($this->stripe_id, $subscription);
        return $subscription['trial_start'];
    }

    /**
     * Get subscription trial end date
     * @param  string $subscription - stripe unique identifier
     * @return array
     */
    public function getTrialEndDate($subscription)
    {
        $stripe = $this->getStripe();
        $subscription = $stripe->subscriptions()->find($this->stripe_id, $subscription);
        return $subscription['trial_end'];
    }

    /**
     * Get subscription end date
     * @param  string $subscription - stripe unique identifier
     * @return array
     */
    public function getSubscriptionEndDate($subscription)
    {
        $stripe = $this->getStripe();
        $subscription = $stripe->subscriptions()->find($this->stripe_id, $subscription);

        if ($subscription['cancel_at_period_end'] == false)
        {
            return null;
        }

        return $subscription['current_period_end'];
    }

    /**
     * Set subscription trial end date
     * @param  string $subscription - stripe unique identifier
     * @return array
     */
    public function setTrialEndDate($subscription, Carbon $date)
    {
        $stripe = $this->getStripe();
        $subscription = $stripe->subscriptions()->find($this->stripe_id, $subscription);
        return $subscription['trial_end'];
    }

    /**
     * Determine if the entity is within their trial period.
     * @param  string $subscription - stripe unique identifier
     * @return bool
     */
    public function isOnTrial($subscription)
    {
        if (! is_null($this->getTrialEndDate($subscription))) {
            return Carbon::today()->lt($this->getTrialEndDate($subscription));
        } else {
            return false;
        }
    }

    public function endTrial($subscription)
    {
        $params = ['trial_end' => 'now'];
        return $this->updateSubscription($params);
    }

    /**
     * Determine if the entity is on grace period after cancellation.
     *
     * @return bool
     */
    public function isOnGracePeriod($subscription)
    {
        if (! is_null($endsAt = $this->getSubscriptionEndDate($subscription))) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        } else {
            return false;
        }
    }
    
    /**
     * Subscribe a user and student to a plan
     * @param  string $planId    - stripe unique identifier for the plan
     * @param  integer $studentId
     * @return boolean
     */
    public function subscribeToPlan($plan, $studentId)
    {
        // Create stripe subscription
        $stripe = $this->getStripe();
        $subscription = $stripe->subscriptions()->create($this->stripe_id, [
            'plan' => $plan
        ]);

        // Add row to Subscriptions model
        $sub = new Subscription;
        $sub->student_id = $studentId;
        $sub->user_id = $this->id;
        $sub->stripe_plan = $plan;
        $sub->stripe_subscription = $subscription['id'];
        $sub->trial_ends_at = $subscription['trial_end'];
        $sub->subscription_ends_at = ($subscription['cancel_at_period_end'] == false) ? $subscription['current_period_end'] : null;
        
        $sub->save();

        return $subscription;
    }
}