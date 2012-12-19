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
$fh = fopen($myFile, 'w') or die("can't open file");

// Initialise DragonflyCMS core, security, session, and user config
define('XMLFEED', 1);
require_once('../includes/cmsinit.inc');
require_once('../includes/classes/security.php');
require_once('../includes/classes/session.php');
require_once('../includes/classes/cpg_member.php');
$CPG_SESS = array();
$SESS = new cpg_session();
$CLASS['member'] = new cpg_member();
$userinfo = $_SESSION['CPG_USER'];

// Initialise CPG-BB (formally phpbb)
define('IN_PHPBB', true);
global $phpbb_root_path;
$phpbb_root_path = '../modules/Forums/';
$webrootpath = '../';
set_include_path(get_include_path() . PATH_SEPARATOR . $webrootpath);
include($phpbb_root_path.'common.php');

// Initialise tapatalk
include("./include/xmlrpc.inc");
include("./include/xmlrpcs.inc");
require('./config/config.php');
require('./error_code.php');
require('./mobiquo_common.php');
require('./server_define.php');
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
