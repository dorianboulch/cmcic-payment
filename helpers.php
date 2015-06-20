<?php
// ----------------------------------------------------------------------------
// function getMethode
//
// IN:
// OUT: Données soumises par GET ou POST / Data sent by GET or POST
// description: Renvoie le tableau des données / Send back the data array
// ----------------------------------------------------------------------------

function getMethode()
{
if ($_SERVER["REQUEST_METHOD"] == "GET")
return $_GET;

if ($_SERVER["REQUEST_METHOD"] == "POST")
return $_POST;

die ('Invalid REQUEST_METHOD (not GET, not POST).');
}

// ----------------------------------------------------------------------------
// function HtmlEncode
//
// IN:  chaine a encoder / String to encode
// OUT: Chaine encodée / Encoded string
//
// Description: Encode special characters under HTML format
//                           ********************
//              Encodage des caractères spéciaux au format HTML
// ----------------------------------------------------------------------------
function HtmlEncode ($data)
{
$SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
$encoded_data = "";
$result = "";
for ($i=0; $i<strlen($data); $i++)
{
if (strchr($SAFE_OUT_CHARS, $data{$i})) {
$result .= $data{$i};
}
else if (($var = bin2hex(substr($data,$i,1))) <= "7F"){
$result .= "&#x" . $var . ";";
}
else
$result .= $data{$i};

}
return $result;
}