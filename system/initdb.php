<?php
global $Zdb, $CONF;

try {
    /** @var Zend\Db\Adapter\Adapter */
    $zend_db = new Zend\Db\Adapter\Adapter([
        'driver' => $CONF['dbtype'],
        'hostname' => $CONF['dbhost'],
        'database' => $CONF['dbname'],
        'username' => $CONF['dbuser'],
        'password' => $CONF['dbpasswd'],
    ]);
    $Zdb = new nordgen\DbBatch\DbBatch($zend_db);
} catch (exception $e) {
    if ($CONF['debug']) {
        echo "init: error connecting to db<br>error:" . $e->getMessage();
//        var_dump($e);
//        adodb_backtrace($e->gettrace());
    }
}
