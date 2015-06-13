<?php namespace AndyJessop\HubbubBilling;

use Cartalyst\Stripe\Stripe;
use Illuminate\Support\Facades\Config;

/**
 * trait BillableTrait
 * @package AndyJessop\HubbubBilling
 */
trait BillableTrait {

	public function getStripeKey()
	{
		return Config::get('services.stripe.secret');
	}

    public function test()
    {
        return 1;
    }
    
	public function charge($amount, array $options = array())
    {
    	$stripe = new Stripe($this->getStripeKey());

        return (new StripeGateway($this))->charge($amount, $options);
    }

    /* 
		Required Methods
		================

		addCard
		updateCard
		removeCard
		makeDefaultCard

		getInvoices
		findInvoice
		downloadInvoice

		onTrial
		getTrialEndDate
		setTrialEndDate
		endTrial
		onGracePeriod

		isSubscribedTo
		getSubscriptions
		getSubscriptionEndDate
		setSubscriptionEndDate

		readyForBilling
		getStripeId
		getStripeEmail
		getDefaultCard
		getLastFourDigits

    */
}


/*
    protected static $stripeKey;

    public function getBillableName()
    {
        return $this->email;
    }
 
    public function saveBillableInstance()
    {
        $this->save();
    }

    public function charge($amount, array $options = array())
    {
        return (new StripeGateway($this))->charge($amount, $options);
    }
 
    public function subscription($plan = null)
    {
        return new StripeGateway($this, $plan);
  }
    public function invoice()
    {
        return $this->subscription()->invoice();
    }
    
   
    public function findInvoice($id)
    {
        $invoice = $this->subscription()->findInvoice($id);
        if ($invoice && $invoice->customer == $this->getStripeId()) {
            return $invoice;
        }
   }
    public function findInvoiceOrFail($id)
    {
        $invoice = $this->findInvoice($id);
        if (is_null($invoice)) {
            throw new NotFoundHttpException;
        } else {
            return $invoice;
        }
    }
  
    public function invoiceFile($id, array $data)
    {
        return $this->findInvoiceOrFail($id)->file($data);
    }

    public function downloadInvoice($id, array $data)
    {
        return $this->findInvoiceOrFail($id)->download($data);
    }

    public function invoices($parameters = array())
    {
        return $this->subscription()->invoices(false, $parameters);
    }

    public function upcomingInvoice()
    {
        return $this->subscription()->upcomingInvoice();
    }

    public function updateCard($token)
    {
        return $this->subscription()->updateCard($token);
    }

    public function applyCoupon($coupon)
    {
        return $this->subscription()->applyCoupon($coupon);
    }

    public function onTrial()
    {
        if (! is_null($this->getTrialEndDate())) {
            return Carbon::today()->lt($this->getTrialEndDate());
        } else {
            return false;
        }
    }

    public function onGracePeriod()
    {
        if (! is_null($endsAt = $this->getSubscriptionEndDate())) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        } else {
            return false;
        }
    }

    public function subscribed()
    {
        if ($this->requiresCardUpFront()) {
            return $this->stripeIsActive() || $this->onGracePeriod();
        } else {
            return $this->stripeIsActive() || $this->onTrial() || $this->onGracePeriod();
        }
    }

    public function expired()
    {
        return ! $this->subscribed();
    }

    public function cancelled()
    {
        return $this->readyForBilling() && ! $this->stripeIsActive();
    }

    public function everSubscribed()
    {
        return $this->readyForBilling();
    }

    public function onPlan($plan)
    {
        return $this->stripeIsActive() && $this->subscription()->planId() == $plan;
    }

    public function requiresCardUpFront()
    {
        if (isset($this->cardUpFront)) {
            return $this->cardUpFront;
        }
        return true;
    }

    public function readyForBilling()
    {
        return ! is_null($this->getStripeId());
    }

    public function stripeIsActive()
    {
        return $this->stripe_active;
    }

    public function setStripeIsActive($active = true)
    {
        $this->stripe_active = $active;
        return $this;
    }

    public function deactivateStripe()
    {
        $this->setStripeIsActive(false);
        $this->stripe_subscription = null;
        return $this;
    }

    public function hasStripeId()
    {
        return ! is_null($this->stripe_id);
    }

    public function getStripeId()
    {
        return $this->stripe_id;
    }

    public function getStripeIdName()
    {
        return 'stripe_id';
    }

    public function setStripeId($stripe_id)
    {
        $this->stripe_id = $stripe_id;
        return $this;
    }

    public function getStripeSubscription()
    {
        return $this->stripe_subscription;
    }

    public function setStripeSubscription($subscription_id)
    {
        $this->stripe_subscription = $subscription_id;
        return $this;
    }

    public function getStripePlan()
    {
        return $this->stripe_plan;
    }

    public function setStripePlan($plan)
    {
        $this->stripe_plan = $plan;
        return $this;
    }

    public function getLastFourCardDigits()
    {
        return $this->last_four;
    }

    public function setLastFourCardDigits($digits)
    {
        $this->last_four = $digits;
        return $this;
    }

    public function getTrialEndDate()
    {
        return $this->trial_ends_at;
    }

    public function setTrialEndDate($date)
    {
        $this->trial_ends_at = $date;
        return $this;
    }

    public function getSubscriptionEndDate()
    {
        return $this->subscription_ends_at;
    }

    public function setSubscriptionEndDate($date)
    {
        $this->subscription_ends_at = $date;
        return $this;
    }

    public function getCurrency()
    {
        return 'usd';
    }

    public function getCurrencyLocale()
    {
        return 'en_US';
    }

    public function getTaxPercent()
    {
        return 0;
    }

    public function formatCurrency($amount)
    {
        return number_format($amount / 100, 2);
    }

    public function addCurrencySymbol($amount)
    {
        return '$'.$amount;
    }

    public static function getStripeKey()
    {
        return static::$stripeKey ?: Config::get('services.stripe.secret');
    }

    public static function setStripeKey($key)
    {
        static::$stripeKey = $key;
    }

    */