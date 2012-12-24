<?php
/**
*
*
*/

# 0 POST_NORMAL
# 1 POST_STICKY
# 2 POST_ANNOUNCE
if (!defined('CPG_NUKE') || !defined('IN_MOBIQUO')) exit;

function get_topic_func($xmlrpc_params)
{
	$params = php_xmlrpc_decode($xmlrpc_params);

	$forum_id = isset($params[0]) ? intval($params[0]) : 0;
	if (!$forum_id) return get_error(3);

	$start_num  = isset($params[1]) && is_int($params[1]) ? $params[1] : 0;
	$end_num    = isset($params[2]) && is_int($params[2]) ? $params[2] : 19;
	$topic_type = !isset($params[3]) ? false : $params[3] == 'TOP' ? '1' : ($params[3] == 'ANN' ? '2' : false);
	$topicQString = $topic_type ?: '0,1,2';

	# check if topic index is out of range
	if ($start_num > $end_num) return get_error(5);
	# return at most 50 topics
	if ($end_num - $start_num >= 50) $end_num = $start_num + 49;

	global $db, $prefix;

	$forum = mobi_forums($forum_id);
	$forum = !empty($forum[0]) ? $forum[0] : 0;
	if (!$forum) return get_error(3);
	if (isset($forum['subforums'])) unset($forum['subforums']);
	if (FORUM_LINK == $forum_data['forum_type']) return get_error(4);

	$sql_limit = $end_num - $start_num + 1;  // num of topics needs to be return, default is 20, at most 50

	$result = $db->sql_query("SELECT t.*, u.username, u.user_avatar, u.user_avatar_type, u.user_allowavatar
		FROM {$prefix}_bbtopics t
		LEFT JOIN {$prefix}_users u
		ON t.topic_poster=u.user_id
		WHERE t.forum_id = $forum_id
		AND t.topic_type IN ($topicQString)
		ORDER BY t.topic_id DESC
		LIMIT $sql_limit OFFSET $start_num");

	$topic_list = array();
	while ($row = $db->sql_fetchrow($result, SQL_ASSOC))
	{
		$short_content = get_short_content($row['topic_first_post_id']);
		$user_avatar_url = $row['user_allowavatar'] ? get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']) : '';

		$rpc = array(
			'forum_id'          => new xmlrpcval($forum_id),
			'topic_id'          => new xmlrpcval($row['topic_moved_id']?:$row['topic_id']),
			'topic_title'       => new xmlrpcval(html_entity_decode(strip_tags($row['topic_title']), ENT_QUOTES, 'UTF-8'), 'base64'),
			'topic_author_id'   => new xmlrpcval($row['topic_poster']),
			'topic_author_name' => new xmlrpcval(html_entity_decode($row['username']), 'base64'),
			'is_subscribed'     => new xmlrpcval(!empty($_SESSION['CPG_SESS']['Forums']['track_topics'][$row['topic_id']]), 'boolean'),
			'can_subscribe'     => new xmlrpcval(is_user(), 'boolean'),
			'is_closed'         => new xmlrpcval(TOPIC_LOCKED == $row['topic_status'], 'boolean'),
			'last_reply_time'   => new xmlrpcval(mobiquo_iso8601_encode($row['topic_time']), 'dateTime.iso8601'),
			'reply_number'      => new xmlrpcval($row['topic_replies'], 'int'),
			'new_post'          => new xmlrpcval(false, 'boolean'), // FIXME
			'view_number'       => new xmlrpcval($row['topic_views'], 'int'),
			'short_content'     => new xmlrpcval(get_short_content($row['topic_first_post_id']), 'base64')
		);
		if ($user_avatar_url) $rpc['icon_url'] = new xmlrpcval($user_avatar_url);

		$topic_list[] = new xmlrpcval($rpc, 'struct');
	}
	$db->sql_freeresult($result);

	$unread_sticky_num = $unread_announce_count = 0;
	$response = new xmlrpcval(array(
		'total_topic_num' => new xmlrpcval($forum['forum_topics'], 'int'),
		'forum_id'        => new xmlrpcval($forum['forum_id'], 'string'),
		'forum_name'      => new xmlrpcval(html_entity_decode($forum['forum_name']), 'base64'),
		'can_post'        => new xmlrpcval(is_user(), 'boolean'), // needs to check forum auths
		'unread_sticky_count'   => new xmlrpcval($unread_sticky_num, 'int'),
		'unread_announce_count' => new xmlrpcval($unread_announce_count, 'int'),
		//'prefixes'        => new xmlrpcval(array(), 'array'),
		//'prefix_id'        => new xmlrpcval('string'),
		//'prefix_display_name'        => new xmlrpcval('', 'base64'),
		'topics'          => new xmlrpcval($topic_list, 'array'),
		), 'struct');

	return new xmlrpcresp($response);
}
