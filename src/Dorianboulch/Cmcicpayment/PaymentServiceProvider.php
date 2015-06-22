<?php
namespace Dorianboulch\Cmcicpayment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider{

  protected $defer = true;

  public function register() {
    $this->app->bind('Dorianboulch\Cmcicpayment\PaymentInterface', 'Dorianboulch\Cmcicpayment\PaymentManager');
  }

  public function provides(){
    return ['Dorianboulch\Cmcicpayment\PaymentInterface'];
  }
}