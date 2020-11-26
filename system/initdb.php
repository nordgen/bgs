<?php
global $Zdb, $CONF;

//****** INIT DB ************
//For fastest database performance
//$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
//$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
//$ADODB_COUNTRECS = false;
//$dsn=$CONF['dbtype']."://".$CONF['dbuser'].":".$CONF['dbpasswd']."@".$CONF['dbhost']."/".$CONF['dbname'];
//if($CONF['dbpersist']||$CONF['debug']){
//	$dsn.="?";
//	if($CONF['dbpersist']){
//		$dsn.="persist";
//		if($CONF['debug'])$dsn.="&debug";
//	}else{
//		$dsn.="debug";
//	}
//}
//try {
//	$Gdb = ADONewConnection($dsn);
//} catch (exception $e){
//	if($CONF['debug']){
//		echo "init: error connecting to db<br>";
////		var_dump($e);
////		adodb_backtrace($e->gettrace());
//	}
//}
try {
    /** @var Zend\Db\Adapter\Adapter */
    $zend_db = new Zend\Db\Adapter\Adapter([
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
//        var_dump($e);
//        adodb_backtrace($e->gettrace());
    }
}
