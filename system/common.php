<?php
$maxuploadsize = 10485760;  //bytes; =10MB
$supported_uploads = array("image/jpeg","image/png","image/tiff","image/x-tiff");

//common functions
function isLoggedin(){
	if(!isset($_SESSION["isloggedin"])){
		return false;
	}
	return true;
}

function hasAllRoles($rolesArray){
	if(!isset($_SESSION["isloggedin"])){
		return false;
	}
	
	foreach($rolesArray as $rolename){
		if(!in_array($rolename,$_SESSION["isloggedin"]["roles"])){
			return false;
		}
	}
	return true;
}


function hasAnyRole($rolesArray){
	if(!isset($_SESSION["isloggedin"])){
		return false;
	}
	
	foreach($rolesArray as $rolename){
		if(in_array($rolename,$_SESSION["isloggedin"]["roles"])){
			return true;
		}
	}
	return false;
}
