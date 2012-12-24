<?php
/**
*
* @copyright (c) 2009 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

defined('IN_MOBIQUO') or exit;

function get_subscribed_forum_func()
{
	# UNTESTED function

	if (!is_user()) return get_error(20, 'Please login first');

	global $db;
	$tracked = isset($_SESSION['CPG_SESS']['Forums']['track_forums']) ? $_SESSION['CPG_SESS']['Forums']['track_forums'] : array();
	$tracked = array('1' => '1234', '2' => '2345', '3' => '3456');
	list($forums) $db->sql_ufetchrowset('SELECT * FROM '.FORUMS_TABLE.' WHERE forum_id IN ('.implode(array_keys($tracked), ',').')');
	//$forums = $forums[0];

	$xmlrpc = array();
	foreach($forums as $forum )
	{
		$xmlrpc[] = new xmlrpcval(array(
			'forum_id'      => new xmlrpcval($forum['forum_id']),
			'forum_name'    => new xmlrpcval(html_entity_decode($forum['forum_name']), 'base64'),
			//'icon_url'      => new xmlrpcval($logo_url),
			'is_protected'  => new xmlrpcval(false, 'boolean'),
			'sub_only'      => new xmlrpcval(false, 'boolean'),
		), 'struct');
	}
	$response = new xmlrpcval(array(
		'total_forums_num' => new xmlrpcval(count($forums), 'int'),
		'forums'    => new xmlrpcval($xmlrpc, 'array'),
	), 'struct');

	return new xmlrpcresp($response);
}
