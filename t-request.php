<?php

/**
 * Name: Stripe Cancellation Script for ClickFunnels
 * Version: 1.1
 * Author: Sorin Marta
 * Author URI: https://sorinmarta.com
 * Owner: MemberFix
 * Owner URI: https://memberfix.rocks
 */

 class StripeCancellation{
    
    // Required Parameters
    private $apiKey = 'ADD HERE THE STRIPE API KEY';
    private $subDefaultName = 'ADD HERE THE STRIPE PRODUCT NAME';

    // Parameters that will later be set by the script
    private $stripe;
    private $customerEmail;
    private $customerID;
    private $activeSub;
    private $activeSubID;

    // The constructor that manages all the other methods
    public function __construct(){
        $this->paramValidate();
        $this->init();
        $this->retrieveCustomer();
        $this->retrieveSubscription();
        $this->subscriptionValidate();
        $this->cancel();

        echo 'Subscription Canceled';
    }

    // Require the Stripe SDK and set the API Key
    private function init(){
        require_once('stripe-php/init.php');
        $this->stripe = \Stripe\Stripe::setApiKey($this->apiKey);
    }

    // Transform an object to an array
    private function toArray($object){
        return json_decode(json_encode($object), true);
    }

    // Validate the parameters
    private function paramValidate(){
        //Headers Data
        $headers = getallheaders();

        // Check if the request has the Zapier header
        if (!isset($headers['Source'])) {
            die('Unauthorised1');
        }
   
        // Check if the header has the required value
        if ($headers['Source'] != 'MemberFix-ClickFunnels') {
            die('Unauthorised2');
        }
   
        // Check if the request is a POST request
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            die('Unauthorised | REQ');
        }
        
        // Check if there is a User_Email set as a POST parameter
        if (isset($_POST['User_Email'])) {
            $this->customerEmail = $_POST['User_Email'];
        }else {
            die('Email address not set');
        }
    }

    // Retrieve the customer data
    private function retrieveCustomer(){
        $rawCurrentUser = \Stripe\Customer::all(["email" => $this->customerEmail,'limit' => 1]);
        $currentUser = $this->toArray($rawCurrentUser);
        $this->customerID = $currentUser['data'][0]['id'];
    }

    // Retrieve the subscription data
    private function retrieveSubscription(){
        // Subscription Data
        $listSub = \Stripe\Subscription::all(['customer'=>$this->customerID]);
        $subArray = $this->toArray($listSub);

        // Subscription Parameters
        $this->activeSub = $subArray['data'][0]['items']['data'][0]['plan']['nickname'];
        $this->activeSubID = $subArray['data'][0]['id'];

        var_dump($this->activeSub);
        var_dump($this->subDefaultName);
    }

    // Validate the subscription name
    private function subscriptionValidate(){
        if ($this->activeSub != $this->subDefaultName) {
            die('The active subscription is not the required one');
          }
    }

    // Cancel the subscription
    private function cancel(){
        // Cancel the subscription
        $sub = \Stripe\Subscription::retrieve($this->activeSubID);
        $sub->cancel();
    }
 }

 new StripeCancellation();