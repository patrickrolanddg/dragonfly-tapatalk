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
require_once('modules/Forums/nukebb.php');

function get_forum_func()
{
	global $db, $auth, $user, $prefix, $config, $mobiquo_config, $BASEHREF;
	// Get Forum Categories
	if (!cache_load_array('category_rows', $module_name)) {
		$category_rows = $db->sql_ufetchrowset('SELECT c.cat_id as forum_id, c.cat_title as forum_name, c.cat_order, sub_only as 1 FROM ' . CATEGORIES_TABLE . ' c ORDER BY c.cat_order', SQL_ASSOC);
		cache_save_array('category_rows', $module_name);
	}

	require_once(BASEDIR.'includes/phpBB/functions_display.php');
	$forums = display_forums(0, false);

	$data = array();
	for ($i=0,$c=count($forums); $i<$c; ++$i) {
		$forums[$i]['can_subscribe'] = false;
		$forums[$i]['is_subscribed'] = false;
		//if (!isset($cats[$forums[$i]['parent_id']])) $cats[$forums[$i]['parent_id']] = array();
		//$cats[$forums[$i]['parent_id']][] = $forums[$i];
		$xmlrpc_forum = new xmlrpcval(
			array(
			'forum_id'      => new xmlrpcval($forums[$i]['forum_id']),
			'forum_name'    => new xmlrpcval(html_entity_decode($forums[$i]['forum_name']), 'base64'),
			'description'   => new xmlrpcval(html_entity_decode($forums[$i]['forum_desc']), 'base64'),
			'parent_id'     => new xmlrpcval($forums[$i]['parent_id']),
			'logo_url'      => new xmlrpcval($forums[$i]['folder_image']),
			'new_post'      => new xmlrpcval('No new posts' != $forums[$i]['alt_image'], 'boolean'),
			'unread_count'  => new xmlrpcval('No new posts' != $forums[$i]['alt_image'] ? 1 : 0, 'int'),
			'is_protected'  => new xmlrpcval(false, 'boolean'),
			'url'           => new xmlrpcval($forums[$i]['forum_link']),
			'sub_only'      => new xmlrpcval(false, 'boolean'),
			//'can_subscribe' => new xmlrpcval($forums[$i]['can_subscribe'], 'boolean'),
			//'is_subscribed' => new xmlrpcval($forums[$i]['is_subscribed'], 'boolean'),
			),
			'struct'
		);
		if (!empty($forum[$i]['subforums'])) {
			$xmlrpc_forum->addStruct(array('child' => new xmlrpcval($forum[$i]['subforums'], 'array')));
		}
		$forums[$i] = $xmlrpc_forum;
	}

	return new xmlrpcresp(new xmlrpcval($forums, 'array'));
}
