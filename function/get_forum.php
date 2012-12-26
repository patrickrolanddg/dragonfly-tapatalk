<?php
/*======================================================================*\
|| #################################################################### ||
|| # DragonflyCMS Tapatalk plugin                                     # ||
|| # Written by Jeff Mills (hybiepoo@hotmail.com) and based on        # ||
|| # existing phpbb3 plugin for Tapatalk                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/

if (!defined('CPG_NUKE') || !defined('IN_MOBIQUO')) exit;

function get_forum_func($xmlrpc_params)
{
	$params = php_xmlrpc_decode($xmlrpc_params);
	$desc = isset($params[0]) ? true : false;
	$parent_id = isset($params[1]) ? intval($params[1]) : 0;

	global $db;
	$cats = $db->sql_ufetchrowset('SELECT cat_id, cat_title FROM ' . CATEGORIES_TABLE . ' ORDER BY cat_order', SQL_ASSOC);
	$forums = mobi_forums($parent_id);

	for ($i=0, $c=count($cats); $i<$c; ++$i) {
		$cats[$i]['forum_id']   = $cats[$i]['cat_id']+99999;
		$cats[$i]['forum_name'] = $cats[$i]['cat_title'];
		$cats[$i]['parent_id']  = '-1';
		$cats[$i]['sub_only']   = true;
		$cats[$i]['child']  = array();
		foreach ($forums as &$forum) {
			if ($cats[$i]['cat_id'] != $forum['cat_id']) continue;
			if (!$forum['parent_id']) $forum['parent_id'] = $forum['cat_id']+99999;
			$cats[$i]['child'][] = assocToStruct($forum);
			unset($forum);
		}
		if (empty($cats[$i]['child'])) {
			unset($cats[$i]);
			continue;
		}
		$cats[$i] = assocToStruct($cats[$i], $desc);
	}
	return new xmlrpcresp(new xmlrpcval($cats, 'array'));
}

function assocToStruct(array $data, $desc=false)
{
	$rpc = array(
		'forum_id'   => new xmlrpcval($data['forum_id']),
		'forum_name' => new xmlrpcval(html_entity_decode($data['forum_name']), 'base64'),
		'parent_id'  => new xmlrpcval($data['parent_id']),
		'sub_only'   => new xmlrpcval(!empty($data['sub_only']) ?: false, 'boolean'),
		'can_subscribe' => new xmlrpcval(is_user(), 'boolean'),
		'is_subscribed' => new xmlrpcval(!empty($_SESSION['CPG_SESS']['Forums']['track_forums'][$data['forum_id']]), 'boolean'),
	);
	if (isset($data['forum_desc']) && $desc)
		$rpc['description'] = new xmlrpcval(html_entity_decode($data['forum_desc']), 'base64');

	if (isset($data['is_protected']))
		$rpc['is_protected'] = new xmlrpcval($data['is_protected'], 'boolean');

	if (!empty($data['child']))
	{
		$rpc['child'] = new xmlrpcval($data['child'], 'array');
	}
	else if (!empty($data['subforums']))
	{
		for ($i=0, $c=count($data['subforums']); $i<$c; ++$i) {
			$data['subforums'][$i] = assocToStruct($data['subforums'][$i], $desc);
		}
		$rpc['child'] = new xmlrpcval($data['subforums'], 'array');
	}
	return new xmlrpcval($rpc, 'struct');
}
