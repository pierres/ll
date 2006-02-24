<?php


class AdminFunctions extends Modul{


private function __construct()
	{
	}


public static function delThread($thread)
	{
	try
		{
		$forum = self::__get('Sql')->fetchValue('SELECT forumid FROM threads WHERE id = '.$thread);
		}
	catch (SqlNoDataException $e)
		{
		return;
		}

	self::removeThread($thread);
	self::updateForum($forum);
	}

private static function removeThread($thread)
	{
	self::__get('Sql')->query
		('
		DELETE FROM
			post_file
		WHERE
			postid IN (SELECT id FROM posts WHERE threadid ='.$thread.')
		');

	self::__get('Sql')->query
		('
		DELETE FROM
			posts
		WHERE
			threadid = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			polls
		WHERE
			id = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			poll_values
		WHERE
			pollid = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			poll_voters
		WHERE
			pollid = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			threads
		WHERE
			id = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			threads_log
		WHERE
			threadid = '.$thread
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			thread_user
		WHERE
			threadid = '.$thread
		);
	}

public static function delForum($forum)
	{
	try
		{
		$threads = self::__get('Sql')->fetchCol
			('
			SELECT
				id
			FROM
				threads
			WHERE
				forumid = '.$forum
			);
		}
	catch(SqlNoDataException $e)
		{
		$threads = array();
		}

	foreach ($threads as $thread)
		{
		self::removeThread($thread);
		}

	self::__get('Sql')->query
		('
		DELETE FROM
			forums
		WHERE
			id = '.$forum
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			forum_cat
		WHERE
			forumid = '.$forum
		);
	}

public static function delCat($cat)
	{
	try
		{
		$forums = self::__get('Sql')->fetchCol
			('
			SELECT
				forums.id
			FROM
				forum_cat,
				cats,
				forums
			WHERE
				forum_cat.catid = '.$cat.'
				AND cats.id = '.$cat.'
				AND forum_cat.forumid = forums.id
				AND forums.boardid = cats.boardid
			');
		}
	catch (SqlNoDataException $e)
		{
		$forums = array();
		}

	foreach ($forums as $forum)
		{
		self::delForum($forum);
		}

	self::__get('Sql')->query
		('
		DELETE FROM
			forum_cat
		WHERE
			catid = '.$cat
		);

	self::__get('Sql')->query
		('
		DELETE FROM
			cats
		WHERE
			id = '.$cat
		);
	}

public static function delBoard($board)
	{
	try
		{
		$cats = self::__get('Sql')->fetchCol
			('
			SELECT
				id
			FROM
				cats
			WHERE
				boardid = '.$board
			);
		}
	catch (SqlNoDataException $e)
		{
		$cats = array();
		}

	foreach ($cats as $cat)
		{
		self::delCat($cat);
		}

	self::__get('Sql')->query
		('
		DELETE FROM
			boards
		WHERE
			id = '.$board
		);

	unlink(PATH.'html'.$board.'.html');
	unlink(PATH.'html'.$board.'.css');
	unlink(PATH.'html'.$board.'.js');
	}

//---------------------------------------------------------------------------------------------------------
public static function updateThread($thread)
	{
	try
		{
		$post_count = self::__get('Sql')->numRows('posts WHERE deleted = 0 AND threadid = '.$thread);

		$lastpost = self::__get('Sql')->fetchRow
			('
			SELECT
				dat,
				userid,
				username
			FROM
				posts
			WHERE
				threadid = '.$thread.'
				AND deleted = 0
			ORDER BY
				dat DESC
			');

		$firstpost = self::__get('Sql')->fetchRow
			('
			SELECT
				dat,
				userid,
				username
			FROM
				posts
			WHERE
				threadid = '.$thread.'
				AND deleted = 0
			ORDER BY
				dat ASC
			');

		self::__get('Sql')->query
			('
			UPDATE
				threads
			SET
				lastdate = '.$lastpost['dat'].',
				lastuserid = '.$lastpost['userid'].',
				lastusername = \''.self::__get('Sql')->escapeString($lastpost['username']).'\',
				firstdate = '.$firstpost['dat'].',
				firstuserid = '.$firstpost['userid'].',
				firstusername = \''.self::__get('Sql')->escapeString($firstpost['username']).'\',
				posts = '.$post_count.'
			WHERE
				id = '.$thread
			);
		}
	catch (SqlNoDataException $e)
		{
		/** Ein Thread ohne Postings können wir auch als gelöscht markieren */
		self::__get('Sql')->query
			('
			UPDATE
				threads
			SET
				deleted = 1
			WHERE
				id = '.$thread
			);
		}
	}

public static function updateForum($forum)
	{
	try
		{
		$thread_data = self::__get('Sql')->fetchRow
			('
			SELECT
				id,
				posts
			FROM
				threads
			WHERE
				forumid = '.$forum.'
				AND deleted = 0
			ORDER BY
				lastdate DESC
			');

		$count =  self::__get('Sql')->fetchRow
			('
			SELECT
				COUNT(*) AS threads,
				SUM(posts) AS posts
			FROM
				threads
			WHERE
				forumid = '.$forum.'
				AND deleted = 0
			');
		}
	catch (SqlNoDataException $e)
		{
		$thread_data['id'] 	= 0;
		$count['threads'] 	= 0;
		$count['posts'] 	= 0;
		}

	self::__get('Sql')->query
		('
		UPDATE
			forums
		SET
			lastthread = '.$thread_data['id'].',
			threads = '.$count['threads'].',
			posts = '.$count['posts'].'
		WHERE
			id = '.$forum
		);
	}

public static function buildPositionMenu($name, $values, $marked)
	{
	$menu = '<select name="'.$name.'">';

	for ($i = 1; $i <= $values; $i++)
		{
		$menu .= '<option value="'.$i.'"'.($i == $marked ? ' selected="selected"' : '').'>'.$i.'</option>';
		}

	return $menu.'</select>';
	}

public static function getUserId($name)
	{
	return self::__get('Sql')->fetchValue
		('
		SELECT
			id
		FROM
			users
		WHERE
			name = \''.self::__get('Sql')->FormatString($name).'\'
		');
	}

public static function getUserName($id)
	{
	return self::__get('Sql')->fetchValue
		('
		SELECT
			name
		FROM
			users
		WHERE
			id = '.$id
		);
	}


}




?>