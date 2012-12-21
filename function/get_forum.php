<?php
/*======================================================================*\
|| #################################################################### ||
|| # DragonflyCMS Tapatalk plugin                                     # ||
|| # Written by Jeff Mills (hybiepoo@hotmail.com) and based on        # ||
|| # existing phpbb3 plugin for Tapatalk                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/

defined('IN_MOBIQUO') or exit;
if (!defined('CPG_NUKE')) { exit; }

function get_forum_func($xmlrpc_params)
{
	$params = php_xmlrpc_decode($xmlrpc_params);
	$return_description = isset($params[0]) ? true : false;
	$parent_id = isset($params[1]) ? intval($params[1]) : 0;

	setup_style();
	require_once(BASEDIR.'includes/phpBB/functions_display.php');
	$forums = display_forums();

	foreach ($forums as &$forum)
	{
		$forum = dataToStruct($forum, $return_description);
	}
	return new xmlrpcresp(new xmlrpcval($forums, 'array'));
}

function dataToStruct(array $data, $return_description=false)
{
	global $BASEHREF;
	$rpc = array(
		'forum_id'      => new xmlrpcval($data['forum_id']),
		'forum_name'    => new xmlrpcval(html_entity_decode($data['forum_name']), 'base64'),
		'parent_id'     => new xmlrpcval($data['parent_id']),
		'sub_only'      => new xmlrpcval(!empty($data['sub_only']) ?: false, 'boolean')
	);
	if (!empty($data['folder_image'])) {
		$rpc['logo_url'] = new xmlrpcval($BASEHREF . $data['folder_image']);
	}
	if (!empty($data['forum_desc']) && $return_description) {
		$rpc['description'] = new xmlrpcval(html_entity_decode($data['forum_desc']), 'base64');
	}
	if (!empty($data['forum_link'])) {
		$rpc['url'] = new xmlrpcval($data['forum_link']);
	}
	# should supports multilevel subforums but $forums only contains first level subs only
	if (!empty($data['subforums']))
	{
		foreach($data['subforums'] as $key => $val) {
			$data['subforums'][$key] = dataToStruct($val);
		}
		$rpc['child'] = new xmlrpcval($data['subforums'], 'array');
	}
	return new xmlrpcval($rpc, 'struct');
}
