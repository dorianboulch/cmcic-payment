<?php
namespace Dorianboulch\Cmcicpayment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider{

  protected $defer = true;

  public function register() {
    $this->app->bind('PaymentInterface', 'PaymentManager');
  }

  public function provides(){
    return ['PaymentInterface'];
  }
}