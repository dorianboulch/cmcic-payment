<?php

namespace Dorianboulch\Cmcicpayment;


class CMCIC_Tpe {


  public $sVersion;	// Version du TPE - TPE Version (Ex : 3.0)
  public $sNumero;	// Numero du TPE - TPE Number (Ex : 1234567)
  public $sCodeSociete;	// Code Societe - Company code (Ex : companyname)
  public $sLangue;	// Langue - Language (Ex : FR, DE, EN, ..)
  public $sUrlOK;		// Url de retour OK - Return URL OK
  public $sUrlKO;		// Url de retour KO - Return URL KO
  public $sUrlPaiement;	// Url du serveur de paiement - Payment Server URL (Ex : https://paiement.creditmutuel.fr/paiement.cgi)

  private $_sCle;		// La clé - The Key


  // ----------------------------------------------------------------------------
  //
  // Constructeur / Constructor
  //
  // ----------------------------------------------------------------------------

  function __construct(Array $parameters, $sLangue = 'FR') {

    // contrôle de l'existence des constantes de paramétrages.
    $aRequiredDatas = array('CMCIC_CLE', 'CMCIC_VERSION', 'CMCIC_TPE', 'CMCIC_CODESOCIETE');
    $this->_checkTpeParams($aRequiredDatas,$parameters);

    $this->sVersion     = $parameters['CMCIC_VERSION'];
    $this->_sCle        = $parameters['CMCIC_CLE'];
    $this->sNumero      = $parameters['CMCIC_TPE'];
    $this->sUrlPaiement = $parameters['CMCIC_SERVEUR'] . $parameters['CMCIC_URLPAIEMENT'];

    $this->sCodeSociete = $parameters['CMCIC_CODESOCIETE'];
    $this->sLangue      = $sLangue;

    $this->sUrlOK = $parameters['CMCIC_URLOK'];
    $this->sUrlKO = $parameters['CMCIC_URLKO'];

  }

  // ----------------------------------------------------------------------------
  //
  // Fonction / Function : getCle
  //
  // Renvoie la clé du TPE / return the TPE Key
  //
  // ----------------------------------------------------------------------------

  public function getCle() {

    return $this->_sCle;
  }

  // ----------------------------------------------------------------------------
  //
  // Fonction / Function : _checkTpeParams
  //
  // Contrôle l'existence des constantes d'initialisation du TPE
  // Check for the initialising constants of the TPE
  //
  // ----------------------------------------------------------------------------

  private function _checkTpeParams($aConstants) {

    for ($i = 0; $i < count($aConstants); $i++)
      if (!array_key_exists($aConstants[$i],$aConstants))
        die ("Erreur paramètre " . $aConstants[$i] . " indéfini");
  }

}