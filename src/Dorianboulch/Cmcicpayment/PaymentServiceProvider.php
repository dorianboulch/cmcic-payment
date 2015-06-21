<?php
namespace Dorianboulch\Cmcicpayment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider{

  protected $defer = true;

  public function register() {
    $this->app->bind('PaymentInterface', 'Dorianboulch\Cmcicpayment\PaymentManager');
  }

  public function provides(){
    return ['PaymentInterface'];
  }
}