<?php
/*======================================================================*\
 || #################################################################### ||
 || # DragonflyCMS Tapatalk plugin                                     # ||
 || # Written by Jeff Mills (hybiepoo@hotmail.com) with help from      # ||
 || # existing phpbb3 plugin for Tapatalk                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/

defined('IN_MOBIQUO') or exit;
// require_once(BASEDIR.'modules/Your_Account/userinfo.php');
function login_func($xmlrpc_params)
{
	global $prefix, $user_prefix, $db, $userinfo, $MAIN_CFG, $board_config;
	$params = php_xmlrpc_decode($xmlrpc_params);
//	$user->setup('ucp');

    $username = ($params[0]);
    $password = MD5($params[1]);
	$userinfo['username'] = $username;
print_r($userinfo);
//    set_var($username, $username, 'string', true);
//    set_var($password, $password, 'string', true);
//    header('Set-Cookie: mobiquo_a=0');
//    header('Set-Cookie: mobiquo_b=0');
//    header('Set-Cookie: mobiquo_c=0');
    $usergroup_id = array();
	$sql = "SELECT user_id from ".$prefix."_users WHERE username='".$username."' AND user_password='".$password."' AND user_level!='0' AND user_active='1' ";
        $result = $db->sql_query($sql);
        $login_status = false;


    if($db->sql_numrows($result) != NULL){
        $login_status = true;
	$error_msg = '';
	while($row = $db->sql_fetchrow($result)) {
        $user_id = $row['user_id'];
	$userinfo['user_id'] = $user_id;
	}
        $sql = "SELECT *
                FROM ".$prefix."_users
                WHERE user_id = $user_id";
        $result = $db->sql_query($sql);
        $user_info = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

	$usergroup_id[] = new xmlrpcval($user_info['user_group_list_cp']);

    } else {
        $login_status = false;
        $error_msg = $sql;
        $user_id = '';
    }

    $response = new xmlrpcval(array(
        'result'        => new xmlrpcval($login_status, 'boolean'),
        'result_text'   => new xmlrpcval($error_msg, 'base64'),
        'user_id'       => new xmlrpcval($user_id, 'string'),
        'can_pm'        => new xmlrpcval($board_config['allow_privmsg'] ? true : false, 'boolean'),
        'can_send_pm'   => new xmlrpcval($board_config['allow_privmsg'] ? true : false, 'boolean'),
        'usergroup_id'  => new xmlrpcval($usergroup_id, 'array'),
    ), 'struct');

    return new xmlrpcresp($response);
}

