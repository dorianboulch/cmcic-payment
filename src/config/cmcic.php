<?php

return [

    'CMCIC' => [
    ],

    'TPE' => [
        'CLE'         => '12345678901234567890123456789012345678P0',
        'TPE'         => '0000001',
        'VERSION'     => '3.0',
        'SERVEUR'     => 'https://ssl.paiement.cic-banques.fr/test/',
        'URLPAIEMENT' => 'paiement.cgi',
        'CODESOCIETE' => '0000000',
        'URLOK'       => 'named.route.ok', //laravel route
        'URLKO'       => 'named.route.ko' //laravel route
    ]
];
