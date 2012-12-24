<?php
/*======================================================================*\
 || #################################################################### ||
 || # DragonflyCMS Tapatalk plugin                                     # ||
 || # Written by Jeff Mills (hybiepoo@hotmail.com) with help from      # ||
 || # existing phpbb3 plugin for Tapatalk                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/

define('IN_MOBIQUO', true);

$module_name = 'Forums';

// Initialise DragonflyCMS
require_once('../includes/cmsinit.inc');

if (!is_active('Forums')) {
	header("{$_SERVER['SERVER_PROTOCOL']} 503 Service Unavailable");
	exit;
}

if (!defined('BASEHREF')) define('BASEHREF', $BASEHREF);
define('MOBIHREF', BASEHREF . basename(dirname($_SERVER['SCRIPT_NAME'])).'/');
define('FORUMHREF', BASEHREF . 'modules/Forums/');
set_include_path(get_include_path() . PATH_SEPARATOR . BASEDIR);

// Initialise CPG-BB (formally phpbb)
include(BASEDIR.'modules/Forums/nukebb.php');

// Initialise tapatalk
include("./include/xmlrpc.inc");
include("./include/xmlrpcs.inc");
require('./config/config.php');
require('./error_code.php');
require('./mobiquo_common.php');
require('./server_define.php');


$mobiquo_config = get_mobiquo_config();

if (!$mobiquo_config['is_open']) {
	header("{$_SERVER['SERVER_PROTOCOL']} 503 Service Unavailable");
	exit;
}

$phpEx = $mobiquo_config['php_extension'];
$xmlrpc_internalencoding = 'UTF-8';
$xmlrpcName = 'DragonflyCMS/'.CPG_NUKE.' (PHP '.PHP_MAJOR_VERSION.'; '.PHP_SAPI.'; '.PHP_OS.') XML-RPC Tapatalk/3';
$xmlrpcVersion = 'v'.$mobiquo_config['sys_version'];

// Get requested function and load file
$request_method_name = get_method_name();
if ($request_method_name && isset($server_param[$request_method_name]))
{
    require('./function/'.$request_method_name.'.php');
}

$rpcServer = new xmlrpc_server($server_param, false);
$rpcServer->allow_system_funcs = false;
$rpcServer->compress_response = 'false';
$rpcServer->response_charset_encoding = 'UTF-8';

$response = $rpcServer->service();
