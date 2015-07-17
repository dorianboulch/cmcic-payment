<?php
namespace Dorianboulch\Cmcicpayment;

require_once('CMCIC_Tpe.php');
require_once('CMCIC_Hmac.php');

class PaymentManager implements PaymentInterface {

  private $oTpe;
  private $oHmac;

  private $cmcicCtlHmac = "V1.04.sha1.php--[CtlHmac%s%s]-%s";
  private $cmcicCtlHmacStr = "CtlHmac%s%s";
  private $cmcicCgi2Receipt = "version=2\ncdr=%s";
  private $cmcicCgi2MacOk = "0";
  private $cmcicCgi2MacNotOk = "1\n";
  private $cmcicCgi2Fields = "%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*";
  private $cmcicCgi1Fields = "%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s";
  private $cmcicUrlPaiement = "paiement.cgi";

  private $sReference;
  private $sMontant;
  private $sDevise;
  private $sTexteLibre;
  private $sDate;
  private $sLangue;
  private $sEmail;

  private $sNbrEch;
  private $sDateEcheance1;
  private $sMontantEcheance1;
  private $sDateEcheance2;
  private $sMontantEcheance2;
  private $sDateEcheance3;
  private $sMontantEcheance3;
  private $sDateEcheance4;
  private $sMontantEcheance4;

  private $sOptions;

  private $sMac;

  private $orderData;

  public function create(Array $param) {
    $configCmcic = app()['config']['cmcic']['CMCIC'];
    $configTpe   = app()['config']['cmcic']['TPE'];

    $this->oTpe  = new CMCIC_Tpe($configTpe);
    $this->oHmac = new CMCIC_Hmac($this->oTpe);

    $this->checkPaymentParams($param);

    $this->sReference  = $param['reference'];
    $this->sMontant    = $param['montant'];
    $this->sDevise     = isset($param['devise']) ? $param['devise'] : 'EUR';
    $this->sTexteLibre = $param['texte'];
    $this->sDate       = isset($param['date']) ? $param['date'] : date("d/m/Y:H:i:s");
    $this->sLangue     = isset($param['langue']) ? $param['langue'] : 'FR';
    $this->sEmail      = $param['email'];

    $this->sNbrEch           = isset($param['nbrEch']) ? $param['nbrEch'] : '';
    $this->sDateEcheance1    = isset($param['dateEch1']) ? $param['dateEch1'] : '';
    $this->sMontantEcheance1 = isset($param['montantEch1']) ? $param['montantEch1'] : '';
    $this->sDateEcheance2    = isset($param['dateEch2']) ? $param['dateEch2'] : '';
    $this->sMontantEcheance2 = isset($param['montantEch2']) ? $param['montantEch2'] : '';
    $this->sDateEcheance3    = isset($param['dateEch3']) ? $param['dateEch3'] : '';
    $this->sMontantEcheance3 = isset($param['montantEch3']) ? $param['montantEch3'] : '';
    $this->sDateEcheance4    = isset($param['dateEch4']) ? $param['dateEch4'] : '';
    $this->sMontantEcheance4 = isset($param['montantEch4']) ? $param['montantEch4'] : '';

    $this->sOptions = isset($param['options']) ? $param['options'] : '';

    $CtlHmac = sprintf($this->cmcicCtlHmac, $this->oTpe->sVersion, $this->oTpe->sNumero, $this->oHmac->computeHmac(sprintf($this->cmcicCtlHmacStr, $this->oTpe->sVersion, $this->oTpe->sNumero)));

    // Data to certify
    $php1Fields = sprintf(
        $this->cmcicCgi1Fields,
        $this->oTpe->sNumero,
        $this->sDate,
        $this->sMontant,
        $this->sDevise,
        $this->sReference,
        $this->sTexteLibre,
        $this->oTpe->sVersion,
        $this->oTpe->sLangue,
        $this->oTpe->sCodeSociete,
        $this->sEmail,
        $this->sNbrEch,
        $this->sDateEcheance1,
        $this->sMontantEcheance1,
        $this->sDateEcheance2,
        $this->sMontantEcheance2,
        $this->sDateEcheance3,
        $this->sMontantEcheance3,
        $this->sDateEcheance4,
        $this->sMontantEcheance4,
        $this->sOptions
    );

    // MAC computation
    $this->sMac = $this->oHmac->computeHmac($php1Fields);

  }

  public function openForm(){
    return '<form action="'.$this->oTpe->sUrlPaiement.'" method="POST" id="PaymentRequest">';
  }

  public function getInputs(){
    $inputs = '
        <form action="'.$this->oTpe->sUrlPaiement.'" method="post" id="PaymentRequest">
            <input type="hidden" name="version"             id="version"        value="'.$this->oTpe->sVersion.'" />
            <input type="hidden" name="TPE"                 id="TPE"            value="'.$this->oTpe->sNumero.'" />
            <input type="hidden" name="date"                id="date"           value="'.$this->sDate.'" />
            <input type="hidden" name="montant"             id="montant"        value="'.$this->sMontant . $this->sDevise.'" />
            <input type="hidden" name="reference"           id="reference"      value="'.$this->sReference.'" />
            <input type="hidden" name="MAC"                 id="MAC"            value="'.$this->sMac.'" />
            <input type="hidden" name="url_retour"          id="url_retour"     value="'.route($this->oTpe->sUrlKO).'" />
            <input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="'.route($this->oTpe->sUrlOK).'" />
            <input type="hidden" name="url_retour_err"      id="url_retour_err" value="'.route($this->oTpe->sUrlKO).'" />
            <input type="hidden" name="lgue"                id="lgue"           value="'.$this->oTpe->sLangue.'" />
            <input type="hidden" name="societe"             id="societe"        value="'.$this->oTpe->sCodeSociete.'" />
            <input type="hidden" name="texte-libre"         id="texte-libre"    value="'.htmlEncode($this->sTexteLibre).'" />
            <input type="hidden" name="mail"                id="mail"           value="'.$this->sEmail.'" />';
    if($this->sNbrEch != ''){
      $inputs .= '
            <input type="hidden" name="nbrech"              id="nbrech"         value="'.$this->sNbrEch.'" />
            <input type="hidden" name="dateech1"            id="dateech1"       value="'.$this->sDateEcheance1.' />
            <input type="hidden" name="montantech1"         id="montantech1"    value="'.$this->sMontantEcheance1.'" />
            <input type="hidden" name="dateech2"            id="dateech2"       value="'.$this->sDateEcheance2.' />
            <input type="hidden" name="montantech2"         id="montantech2"    value="'.$this->sMontantEcheance2.'" />
            <input type="hidden" name="dateech3"            id="dateech3"       value="'.$this->sDateEcheance3.' />
            <input type="hidden" name="montantech3"         id="montantech3"    value="'.$this->sMontantEcheance3.'" />
            <input type="hidden" name="dateech4"            id="dateech4"       value="'.$this->sDateEcheance4.' />
            <input type="hidden" name="montantech4"         id="montantech4"    value="'.$this->sMontantEcheance4.'" />';
    }
    return $inputs;
  }

  public function closeForm(){
    return '</form>';
  }

  private function checkPaymentParams(Array $params){
    $aRequiredDatas = array('reference', 'montant', 'texte', 'email');
    for ($i = 0; $i < count($aRequiredDatas); $i++)
      if (!array_key_exists($aRequiredDatas[$i], $params))
        die ("Erreur paramètre " . $aRequiredDatas[$i] . " indéfini");
  }

  public function processServerReturn() {
    $this->orderData = getMethode();

    $configTpe   = app()['config']['cmcic']['TPE'];

    $this->oTpe  = new CMCIC_Tpe($configTpe);
    $this->oHmac = new CMCIC_Hmac($this->oTpe);

    if(
        isset($this->orderData['MAC']) &&
        isset($this->orderData['date']) &&
        isset($this->orderData['montant']) &&
        isset($this->orderData['reference']) &&
        isset($this->orderData['texte-libre']) &&
        isset($this->orderData['code-retour']) &&
        isset($this->orderData['cvx']) &&
        isset($this->orderData['vld']) &&
        isset($this->orderData['brand']) &&
        isset($this->orderData['status3ds']) &&
        isset($this->orderData['numauto']) &&
        isset($this->orderData['originecb']) &&
        isset($this->orderData['bincb']) &&
        isset($this->orderData['hpancb']) &&
        isset($this->orderData['ipclient']) &&
        isset($this->orderData['originetr']) &&
        isset($this->orderData['veres']) &&
        isset($this->orderData['pares'])
      ){
	  if(!isset($this->orderData['motifrefus'])){
		  $this->orderData['motifrefus'] = '';
	  }

      $cgi2_fields = sprintf($this->cmcicCgi2Fields, $this->oTpe->sNumero,
          $this->orderData['date'],
          $this->orderData['montant'],
          $this->orderData['reference'],
          $this->orderData['texte-libre'],
          $this->oTpe->sVersion,
          $this->orderData['code-retour'],
          $this->orderData['cvx'],
          $this->orderData['vld'],
          $this->orderData['brand'],
          $this->orderData['status3ds'],
          $this->orderData['numauto'],
          $this->orderData['motifrefus'],
          $this->orderData['originecb'],
          $this->orderData['bincb'],
          $this->orderData['hpancb'],
          $this->orderData['ipclient'],
          $this->orderData['originetr'],
          $this->orderData['veres'],
          $this->orderData['pares']
      );

      if ($this->oHmac->computeHmac($cgi2_fields) == strtolower($this->orderData['MAC'])){
        $dataValid = true;
        $receipt   = $this->cmcicCgi2MacOk;
      }else{
        $dataValid = false;
        $receipt   = $this->cmcicCgi2MacNotOk;
      }
      $toPrint = sprintf ($this->cmcicCgi2Receipt, $receipt);
    }else{
      $dataValid = false;
      $toPrint   = 'Error data not received';
    }

    return [
      'dataValid' => $dataValid,
      'toPrint' => $toPrint
    ];
  }

  public function getOrderDatas() {
    return $this->orderData;
  }
}