<?php
/**
*
* @copyright (c) 2009 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

defined('IN_MOBIQUO') or exit;
if (!defined('CPG_NUKE')) { exit; }
require_once('../modules/Forums/nukebb.php');

function get_topic_func($xmlrpc_params)
{
	global $db, $auth, $prefix, $userinfo, $config, $BASEHREF;

	$params = php_xmlrpc_decode($xmlrpc_params);

	$start_num  = 0;
	$end_num    = 19;
	$topic_type = '';

	if (!isset($params[0]))     // forum id undefine
	{
		return get_error(1);
	}
	else if ($params[0] === 0)  // forum id equal 0
	{
		return get_error(3);
	}

	// get forum id from parameters
	$forum_id = intval($params[0]);

	// get start index of topic from parameters
	if (isset($params[1]) && is_int($params[1]))
	{
		$start_num = $params[1];
	}

	// get end index of topic from parameters
	if (isset($params[2]) && is_int($params[2]))
	{
		$end_num = $params[2];
	}

	// check if topic index is out of range
	if ($start_num > $end_num)
	{
		return get_error(5);
	}

	// return at most 50 topics
	if ($end_num - $start_num >= 50)
	{
		$end_num = $start_num + 49;
	}

	// check if need sticky/announce topic only
	if (isset($params[3]))
	{
		// check if need sticky topic only
		if ($params[3] == 'TOP')
		{
			$topic_type = POST_STICKY;
			$start_num  = 0;
			$end_num    = 19;
		}
		// check if need announce topic only
		else if ($params[3] == 'ANN')
		{
			$topic_type = POST_ANNOUNCE;
			$start_num  = 0;
			$end_num    = 19;
		}
	}

	$sort_days = 0;
	$sort_key  = 't';
	$sort_dir  = 'd';

	//------- Grab appropriate forum data --------
	$sql = 'SELECT f.* FROM '.$prefix."_bbforums f WHERE f.forum_id = $forum_id";
	$result = $db->sql_query($sql);
	$forum_data = $db->sql_fetchrow($result, SQL_ASSOC);
	$db->sql_freeresult($result);

	// Forum does not exist
	if (!$forum_data)
	{
		return get_error(3);
	}

	// Can not get topics from link forum
	if ($forum_data['forum_type'] == FORUM_LINK)
	{
		return get_error(4);
	}


	// Topic ordering options
	$sort_by_sql = array(
		'a' => 't.topic_first_poster_name',
		't' => 't.topic_time',   // default one
		'r' => 't.topic_replies',
		's' => 't.topic_title',
		'v' => 't.topic_views');

	// Limit topics to certain time frame, obtain correct topic count
	// global announcements must not be counted, normal announcements have to
	// be counted, as forum_topics(_real) includes them

	$sql_approved = $sql_shadow_out = '';
	// Get all shadow topics in this forum
	$sql = 'SELECT t.topic_moved_id, t.topic_id
		FROM '.$prefix.'_bbtopics t
		WHERE t.forum_id = ' . $forum_id . '
		AND t.topic_type IN (' . POST_NORMAL . ', ' . POST_STICKY . ', ' . POST_ANNOUNCE . ')' .
		$sql_approved;
	$result = $db->sql_query($sql);
	
	//Get the topic count for this forum
	$topcs_number = $db->sql_numrows($result);

	$shadow_topic_list = array();
	while ($row = $db->sql_fetchrow($result, SQL_ASSOC))
	{
		$shadow_topic_list[$row['topic_moved_id']] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	// Grab all topic data
	$topic_list = array();

	$sql_limit = $end_num - $start_num + 1;  // num of topics needs to be return, default is 20, at most 50
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
	//$sql_shadow_out = empty($shadow_topic_list) ? '' : 'AND ' . $db->sql_in_set('t.topic_moved_id', $shadow_topic_list, true);

	$unread_sticky_num = $unread_announce_count = 0;
	if (!empty($topic_type)) // get top 20 announce/sticky topics only if need
	{
		$sql = "SELECT t.*, tw.notify_status
		FROM {$prefix}_bbtopics t
		LEFT JOIN {$prefix}_bbtopics_watch tw ON (tw.user_id = {$userinfo['user_id']} AND t.topic_id = tw.topic_id)
		WHERE t.forum_id IN ({$forum_id}, 0)
		AND t.topic_type IN ({$topic_type})
		{$sql_shadow_out} {$sql_approved}
		ORDER BY {$sql_sort_order}
		LIMIT $sql_limit OFFSET $start_num";
		$result = $db->sql_query($sql);
	}
	else    // get normal topics from $start_num to $end_num
	{
		// get total number of unread sticky topics number
		$sql = 'SELECT t.topic_id, t.topic_time
			FROM '.$prefix.'_bbtopics t
			WHERE t.forum_id = ' . $forum_id.'
			AND t.topic_type = ' . POST_STICKY . ' ' .
			$sql_shadow_out . ' ' .
			$sql_approved;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$unread_sticky_num++;
		}
		$db->sql_freeresult($result);

		// get total number of unread announce topics number
			$sql = 'SELECT t.topic_id, t.topic_time
			FROM '.$prefix.'_bbtopics t
			WHERE t.forum_id IN (' . $forum_id . ', 0)
			AND t.topic_type IN (' . POST_ANNOUNCE . ') ' .
			$sql_shadow_out . ' ' .
			$sql_approved;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$unread_announce_count++;
		}
		$db->sql_freeresult($result);

		// get total number of normal topics
		$sql = 'SELECT t.topic_id AS num_topics
			FROM '.$prefix.'_bbtopics t
			WHERE t.forum_id = ' . $forum_id.'
			AND t.topic_type = ' . POST_NORMAL . ' ' .
			$sql_shadow_out . ' ' .
			$sql_approved;
		$result = $db->sql_query($sql);
		$topics_count = (int) $db->sql_numrows($result);
		$db->sql_freeresult($result);

		// If the user is trying to reach late pages, start searching from the end
		$store_reverse = false;

		if ($start_num > $topics_count / 2)
		{
			$store_reverse = true;
			if ($start_num + $sql_limit > $topics_count)
			{
				$sql_limit = min($sql_limit, max(1, $topics_count - $start_num));
			}

			// Select the sort order
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
			$start_num = max(0, $topics_count - $sql_limit - $start_num);
		}

		$sql = 'SELECT t.*, u.user_avatar, u.user_avatar_type, tw.notify_status
			FROM '.$prefix.'_bbtopics t
			LEFT JOIN '.$prefix.'_users u ON (t.topic_poster = u.user_id)
			LEFT JOIN '. TOPICS_WATCH_TABLE .' tw ON (tw.user_id = ' . $userinfo['user_id'] . ' AND t.topic_id = tw.topic_id)
			WHERE t.forum_id = ' . $forum_id.'
			AND t.topic_type = ' . POST_NORMAL . ' ' .
			$sql_shadow_out . ' ' .
			$sql_approved . '
			ORDER BY ' . $sql_sort_order;
		$result = $db->sql_query($sql);
	}

	$rowset = array();
	while ($row = $db->sql_fetchrow($result, SQL_ASSOC))
	{
		$rowset[] = $row;
	}
	$db->sql_freeresult($result);

	$topic_list = array();
	foreach($rowset as $row)
	{
		$topic_posterid = $row['topic_poster'];
		$topic_first_post_id = $row['topic_first_post_id'];
		$result = $db->sql_query('SELECT username  from '.$prefix.'_users WHERE user_id = '. $topic_posterid);
		while ($userrow = $db->sql_fetchrow($result, SQL_ASSOC))
		{
			$topic_first_poster_name = $userrow['username'];
		}
		$db->sql_freeresult($result);

		//$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];
		$short_content = '';
		$user_avatar_url = '';
		$topic_tracking = '';
		$short_content = get_short_content($topic_first_post_id);
		$user_avatar_url = get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']);
		//$topic_tracking = get_complete_topic_tracking($forum_id, $row['topic_id']);
		$new_post = $topic_tracking[$row['topic_id']] < $row['topic_time'] ? true : false;

		//$allow_change_type = ($auth->acl_get('m_', $forum_id) || (is_user() && $userinfo['user_id'] == $row['topic_poster'])) ? true : false;

		$xmlrpc_topic = new xmlrpcval(array(
			'forum_id'          => new xmlrpcval($forum_id),
			'topic_id'          => new xmlrpcval($row['topic_id']),
			'topic_title'       => new xmlrpcval(html_entity_decode(strip_tags($row['topic_title']), ENT_QUOTES, 'UTF-8'), 'base64'),
			'topic_author_id'   => new xmlrpcval($row['topic_poster']),
			'topic_author_name' => new xmlrpcval(html_entity_decode($topic_first_poster_name), 'base64'),
			'last_reply_time'   => new xmlrpcval(mobiquo_iso8601_encode($row['topic_time']),'dateTime.iso8601'),
			'reply_number'      => new xmlrpcval(0, 'int'), // FIXME
			'view_number'       => new xmlrpcval(0, 'int'), // FIXME
			'short_content'     => new xmlrpcval($short_content, 'base64'),
			'new_post'          => new xmlrpcval($new_post, 'boolean'),
			'icon_url'          => new xmlrpcval($user_avatar_url),
			//'can_delete'        => new xmlrpcval(false, 'boolean'), // FIXME
			//'can_subscribe'     => new xmlrpcval(($config['email_enable'] || $config['jab_enable']) && $config['allow_topic_notify'] && is_user(), 'boolean'),
			//'can_bookmark'      => new xmlrpcval(is_user() && $config['allow_bookmarks'], 'boolean'),
			//'issubscribed'      => new xmlrpcval(!is_null($row['notify_status']) && $row['notify_status'] !== '' ? true : false, 'boolean'),
			//'is_subscribed'     => new xmlrpcval(!is_null($row['notify_status']) && $row['notify_status'] !== '' ? true : false, 'boolean'),
			//'isbookmarked'      => new xmlrpcval($row['bookmarked'] ? true : false, 'boolean'),
			//'can_close'         => new xmlrpcval(false, 'boolean'), //FIXME
			//'is_closed'         => new xmlrpcval($row['topic_status'] == ITEM_LOCKED, 'boolean'),
			//'can_stick'         => new xmlrpcval(true, 'boolean'), // FIXME
			//'to_normal'         => new xmlrpcval(true, 'boolean'), // FIXME
			//'can_move'          => new xmlrpcval(true, 'boolean'), //FIXME

			//'attachment'        => new xmlrpcval('0', 'string'), //FIXME
		), 'struct');

		$topic_list[] = $xmlrpc_topic;
		unset($xmlrpc_topic);
	}

	if ($store_reverse)
	{
		$topic_list = array_reverse($topic_list);
	}

	if (!empty($topic_type))
	{
		$topic_num = count($topic_list);
	}
	else
	{
		$topic_num = $topics_count;
	}

	//$allowed = $auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && @ini_get('file_uploads') != '0' && strtolower(@ini_get('file_uploads')) != 'off';

	$response = new xmlrpcval(array(
		'total_topic_num' => new xmlrpcval($topic_num, 'int'),
		'unread_sticky_count'   => new xmlrpcval($unread_sticky_num, 'int'),
		'unread_announce_count' => new xmlrpcval($unread_announce_count, 'int'),
		'forum_id'        => new xmlrpcval($forum_id, 'string'),
		'forum_name'      => new xmlrpcval(html_entity_decode($forum_data['forum_name']), 'base64'),
		//'can_post'        => new xmlrpcval(is_user(), 'boolean'), //FIXME
		//'can_upload'      => new xmlrpcval(false, 'boolean'),
		'topics'          => new xmlrpcval($topic_list, 'array'),
		), 'struct');

	return new xmlrpcresp($response);
}
