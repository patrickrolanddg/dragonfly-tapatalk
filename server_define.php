<?php
/**
*
* @copyright (c) 2009 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

defined('IN_MOBIQUO') or exit;

$server_param = array(

	'get_config' => array(
		'function'  => 'get_config_func',
		//'function'  => 'Dragonfly_Mobiquo::init',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_forum'),

	# Forum
	'get_forum' => array(
		'function'  => 'get_forum_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::list',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_forum.'),

	'get_participated_forum' => array(
		'function'  => 'get_participated_forum_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::listPartecipated',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_forum.'),

	'mark_all_as_read' => array(
		'function'  => 'mark_all_as_read_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::setRead',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'no need parameters for mark_all_as_read'),

	'login_forum' => array(
		'function'  => 'login_forum_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
		'docstring' => 'Never executed'),

	'get_id_by_url' => array(
		'function'  => 'get_id_by_url_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'get_id_by_url need one parameters as url.'),

	'get_board_stat' => array(
		'function'  => 'get_board_stat_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::stats',
		'signature' => array(array($xmlrpcStruct)),
		'docstring' => 'no parameter'),

	'get_furom_status' => array(
		'function' => '',
		//'function'  => 'Dragonfly_Mobiquo_Forum::status',
		'signature' => '',
		'docstring' => ''),

	'get_subscribed_forum' => array(
		'function'  => 'get_subscribed_forum_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::listWatched',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_subscribed_forum'),

	'subscribe_forum' => array(
		'function'  => 'subscribe_forum_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::addToWatchlist',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'subscribe_topic need one parameters as forum id.'),

	'unsubscribe_forum' => array(
		'function'  => 'unsubscribe_forum_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::removeFromWatchlist',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'unsubscribe_topic need one parameters as forum id.'),

	'get_subscribed_topic' => array(
		'function'  => 'get_subscribed_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::listWatched',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_subscribed_topic'),

	'search' => array(
		'function' => 'search_func',
		//'function'  => 'Dragonfly_Mobiquo_Forum::listBySearch',
		'signature' => array(array($xmlrpcStruct, $xmlrpcStruct)),
		'docstring' => 'advanced search in tapatalk'),

	# User
	'login' => array(
		'function'  => 'login_func',
		//'function'  => 'Dragonfly_Mobiquo_User::login',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBoolean),
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBoolean, $xmlrpcString)),
		'docstring' => 'Server returns cookies in HTTP header.
			Client should store the cookies and pass it back to server for all subsequence calls to maintain user session.
			** DO NOT include HTTP Cookies in the request header **.'),

	'get_inbox_stat' => array(
		'function'  => 'get_inbox_stat_func',
		//'function'  => 'Dragonfly_Mobiquo_User::updates',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
		'docstring' => 'returns inbox related statistic for the user.
			In API Level 3 there is no input parameter need to pass into this function.'),

	'logout_user' => array(
		'function'  => 'logout_user_func',
		//'function'  => 'Dragonfly_Mobiquo_User::logout',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'Logout user, no input and output required.'),

	'get_online_users' => array(
		'function'  => 'get_online_users_func',
		//'function'  => 'Dragonfly_Mobiquo_User::online',
		'signature' => array(
			array($xmlrpcStruct),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt, $xmlrcpString),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt, $xmlrcpString, $xmlrpcString)),
		'docstring' => 'Returns a list of user who are currently online. You can specify forum and thread to limit the users you need.'),

	'get_user_info' => array(
		'function'  => 'get_user_info_func',
		//'function'  => 'Dragonfly_Mobiquo_User::info',
		'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
		'docstring' => 'Returns user related information'),

	'get_user_topic' => array(
		'function'  => 'get_user_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_User::listOwnTopics',
		'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
		'docstring' => 'Returns a list of topics (max 50) the user has previously created. Sorted by last reply time'),

	'get_user_reply_post' => array(
		//'function'  => 'Dragonfly_Mobiquo_User::listOwnReplies',
		'function'  => 'get_user_reply_post_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
		'docstring' => 'Returns a list of posts (max. 50) that\'s a particular user has replied to.'),

	'upload_avatar' => array(
		'function'  => 'upload_avatar_func',
		//'function'  => 'Dragonfly_Mobiquo_User::setAvatar',
		'signature' => array(array($xmlrpcStruct)),
		'docstring' => 'parameter should be'),

	# Topic
	'mark_topic_read' => array(
		'function' => '',
		//'function'  => 'Dragonfly_Mobiquo_Topic::setRead',
		'signature' => array(
				array($xmlrpcStruct, $xmlrcpBoolean),
				array($xmlrpcStruct, $xmlrcpBoolean, $xmlrpcBase64)),
		'docstring' => 'mark unread topics as read'),

	'get_topic_status' => array(
		'function'  => 'get_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::status',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString)),
	'docstring' => 'Given an array of topic IDs, returns their status including unread status, number of reply, number of view and so on.
		A light-weight approach to retrieve certain information without pulling a list of unwanted data.'),

	'new_topic' => array(
		'function'  => 'new_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::new',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString, $xmlrpcArray)),
		'docstring' => 'post new topic to a particular forum'),

	'get_topic' => array(
		'function'  => 'get_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::list',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString)),
	'docstring' => 'Returns a list of topics under a specific forum.
		It can also return sticky topics and announcement, given the "mode" parameter is provided.'),

	'get_unread_topic' => array(
		'function'  => 'get_unread_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listUnread',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt, $xmlrpcString)),
		'docstring' => 'Returns a list of unread topics ordered by date'),

	'get_participated_topic' => array(
		'function'  => 'get_participated_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listParticipated',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcStruct, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt)),
		'docstring' => 'Returns a list of topics that either the user has previously replied to, or is the original topic creator, ordered by date.'),

	'get_latest_topic' => array(
		'function' => 'search_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listLatest',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
			array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcStruct)),
		'docstring' => 'Returns a list of latest topics ordered by date.
			This is the replacement function of get_new_topic in API Level 3.
			This function will be invoked instead of get_new_topic if get_config returns "get_latest_topic=1"'),

	'get_new_topic' => array(
		'function'  => 'get_new_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listLatest',
		'signature' => array(
			array($xmlrpcArray),
			array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt)),
		'docstring' => ''),

	'search_topic' => array(
		'function'  => 'search_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listBySearch',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcBase64)),
		'docstring' => 'a simple search allows user to enter a query string and it will return a list of topics'),

	'subscribe_topic' => array(
		'function'  => 'subscribe_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::listWatched',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'subscribe_topic need one parameters as topic id.'),

	'unsubscribe_topic' => array(
		'function'  => 'unsubscribe_topic_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::removeFromWatchlist',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'unsubscribe_topic need one parameters as topic id.'),

	'get_thread' => array(
		'function'  => 'get_thread_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::get',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcBoolean),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString),
			array($xmlrpcStruct)),
		'docstring' => 'Returns a list of posts under the same thread, given a topic_id'),

	'get_thread_by_unread' => array(
		'function'  => 'get_thread_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::get',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBoolean),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'This function provides a mean to allow users to jump to the "First Unread" post within a thread he has previously participated.
			Please note that this function is used in conjunction with "goto_unread" in get_config function.
			If "goto_unread" is returned and is = "1", get_thread_by_unread is always called instead of get_thread function.
			Please be noted that this function is not invoked when under Guest mode.'),

	'get_thread_by_post' => array(
		'function'  => 'get_thread_func',
		//'function'  => 'Dragonfly_Mobiquo_Topic::goToPost',
		//'function'  => 'Dragonfly_Mobiquo_Post::goTo',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBoolean),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'This function provides a mean to allow users to jump to the exact post within a thread given the post_id as the parameter.
			Please note that this function is used in conjunction with "goto_post" in get_config function. If "goto_post" is returned and is = "1",
			get_thread_by_post is always called instead of get_thread function when the app attempts to enter a thread from a list of posts.
			This function is useful, for example, when entering a thread from a list of posts generated from Search (search_'),

	# Post
	'report_post' => array(
		'function'  => 'report_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::report',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'parameter should be)'),

	'reply_post' => array(
		'function'  => 'reply_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::reply',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcArray)),
		'docstring' => 'parameter should be array(int,string,string)'),

	'get_quote_post' => array(
		'function'  => 'get_quote_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::quote',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'parameter should be array(string)'),

	'get_raw_post' => array(
		'function'  => 'get_raw_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::getRaw',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'parameter should be array(string)'),

	'save_raw_post' => array(
		'function'  => 'save_raw_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::setRaw',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64)),
		'docstring' => 'parameter should be array(string, base64, base64)'),


	'get_smilies' => array(
		'function' => '',
		//'function'  => 'Dragonfly_Mobiquo_Post::listSmilies',
		'signature' => '',
		'docstring' => ''),

	# Search
	'search_post' => array(
		'function'  => 'search_post_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::listBySearch',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcBase64)),
		'docstring' => 'a simple search allows user to enter a query string and it will return a list of post that matched the query.'),

	'upload_attach' => array(
		'function'  => 'upload_attach_func',
		//'function'  => 'Dragonfly_Mobiquo_Post::attach',
		'signature' => array(array($xmlrpcStruct)),
		'docstring' => 'parameter should be'),

	# Private Message
	'report_pm' => array(
		'function'  => 'report_pm_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'report a problematic private message to moderator.
			This function is used in conjunction with "can_report_pm" flag returned from "login" function.'),

	'create_message' => array(
		'function'  => 'create_message_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcArray, $xmlrpcBase64, $xmlrpcBase64),
			array($xmlrpcStruct, $xmlrpcArray, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcInt, $xmlrpcString)),
		'docstring' => 'send a private message to one or more users.'),

	'get_box_info' => array(
		'function'  => 'get_box_info_func',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'Returns a list of message boxes and their information.
			It allows the app to support multiple folders beyond Inbox and Sent box.'),

	'get_box' => array(
		'function'  => 'get_box_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcDateTime),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
			array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'Returns a list of message subject and short content from a specific box.'),

	'get_message' => array(
		'function'  => 'get_message_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean)),
		'docstring' => 'Returns content of private message given a box id and message id'),

	'get_quote_pm' => array(
		'function'  => 'get_quote_pm_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'Returns a processed [quote] content just like when user click the Reply or Forward button on the web browser.
			This is to address different forum systems requires different [quote] format'),


	'delete_message' => array(
		'function'  => 'delete_message_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
		'docstring' => 'delete a particular private message'),

	'mark_pm_unread' => array(
		'function' => '',
		'signature' => '',
		'docstring' => 'mark the particular private message as unread'),

	# Attachement
	'remove_attachement' => array(
		'function' => '',
		'signature' => '',
		'docstring' => ''),

	# Not in ufficial API
	'authorize_user' => array(
		'function'  => 'authorize_user_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
		'docstring' => 'authorize need two parameters,the first is user name, second is password.'),

	'get_bookmarked_topic' => array(
		'function'  => 'get_bookmarked_topic_func',
		'signature' => array(array($xmlrpcArray)),
		'docstring' => 'no need parameters for get_bookmarked_topic'),

	'create_topic' => array(
		'function'  => 'create_topic_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64)),
		'docstring' => 'parameter should be array(int,string,string)'),

	'reply_topic' => array(
		'function'  => 'reply_topic_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString),
			array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64)),
		'docstring' => 'parameter should be array(int,string,string)'),

	'bookmark_topic' => array(
		'function'  => 'bookmark_topic_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'bookmark_topic need one parameters as topic id.'),

	'unbookmark_topic' => array(
		'function'  => 'unbookmark_topic_func',
		'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
		'docstring' => 'unbookmark_topic need one parameters as topic id.'),


	'attach_image' => array(
		'function'  => 'attach_image_func',
		'signature' => array(
			array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString, $xmlrpcString)),
		'docstring' => 'parameter should be array()')
);
