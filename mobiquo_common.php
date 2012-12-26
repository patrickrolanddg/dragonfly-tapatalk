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

function log_it($log_data, $is_begin = false)
{
    global $mobiquo_config;

    if(!$mobiquo_config['keep_log'] || !$log_data)
    {
        return;
    }

    $log_file = './log/'.date('Ymd_H').'.log';

    if ($is_begin)
    {
        global $user;
        $method_name = $log_data;
        $log_data = "\nSTART ======================================== $method_name\n";
        $log_data .= "TIME: ".date('Y-m-d H:i:s')."\n";
        $log_data .= "USER ID: ".$user->data['user_id']."\n";
        $log_data .= "USER NAME: ".$user->data['username']."\n";
        $log_data .= "PARAMETER:\n";
    }

    file_put_contents($log_file, print_r($log_data, true), FILE_APPEND);
}


function get_method_name()
{
    $ver = phpversion();
    if ($ver[0] >= 5) {
        $data = file_get_contents('php://input');
    } else {
        $data = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
    }
    $parsers = php_xmlrpc_decode_xml($data);
    return trim($parsers->methodname);
}


function get_error($error_code = 99, $error_message = '')
{
    global $mobiquo_error_code;

    if(isset($mobiquo_error_code[$error_code]) && $error_message == '')
    {
        $error_message = $mobiquo_error_code[$error_code];
    }

    return new xmlrpcresp('', 18, $error_message); // for test purpose
    //return new xmlrpcresp('', $error_code, $mobiquo_error_code[$error_code]);
}


function get_short_content($post_id, $length = 200)
{
    global $db;
    list($txt) = $db->sql_ufetchrow('SELECT post_text FROM '.POSTS_TEXT_TABLE.' WHERE post_id='.$post_id);

    $txt = preg_replace('/\[url.*?\].*?\[\/url.*?\]/', '[url]', $txt);
    $txt = preg_replace('/\[img.*?\].*?\[\/img.*?\]/', '[img]', $txt);
    $txt = preg_replace('/[\n\r\t]+/', ' ', $txt);
    strip_bbcode($txt);
    $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
    $txt = function_exists('mb_substr') ? mb_substr($txt, 0, $length) : substr($txt, 0, $length);

    return $txt;
}


function post_html_clean($str)
{
    global $phpbb_root_path, $phpbb_home, $mobiquo_config;
    $search = array(
        "/<a .*?href=\"(.*?)\".*?>(.*?)<\/a>/si",
        "/<img .*?src=\"(.*?)\".*?\/?>/sei",
        "/<br\s*\/?>|<\/cite>/si",
        "/<object .*?data=\"(http:\/\/www\.youtube\.com\/.*?)\" .*?>.*?<\/object>/si",
        "/<object .*?data=\"(http:\/\/video\.google\.com\/.*?)\" .*?>.*?<\/object>/si",
    );

    $replace = array(
        '[url=$1]$2[/url]',
        "'[img]'.url_encode('$1').'[/img]'",
        "\n",
        '[url=$1]YouTube Video[/url]',
        '[url=$1]Google Video[/url]'
    );

    $str = preg_replace('/\n|\r/si', '', $str);
    // remove smile
    $str = preg_replace('/<img [^>]*?src=\"[^"]*?images\/smilies\/[^"]*?\"[^>]*?alt=\"([^"]*?)\"[^>]*?\/?>/', '$1', $str);
    $str = preg_replace('/<img [^>]*?alt=\"([^"]*?)\"[^>]*?src=\"[^"]*?images\/smilies\/[^"]*?\"[^>]*?\/?>/', '$1', $str);

    $str = preg_replace('/<null.*?\/>/', '', $str);
    $str = preg_replace($search, $replace, $str);
    $str = strip_tags($str);
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

    // change relative path to absolute URL
    $str = preg_replace('/\[img\]\.\.\/(.*?)\[\/img\]/si', "[img]$phpbb_home/$1[/img]", $str);
    $str = preg_replace('#\[img\]'.addslashes($phpbb_root_path).'(.*?)\[/img\]#si', "[img]$phpbb_home$1[/img]", $str);
    // remove link on img
    $str = preg_replace('/\[url=[^\]]*?\]\s*(\[img\].*?\[\/img\])\s*\[\/url\]/si', '$1', $str);

    // cut quote content to 100 charactors
    if ($mobiquo_config['shorten_quote'])
    {
        $str = cut_quote($str, 100);
    }

    return parse_bbcode($str);
}

function parse_bbcode($str)
{
    $search = array(
        '#\[(b)\](.*?)\[/b\]#si',
        '#\[(u)\](.*?)\[/u\]#si',
        '#\[(i)\](.*?)\[/i\]#si',
        '#\[color=(\#[\da-fA-F]{3}|\#[\da-fA-F]{6}|[A-Za-z]{1,20}|rgb\(\d{1,3}, ?\d{1,3}, ?\d{1,3}\))\](.*?)\[/color\]#si',
    );

    if ($GLOBALS['return_html']) {
        $replace = array(
            '<$1>$2</$1>',
            '<$1>$2</$1>',
            '<$1>$2</$1>',
            '<font color="$1">$2</font>',
        );
        $str = str_replace("\n", '<br />', $str);
    } else {
        $replace = '$2';
    }

    return preg_replace($search, $replace, $str);
}

function parse_quote($str)
{
    $blocks = preg_split('/(<blockquote.*?>|<\/blockquote>)/i', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $quote_level = 0;
    $message = '';

    foreach($blocks as $block)
    {
        if (preg_match('/<blockquote.*?>/i', $block)) {
            if ($quote_level == 0) $message .= '[quote]';
            $quote_level++;
        } else if (preg_match('/<\/blockquote>/i', $block)) {
            if ($quote_level <= 1) $message .= '[/quote]';
            if ($quote_level >= 1) {
                $quote_level--;
                $message .= "\n";
            }
        } else {
            if ($quote_level <= 1) $message .= $block;
        }
    }

    return $message;
}

function url_encode($url)
{
    $url = rawurlencode($url);

    $from = array('/%3A/', '/%2F/', '/%3F/', '/%2C/', '/%3D/', '/%26/', '/%25/', '/%23/', '/%2B/', '/%3B/', '/%5C/');
    $to   = array(':',     '/',     '?',     ',',     '=',     '&',     '%',     '#',     '+',     ';',     '\\');
    $url = preg_replace($from, $to, $url);

    return htmlspecialchars_decode($url);
}

function get_user_avatar_url($avatar, $avatar_type)
{
	global $mobiquo_config, $MAIN_CFG;
	if (empty($avatar)) return '';

	$ret = '';
	switch (intval($avatar_type))
	{
		case USER_AVATAR_UPLOAD && $MAIN_CFG['avatar']['allow_upload']:
			$ret = BASEHREF . $MAIN_CFG['avatar']['path'].'/'.$avatar;
			break;
		case USER_AVATAR_REMOTE && $MAIN_CFG['avatar']['allow_remote']:
			$ret = $avatar;
			break;
		case USER_AVATAR_GALLERY && $MAIN_CFG['avatar']['allow_local']:
			$ret = BASEHREF . $MAIN_CFG['avatar']['gallery_path'].'/'.$avatar;
			break;
	}
	return str_replace(' ', '%20', $ret);
}


function mobiquo_iso8601_encode($timet)
{
    global $user;

    $timezone = ($user->timezone)/3600;
    $t = gmdate("Ymd\TH:i:s", $timet + $user->timezone + $user->dst);

    if($timezone >= 0){
        $timezone = sprintf("%02d", $timezone);
        $timezone = '+'.$timezone;
    }
    else{
        $timezone = $timezone * (-1);
        $timezone = sprintf("%02d",$timezone);
        $timezone = '-'.$timezone;
    }
    $t = $t.$timezone.':00';

    return $t;
}


function get_user_id_by_name($username)
{
    global $db;

    if (!$username)
    {
        return false;
    }

    $username_clean = $db->sql_escape(utf8_clean_string($username));

    $sql = 'SELECT user_id
            FROM ' . USERS_TABLE . "
            WHERE username_clean = '$username_clean'";
    $result = $db->sql_query($sql);
    $user_id = $db->sql_fetchfield('user_id');
    #db->sql_freeresult($result);

    return $user_id;
}

function cut_quote($str, $keep_size)
{
    $str_array = preg_split('/(\[quote\].*?\[\/quote\])/is', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $str = '';

    foreach($str_array as $block)
    {
        if (preg_match('/\[quote\](.*?)\[\/quote\]/is', $block, $block_matches))
        {
            $quote_array = preg_split('/(\[img\].*?\[\/img\]|\[url=.*?\].*?\[\/url\])/is', $block_matches[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $short_str = '';
            $current_size = 0;
            $img_flag = true; // just keep at most one img in the quote
            for ($i = 0, $size = sizeof($quote_array); $i < $size; $i++)
            {
                if (preg_match('/^\[img\].*?\[\/img\]$/is', $quote_array[$i]))
                {
                    if ($img_flag)
                    {
                        $short_str .= $quote_array[$i];
                        $img_flag = false;
                    }
                }
                else if (preg_match('/^\[url=.*?\](.*?)\[\/url\]$/is', $quote_array[$i], $matches))
                {
                    $short_str .= $quote_array[$i];
                    $current_size += strlen($matches[1]);
                    if ($current_size > $keep_size)
                    {
                        $short_str .= "...";
                        break;
                    }
                }
                else
                {
                    if ($current_size + strlen($quote_array[$i]) > $keep_size)
                    {
                        $short_str .= substr($quote_array[$i], 0, $keep_size - $current_size);
                        $short_str .= "...";
                        break;
                    }
                    else
                    {
                        $short_str .= $quote_array[$i];
                        $current_size += strlen($quote_array[$i]);
                    }
                }
            }
            $str .= '[quote]' . $short_str . '[/quote]';
        } else {
            $str .= $block;
        }
    }

    return $str;
}

function video_bbcode_format($type, $url)
{
    switch (strtolower($type)) {
        case 'youtube':
            if (preg_match('#^\s*http://(www\.)?youtube\.com/watch\?v=(\w+)\s*$#', $url, $matches)) {
                $key = $matches['2'];
                $image = '[img]http://i1.ytimg.com/vi/'.$key.'/hqdefault.jpg[/img]';
                $url_code = '[url='.$url.']YouTube Video[/url]';
                $message = $image.$url_code;
            } else if (preg_match('/^\w+$/', $url)) {
                $key = $url;
                $url = 'http://www.youtube.com/watch?v='.$key;
                $image = '[img]http://i1.ytimg.com/vi/'.$key.'/hqdefault.jpg[/img]';
                $url_code = '[url='.$url.']YouTube Video[/url]';
                $message = $image.$url_code;
            } else {
                $message = '';
            }
            break;
        case 'video':
            if (preg_match('#^\s*http(s)?://#', $url)) {
                $message = '[url='.$url.']Video[/url]';
            } else {
                $message = '';
            }
            break;
        case 'gvideo':
        case 'googlevideo':
            if (preg_match('#^\s*http://video.google.com/(googleplayer.swf|videoplay)?docid=-#', $url)) {
                $message = '[url='.$url.']Google Video[/url]';
            } else if (preg_match('/^-?(\d+)/', $url, $matches)) {
                $message = '[url=http://video.google.com/videoplay?docid=-'.$matches['1'].']Google Video[/url]';
            } else {
                $message = '';
            }
            break;
        default: $message = '';
    }

    return $message;
}

if (!function_exists('itemstats_parse'))
{
    function itemstats_parse($message)
    {
        return $message;
    }
}

function check_forum_password($forum_id)
{
    global $user, $db;

    $sql = 'SELECT forum_id
            FROM ' . FORUMS_ACCESS_TABLE . '
            WHERE forum_id = ' . $forum_id . '
                AND user_id = ' . $user->data['user_id'] . "
                AND session_id = '" . $db->sql_escape($user->session_id) . "'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    #db->sql_freeresult($result);

    if (!$row)
    {
        return false;
    }

    return true;
}

function strip_bbcode(&$text, $uid = '')
  {
      if (!$uid)
      {
          $uid = '[0-9a-z]{5,}';
      }

      $text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?(\:?$uid)\]#", ' ', $text);

      $match = get_preg_expression('bbcode_htm');
      $replace = array('\1', '\2', '\1', '', '');

      $text = preg_replace($match, $replace, $text);
}

function get_preg_expression($mode)
  {
      switch ($mode)
      {
          case 'email':
              return '[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*[a-z]+';
          break;

          case 'bbcode_htm':
              return array(
                  '#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
                  '#<!\-\- (l|m|w) \-\-><a href="(.*?)">.*?</a><!\-\- \1 \-\->#',
                  '#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
                  '#<!\-\- .*? \-\->#s',
                  '#<.*?>#s',
              );
          break;
      }

      return '';
  }

function mobi_reindex(array $array){
	$ret = $array = array_values($array);
	foreach($array as $k => $v)
		if(!empty($v['subforums']))
			$ret[$k]['subforums'] = mobi_reindex($v['subforums']);

	return $ret;
}

function mobi_forums($parent=0)
{
	global $db, $images, $userinfo, $CPG_SESS, $lang;
	get_lang('Forums');

	$parent = intval($parent);
	$forums = array();
	$tmp_forums = $db->sql_ufetchrowset('SELECT forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete,
		auth_sticky, auth_announce, auth_vote, auth_pollcreate, auth_attachments, auth_download FROM '. FORUMS_TABLE, SQL_ASSOC);
	$is_auth_ary = auth(AUTH_ALL, AUTH_LIST_ALL, $userinfo, $tmp_forums);

	$c = count($tmp_forums);
	for ($i=0; $i<$c; ++$i) {
		if ($parent && $parent != $tmp_forums[$i]['forum_id'] && $parent != $tmp_forums[$i]['parent_id']) continue;

		if ($is_auth_ary[$tmp_forums[$i]['forum_id']]['auth_view']) {
			$forums[] = $tmp_forums[$i]['forum_id'];
		}
	}

	if (!count($forums)) return array();
	unset($tmp_forums);

	$forums = implode(',', $forums);
	$sql = 'SELECT f.*, p.post_time, u.username, u.user_id
		FROM (( '.FORUMS_TABLE.' f
		LEFT JOIN '.POSTS_TABLE.' p ON p.post_id = f.forum_last_post_id )
		LEFT JOIN '.USERS_TABLE.' u ON u.user_id = p.poster_id )
		WHERE f.forum_id IN ('.$forums.')
		ORDER BY f.cat_id, f.forum_order';
	$result = $db->sql_query($sql);

	$forums = array();
	while ($row = $db->sql_fetchrow($result, SQL_ASSOC))
	{
		if (!$is_auth_ary[$row['forum_id']]['auth_read']) $row['is_protected'] = true;

		if (isset($forums[$row['parent_id']])) {
			$forums[$row['parent_id']]['subforums'][$row['forum_id']] = $row;
		} else {
			$forums[$row['forum_id']] = $row;
		}
	}
	$db->sql_freeresult($result);

	if (is_user())
	{
		$lastvisit = $userinfo['user_lastvisit'];
		if ( isset($CPG_SESS['Forums']['track_all']) ) {
			$lastvisit = $CPG_SESS['Forums']['track_all'];
		}
		$result = $db->sql_query('SELECT t.forum_id, t.topic_id, t.topic_title, t.topic_replies, t.topic_views, p.post_time
				FROM '.TOPICS_TABLE.' t, '.POSTS_TABLE.' p, '.FORUMS_TABLE.' f
				WHERE p.post_id = t.topic_last_post_id
					AND p.post_time > '.$lastvisit.'
					AND t.topic_moved_id = 0
					AND t.forum_id = f.forum_id
				ORDER BY p.post_time DESC');
		$new_topic_data = array();
		while ($topic_data = $db->sql_fetchrow($result, SQL_ASSOC)) {
			//$new_topic_data[$topic_data['forum_id']][$topic_data['topic_id']] = $topic_data;
		}
		$db->sql_freeresult($result);
	}

	$ret = array();
	foreach ($forums as &$forum) {
		$forum = array_merge($forum, $is_auth_ary[$forum['forum_id']]);
		if ($forum['forum_type'] < 2 && FORUM_LOCKED == $forum['forum_status']) {
			if (empty($forum['subforums'])) {
				$forum['subonly'] = true;
			} else {
				foreach ($forum['subforums'] as &$sub) {
					$forum['forum_topics'] += $sub['forum_topics'];
					$forum['forum_posts'] += $sub['forum_posts'];
					if ($sub['post_time'] > $forum['post_time']) {
						$forum['post_time'] = $sub['post_time'];
						$forum['username'] = $sub['username'];
						$forum['user_id'] = $sub['user_id'];
					}
				}
			}
		}
		//$forum['post_username'] = $forums[$i]['post_username'] ? $forums[$i]['post_username'] : $lang['Guest'];
	}
	return mobi_reindex($forums);
}
