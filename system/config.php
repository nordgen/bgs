<?php
/**
 * Put all configuration that is not in db here
 *
 */
global $CONF;
$CONF = [
    'filesep' => '/', //File separator
    'dbtype' => 'pgsql', //Database settings
    'dbpersist' => true,
    'debug' => false,
    'dbname' => 'bgs',
    'dbhost' => $_SERVER["HEIMDAL_IP"], //Change settings below to Nordgen environment
    'dbuser' => $_SERVER['DB_BGS_USR'] ?: 'postgres',
    'dbpasswd' => $_SERVER['DB_BGS_PWD'],
    'document_root' => '/var/www/bgs',
];
