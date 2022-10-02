<?php
global $Zdb;
$md5 = 'md5';
//Check in db
//$q = "SELECT u.username, u.real_name, r.name as rolename FROM bgs_user u LEFT JOIN bgs_user_role ur ON (u.id=ur.user_id) LEFT JOIN bgs_role r ON (ur.role_id=r.id) WHERE u.password = '{$md5($_POST['pwd'])}' AND u.username='${_POST['user']}' AND u.deleted=0";
$q = <<<SQL
SELECT u.username, u.real_name, r.name as rolename 
FROM bgs_user u 
    LEFT JOIN bgs_user_role ur ON (u.id=ur.user_id) 
    LEFT JOIN bgs_role r ON (ur.role_id=r.id) 
WHERE u.password = $1 
  AND u.username = $2 
  AND u.deleted=0
SQL;

$params = array(md5($_POST['pwd']), $_POST['user']);

try {
    $rs = [];
    $rs = $Zdb->query($q,$params)->getQueryResult();
} catch (exception $e) {
    echo "Error selecting user roles: " . $e->getMessage();
}	

$loggedin=false;
$roles = array();
$username = "";
$realname = "";

foreach($rs as $row){
	$loggedin=true;
	$username = $row['username'];
	$realname = $row['real_name'];
	$roles[] = $row['rolename']; 
}

if($loggedin){
	$_SESSION["isloggedin"] = array("username"=>$username,"roles"=>$roles,"realname"=>$realname);
}else{
	unset($_SESSION["isloggedin"]);
	
	$errors[] = array("auth_login","Login failed. Check username and password.");			
}
