<?php
global $Zdb, $CONF;

//****** INIT DB ************
try {
    /** @var \Laminas\Db\Adapter\Adapter */
    $zend_db = new \Laminas\Db\Adapter\Adapter([
        'driver' => 'Pgsql', // $CONF['dbtype']
        'hostname' => $CONF['dbhost'],
        'database' => $CONF['dbname'],
        'username' => $CONF['dbuser'],
        'password' => $CONF['dbpasswd'],
    ]);
    $Zdb = new nordgen\DbBatch\DbBatch($zend_db);
} catch (exception $e) {
    if ($CONF['debug']) {
        echo "init: error connecting to db<br>error:" . $e->getMessage();
    }
}
