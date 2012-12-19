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

// Set up Debug File
$myFile = "log.txt";
$fh = fopen($myFile, 'w') or die("can't open file"); // REALLY BAD

// Initialise CPG-BB (formally phpbb)
define('IN_PHPBB', true);
global $phpbb_root_path;
$phpbb_root_path = BASEDIR.'modules/Forums/';

include($phpbb_root_path.'common.php');
define('MOBPATH', BASEDIR."modules/$module_name/");
// Initialise tapatalk
include(MOBPATH.'include/xmlrpc.inc');
include(MOBPATH.'include/xmlrpcs.inc');
require(MOBPATH.'config/config.php');
require(MOBPATH.'error_code.php');
require(MOBPATH.'mobiquo_common.php');
require(MOBPATH.'server_define.php');
$mobiquo_config = get_mobiquo_config();
$phpEx = $mobiquo_config['php_extension'];

// Get requested function and load file
$request_method_name = get_method_name();
if ($request_method_name && isset($server_param[$request_method_name]))
{
    require('./function/'.$request_method_name.'.php');
}

ob_get_clean();
$rpcServer = new xmlrpc_server($server_param, false);
$rpcServer->setDebug(1);
$rpcServer->compress_response = 'true';
$rpcServer->response_charset_encoding = 'UTF-8';

$response = $rpcServer->service();
fclose($fh);
?>
