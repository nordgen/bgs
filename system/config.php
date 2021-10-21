<?php
/**
 *  * Put all configuration that is not in db here
 *   *
 *    */
global $CONF;
$CONF = [
    'filesep' => '/', //File separator
    'dbtype' => 'pgsql', //Database settings
    'dbpersist' => true,
    'debug' => false,
    'dbname' => $_SERVER['DB_BGS_DBNAME'],
    'dbhost' => $_SERVER["DB_BGS_DBHOST"],
    'dbuser' => $_SERVER['DB_BGS_USR'],
    'dbpasswd' => $_SERVER['DB_BGS_PWD'],
    'document_root' => '/var/www/bgs',
];

