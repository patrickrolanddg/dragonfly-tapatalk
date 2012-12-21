<?php
/**
*
* @copyright (c) 2009 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

defined('IN_MOBIQUO') or exit;

/*
function authorize_user_func($xmlrpc_params)
{
    global $auth, $MyFile, $fh;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    
    $username = $params[0];
    $password = $params[1];
//    fwrite($fh, $username);
//    fwrite($fh, $password);
//    set_var($username, $username, 'string', true);
//    set_var($password, $password, 'string', true);
    header('Set-Cookie: mobiquo_a=0');
    header('Set-Cookie: mobiquo_b=0');
    header('Set-Cookie: mobiquo_c=0');
    $login_result = $auth->login($username, $password);
    
    $login_status = false;
    $status = $login_result['status'];
    fwrite($fh, $status);
    if ($login_result['status'] == LOGIN_SUCCESS) $login_status = true;
    $login_status = true;
    $response = new xmlrpcval(array('authorize_result' => new xmlrpcval($login_status, 'boolean')), 'struct');
    
    return new xmlrpcresp($response);
}
*/

function authorize_user_func($xmlrpc_params = '')
{
    global $db, $prefix;
	
	$params = php_xmlrpc_decode($xmlrpc_params);
	
	header('Set-Cookie: mobiquo_a=0');
    header('Set-Cookie: mobiquo_b=0');
    header('Set-Cookie: mobiquo_c=0');
	
	$username = $params[0];
    $password = MD5($params[1]);
    
    $sql = "SELECT user_id from ".$prefix."_users WHERE username='".$username."' AND user_password='".$password."' AND user_level='1' AND user_active='1' ";
	$result = $db->sql_query($sql);
	$login_status = false;
	
	if($db->sql_numrows($result) != NULL){
		$login_status = true;
	}
	
	$response = new xmlrpcval(array('authorize_result' => new xmlrpcval($login_status, 'boolean')), 'struct');    
    return new xmlrpcresp($response);
	
}
