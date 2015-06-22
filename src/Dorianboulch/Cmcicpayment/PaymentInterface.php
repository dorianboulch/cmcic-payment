<?php

namespace Dorianboulch\Cmcicpayment;


interface PaymentInterface {

  public function create(Array $parameters);
  public function openForm();
  public function getInputs();
  public function closeForm();
}