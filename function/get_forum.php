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
require_once(BASEDIR.'modules/Forums/nukebb.php');

function get_forum_func()
{
    global $db, $auth, $user, $prefix, $mobiquo_config, $phpbb_home;
	// Get Forum Categories
	$unread = array();
	$sql = 'SELECT * from '.$prefix.'_bbforums ORDER BY cat_id ASC';
	$result = $db->sql_query($sql);

    $forum_rows = array();
    $forum_rows[0] = array('forum_id' => 0, 'parent_id' => -1, 'child' => array());
    while ($row = $db->sql_fetchrow($result))
    {
        $forum_id = $row['forum_id'];
	$row['can_subscribe'] = false;
	$row['is_subscribed'] = false;
	$row['parent_id'] = 0;
        $forum_rows[$forum_id] = $row;
    }
    $db->sql_freeresult($result);

    $forum_rows[0] = array('parent_id' => -1, 'child' => array());
    while(empty($forum_rows[0]['child']) && count($forum_rows) > 1)
    {
        $current_parent_id = 0;
        $leaves_forum = array();
        foreach($forum_rows as $row)
        {
            $row_parent_id = $row['parent_id'];

            if ($row_parent_id != $current_parent_id)
            {
                if(isset($leaves_forum[$row_parent_id]))
                {
                    $leaves_forum[$row_parent_id] = array();
                }
                else
                {
                    if(isset($leaves_forum[$forum_rows[$row_parent_id]['parent_id']]))
                    {
                        $leaves_forum[$forum_rows[$row_parent_id]['parent_id']] = array();
                    }
                    $leaves_forum[$row_parent_id][] = $row['forum_id'];
                }
                $current_parent_id = $row_parent_id;
            }
            else if ($row_parent_id == $current_parent_id)
            {
                if(!empty($leaves_forum[$row_parent_id]))
                {
                    $leaves_forum[$row_parent_id][] = $row['forum_id'];
                }
            }
        }
//        echo "We get here...";
        foreach($leaves_forum as $node_forum_id => $leaves)
        {
            foreach($leaves as $forum_id)
            {
                $logo_url = '';
                if (file_exists(MOBPATH."forum_icons/$forum_id.png"))
                {
                    $logo_url = MOBPATH."forum_icons/$forum_id.png";
                }
                else if (MOBPATH."forum_icons/$forum_id.jpg"))
                {
                    $logo_url = MOBPATH."forum_icons/$forum_id.jpg";
                }
                else if (file_exists(MOBPATH."forum_icons/default.png"))
                {
                    $logo_url = MOBPATH."mobiquo/forum_icons/default.png";
                }
                else if ($forum_rows[$forum_id]['forum_image'])
                {
                    $logo_url = MOBPATH.'forum_icons/'.$forum_rows[$forum_id]['forum_image'];
                }
  		//echo "We get here...";
                //$unread_count = count(get_unread_topics(false, "AND t.forum_id = $forum_id"));
                //$forum_rows[$forum_id]['unread_count'] += $unread_count;
                if ($forum_rows[$forum_id]['unread_count'])
                {
                    $forum_rows[$forum_rows[$forum_id]['parent_id']]['unread_count'] += $forum_rows[$forum_id]['unread_count'];
                }

                $xmlrpc_forum = new xmlrpcval(array(
                    'forum_id'      => new xmlrpcval($forum_rows[$forum_id]['forum_id']),
                    'forum_name'    => new xmlrpcval(html_entity_decode($forum_rows[$forum_id]['forum_name']), 'base64'),
                    'description'   => new xmlrpcval(html_entity_decode($forum_rows[$forum_id]['forum_desc']), 'base64'),
                    'parent_id'     => new xmlrpcval($node_forum_id),
                    'logo_url'      => new xmlrpcval($logo_url),
                    'new_post'      => new xmlrpcval($forum_rows[$forum_id]['unread_count'] ? true : false, 'boolean'),
                    'unread_count'  => new xmlrpcval($forum_rows[$forum_id]['unread_count'], 'int'),
                    'is_protected'  => new xmlrpcval($forum_rows[$forum_id]['forum_password'] ? true : false, 'boolean'),
                    'url'           => new xmlrpcval($forum_rows[$forum_id]['forum_link']),
                    'sub_only'      => new xmlrpcval('true', 'boolean'),

                    'can_subscribe' => new xmlrpcval($forum_rows[$forum_id]['can_subscribe'], 'boolean'),
                    'is_subscribed' => new xmlrpcval($forum_rows[$forum_id]['is_subscribed'], 'boolean'),
                ), 'struct');

                if (isset($forum_rows[$forum_id]['child']))
                {
                    $xmlrpc_forum->addStruct(array('child' => new xmlrpcval($forum_rows[$forum_id]['child'], 'array')));
                }

                $forum_rows[$node_forum_id]['child'][] = $xmlrpc_forum;
                unset($forum_rows[$forum_id]);
            }
        }
    }

    $response = new xmlrpcval($forum_rows[0]['child'], 'array');

    return new xmlrpcresp($response);
} // End of get_forum_func
