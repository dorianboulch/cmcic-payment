<?php

namespace Dorianboulch\Cmcicpayment;

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

    return $this->generateHtmlForm();
  }

  private function checkPaymentParams(Array $params){
    $aRequiredDatas = array('reference', 'montant', 'texte', 'email');
    for ($i = 0; $i < count($aRequiredDatas); $i++)
      if (!array_key_exists($aRequiredDatas[$i], $params))
        die ("Erreur paramètre " . $aRequiredDatas[$i] . " indéfini");
  }

  private function generateHtmlForm(){
    $html = '
        <form action="'.$this->oTpe->sUrlPaiement.'" method="post" id="PaymentRequest">
            <input type="hidden" name="version"             id="version"        value="'.$this->oTpe->sVersion.'" />
            <input type="hidden" name="TPE"                 id="TPE"            value="'.$this->oTpe->sNumero.'" />
            <input type="hidden" name="date"                id="date"           value="'.$this->sDate.'" />
            <input type="hidden" name="montant"             id="montant"        value="'.$this->sMontant . $this->sDevise.'" />
            <input type="hidden" name="reference"           id="reference"      value="'.$this->sReference.'" />
            <input type="hidden" name="MAC"                 id="MAC"            value="'.$this->sMac.'" />
            <input type="hidden" name="url_retour"          id="url_retour"     value="'.$this->oTpe->sUrlKO.'" />
            <input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="'.$this->oTpe->sUrlOK.'" />
            <input type="hidden" name="url_retour_err"      id="url_retour_err" value="'.$this->oTpe->sUrlKO.'" />
            <input type="hidden" name="lgue"                id="lgue"           value="'.$this->oTpe->sLangue.'" />
            <input type="hidden" name="societe"             id="societe"        value="'.$this->oTpe->sCodeSociete.'" />
            <input type="hidden" name="texte-libre"         id="texte-libre"    value="'.htmlEncode($this->sTexteLibre).'" />
            <input type="hidden" name="mail"                id="mail"           value="'.$this->sEmail.'" />';
      if($this->sNbrEch != ''){
          $html .= '
            <input type="hidden" name="nbrech"              id="nbrech"         value="'.$this->sNbrEch.'" />
            <input type="hidden" name="dateech1"            id="dateech1"       value="'.$this->sDateEcheance1.' />
            <input type="hidden" name="montantech1"         id="montantech1"    value="'.$this->sMontantEcheance1.'" />
            <input type="hidden" name="dateech2"            id="dateech2"       value="'.$this->sDateEcheance2.' />
            <input type="hidden" name="montantech2"         id="montantech2"    value="'.$this->sMontantEcheance2.'" />
            <input type="hidden" name="dateech3"            id="dateech3"       value="'.$this->sDateEcheance3.' />
            <input type="hidden" name="montantech3"         id="montantech3"    value="'.$this->sMontantEcheance3.'" />
            <input type="hidden" name="dateech4"            id="dateech4"       value="'.$this->sDateEcheance4.' />
            <input type="hidden" name="montantech4"         id="montantech4"    value="'.$this->sMontantEcheance4.'" />
            <!-- -->
            <input type="submit" name="bouton"              id="bouton"         value="Connexion / Connection" />
        </form>';
      }
    return $html;
  }
}