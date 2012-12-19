\<?php
/**
*
* @copyright (c) 2009 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

defined('IN_MOBIQUO') or exit;
if (!defined('CPG_NUKE')) { exit; }
require_once('modules/Forums/nukebb.php');
$logfile = "log.txt";

function get_unread_topic_func($xmlrpc_params)
{
    global $db, $auth, $user, $userinfo, $prefix, $config, $mobiquo_config, $phpbb_home;
    $params = php_xmlrpc_decode($xmlrpc_params);

    $start_num  = 0;
    $end_num    = 19;
    if (isset($params[0]) && is_int($params[0]))
    {
        $start_num = $params[0];
    }

    // get end index of topic from parameters
    if (isset($params[1]) && is_int($params[1]))
    {
        $end_num = $params[1];
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
    $sql_limit = $end_num - $start_num + 1;

    //$ex_fid_ary = array_unique(array_merge(array_keys($auth->acl_getf('!f_read', true)), array_keys($auth->acl_getf('!f_search', true))));

 if (isset($mobiquo_config['hide_forum_id']))
    {
        $ex_fid_ary = array_unique(array_merge($ex_fid_ary, $mobiquo_config['hide_forum_id']));
    }

    //$not_in_fid = (sizeof($ex_fid_ary)) ? 'WHERE ' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . ';

        $sql = 'SELECT forum_id, forum_name, parent_id, forum_type, forum_order
                    FROM '.$prefix.'_bbforums
		    '.$not_in_fid.'
                    ORDER BY forum_order';
        $result = $db->sql_query($sql);

$db->sql_freeresult($result);
    // find out in which forums the user is allowed to view approved posts
    $sql = 'SELECT t.topic_id, t.forum_id, t.topic_last_post_id, p.post_time AS topic_last_post_time FROM '.$prefix.'_bbtopics t, '.$prefix.'_bbposts p
            WHERE p.topic_id = t.topic_id AND p.post_time > ' . $userinfo['user_lastvisit'] . ' ORDER BY topic_last_post_time DESC LIMIT '.$sql_limit.'';
	$fh = fopen('log.txt', 'w') or die ("Cant open file");
	fwrite($fh, $userinfo['username']);
    $result = $db->sql_query($sql);
//print_r($userinfo);
    $unread_tids = array();
    while ($row = $db->sql_fetchrow($result))
    {
        $topic_id = $row['topic_id'];
//        $forum_id = $row['forum_id'];
//        $topic_tracking = get_complete_topic_tracking($forum_id, $topic_id);
//        if ($topic_tracking[$topic_id] < $row['topic_last_post_time'])
//        {
            $unread_tids[] = $topic_id;
//        }
    }

$db->sql_freeresult($result);

    $topic_list = array();
	$ur_tids = implode(",",$unread_tids);
 if(count($unread_tids)) {
        $sql = 'SELECT f.forum_id,
                       f.forum_name,
                       t.topic_id,
                       t.topic_title,
                       t.topic_replies,
                       t.topic_views,
                       t.topic_poster,
                       t.topic_status,
                       t.topic_type,
                       t.topic_last_post_id,
                       u.user_avatar,
                       u.user_avatar_type,
                       tw.notify_status,
			p.post_time AS topic_last_post_time,
			u.username AS topic_last_poster_name,
			p.poster_id AS topic_last_poster_id
                FROM '.$prefix.'_bbtopics t
		    LEFT JOIN '.$prefix.'_bbposts p ON (p.post_id = t.topic_last_post_id)
                    LEFT JOIN '.$prefix.'_bbforums f ON (t.forum_id = f.forum_id)
                    LEFT JOIN '.$prefix.'_users u ON (p.poster_id = u.user_id)
                    LEFT JOIN '.$prefix.'_bbtopics_watch tw ON (tw.user_id = ' . $userinfo['user_id'] . ' AND t.topic_id = tw.topic_id)
                WHERE t.topic_id IN ('.$ur_tids.')
                ORDER BY topic_last_post_time DESC LIMIT '.$sql_limit.'';
        $result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
        {
            $topic_id = $row['topic_id'];
            $forum_id = $row['forum_id'];

            $short_content = get_short_content($row['topic_last_post_id']);
            $user_avatar_url = get_user_avatar_url($row['user_avatar'], $row['user_avatar_type']);

            //$allow_change_type = ($auth->acl_get('m_', $forum_id) || ($user->data['is_registered'] && $user->data['user_id'] == $row['topic_poster'])) ? true : false;

            $xmlrpc_topic = new xmlrpcval(array(
                'forum_id'          => new xmlrpcval($forum_id),
                'forum_name'        => new xmlrpcval(html_entity_decode($row['forum_name']), 'base64'),
                'topic_id'          => new xmlrpcval($topic_id),
                'topic_title'       => new xmlrpcval(html_entity_decode(strip_tags($row['topic_title'])), 'base64'),
                'reply_number'      => new xmlrpcval($row['topic_replies'], 'int'),
                'new_post'          => new xmlrpcval(true, 'boolean'),
                'view_number'       => new xmlrpcval($row['topic_views'], 'int'),
                'short_content'     => new xmlrpcval($short_content, 'base64'),
                'post_author_id'    => new xmlrpcval($row['topic_last_poster_id']),
                'post_author_name'  => new xmlrpcval(html_entity_decode($row['topic_last_poster_name']), 'base64'),
                'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($row['topic_last_post_time']), 'dateTime.iso8601'),
                'icon_url'          => new xmlrpcval($user_avatar_url),
                'can_delete'        => new xmlrpcval(false, 'boolean'),
                'can_subscribe'     => new xmlrpcval(($config['email_enable'] || $config['jab_enable']) && $config['allow_topic_notify'] && $user->data['is_registered'], 'boolean'),
                'can_bookmark'      => new xmlrpcval($user->data['is_registered'] && $config['allow_bookmarks'], 'boolean'),
                'issubscribed'      => new xmlrpcval(!is_null($row['notify_status']) && $row['notify_status'] !== '' ? true : false, 'boolean'),
                'is_subscribed'     => new xmlrpcval(!is_null($row['notify_status']) && $row['notify_status'] !== '' ? true : false, 'boolean'),
                'isbookmarked'      => new xmlrpcval($row['bookmarked'] ? true : false, 'boolean'),
                'can_close'         => new xmlrpcval(false, 'boolean'),
                'is_closed'         => new xmlrpcval($row['topic_status'] == ITEM_LOCKED, 'boolean'),
                'can_stick'         => new xmlrpcval(false, 'boolean'),
            ), 'struct');

            $topic_list[] = $xmlrpc_topic;
        }

 $db->sql_freeresult($result);
    }

    $response = new xmlrpcval(
        array(
            'total_topic_num' => new xmlrpcval(count($unread_tids), 'int'),
            'topics'           => new xmlrpcval($topic_list, 'array'),
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}
fclose($fh);
