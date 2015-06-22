<?php

  namespace Dorianboulch\Cmcicpayment;

use Illuminate\Support\Facades\Facade;

class PaymentFacade extends Facade {

  protected static function getFacadeAccessor(){ return 'Dorianboulch\Cmcicpayment\PaymentInterface'; }

}