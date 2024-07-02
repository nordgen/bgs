<?php
/**
 * Put all configuration that is not in db here
 *
 */
global $CONF;
$CONF = [
    'filesep' => '/', //File separator
    'dbtype' => $_SERVER['DB_BGS_DBTYPE'], //Database settings
    'dbpersist' => true,
    'debug' => false,
    'dbname' => $_SERVER['DB_BGS_DBNAME'],
    'dbhost' => $_SERVER["DB_BGS_DBHOST"],
    'dbuser' => $_SERVER['DB_BGS_USR'],
    'dbpasswd' => $_SERVER['DB_BGS_PWD'],
    'document_root' => '/var/www/bgs',
    'info_sys_url' => $_SERVER['INFO_SYS_URL'],
    'info_sys_accession_page' => $_SERVER['INFO_SYS_ACCESSION_PAGE'],
    'info_sys_title' => $_SERVER['INFO_SYS_TITLE'],
    'reference_ngb_numbers' => $_SERVER['REFERENCE_NGB_NUMBERS'] === 'true',
];
