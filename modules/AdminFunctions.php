<?php


class AdminFunctions extends Modul{


private function __construct()
	{
	}


public static function delThread($thread)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE id = ?'
			);
		$stm->bindInteger($thread);
		$forum = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return;
		}

	self::removeThread($thread);
	self::updateForum($forum);
	}

public static function delPost($post)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				threads.forumid,
				threads.id
			FROM
				posts,
				threads
			WHERE
				posts.id = ?'
			);
		$stm->bindInteger($post);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return;
		}

	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				COUNT(*)
			FROM
				posts
			WHERE
				threadid = ?'
			);
		$stm->bindInteger($data['id']);
		$posts = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$posts = 0;
		}

	if ($posts <= 1)
		{
		self::removeThread($data['id']);
		return;
		}

	self::removePost($post);

	self::updateThread($data['id']);
	self::updateForum($data['forumid']);
	}

private static function removeThread($thread)
	{
	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			postid IN (SELECT id FROM posts WHERE threadid = ?)
		');
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			posts
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			polls
		WHERE
			id = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			poll_values
		WHERE
			pollid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			poll_voters
		WHERE
			pollid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			threads
		WHERE
			id = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			threads_log
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			thread_user
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();
	}

private static function removePost($post)
	{
	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			postid = ?
		');
	$stm->bindInteger($post);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			posts
		WHERE
			id = ?'
		);
	$stm->bindInteger($post);
	$stm->execute();
	$stm->close();
	}

public static function delForum($forum)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				id
			FROM
				threads
			WHERE
				forumid = ?'
			);
		$stm->bindInteger($forum);

		foreach ($stm->getColumnSet() as $thread)
			{
			self::removeThread($thread);
			}
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		}

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			forums
		WHERE
			id = ?'
		);
	$stm->bindInteger($forum);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			forum_cat
		WHERE
			forumid = ?'
		);
	$stm->bindInteger($forum);
	$stm->execute();
	$stm->close();
	}

public static function delCat($cat)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				forums.id
			FROM
				forum_cat,
				cats,
				forums
			WHERE
				forum_cat.catid = ?
				AND cats.id = forum_cat.catid
				AND forum_cat.forumid = forums.id
				AND forums.boardid = cats.boardid
			');
		$stm->bindInteger($cat);

		foreach ($stm->getColumnSet() as $forum)
			{
			self::delForum($forum);
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			forum_cat
		WHERE
			catid = ?'
		);
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			cats
		WHERE
			id = ?'
		);
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();
	}

public static function delBoard($board)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				boardid = ?'
			);
		$stm->bindInteger($board);

		foreach ($stm->getColumnSet() as $cat)
			{
			self::delCat($cat);
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$stm = self::__get('DB')->prepare
		('
		DELETE FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($board);
	$stm->execute();
	$stm->close();

	unlink(PATH.'html'.$board.'.html');
	unlink(PATH.'html'.$board.'.css');
	unlink(PATH.'html'.$board.'.js');
	}

/** TODO: Summary erstellen; Änderungen optimieren (nur falls nötig) */
//---------------------------------------------------------------------------------------------------------
public static function updateThread($thread)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			SELECT
				dat,
				userid,
				username
			FROM
				posts
			WHERE
				threadid = ?
				AND deleted = 0
			ORDER BY
				dat DESC
			');
		$stm->bindInteger($thread);
		$lastpost = $stm->getRow();
		$stm->close();

		$stm = self::__get('DB')->prepare
			('
			SELECT
				dat,
				userid,
				username
			FROM
				posts
			WHERE
				threadid = ?
				AND deleted = 0
			ORDER BY
				dat ASC
			');
		$stm->bindInteger($thread);
		$firstpost = $stm->getRow();
		$stm->close();

		$stm = self::__get('DB')->prepare
			('
			UPDATE
				threads
			SET
				lastdate = ?,
				lastuserid = ?,
				lastusername = ?,
				firstdate = ?,
				firstuserid = ?,
				firstusername = ?,
				posts = (SELECT COUNT(*) FROM posts WHERE deleted = 0 AND threadid = ?)
			WHERE
				id = ?'
			);
		$stm->bindInteger($lastpost['dat']);
		$stm->bindInteger($lastpost['userid']);
		$stm->bindString($lastpost['username']);

		$stm->bindInteger($firstpost['dat']);
		$stm->bindInteger($firstpost['userid']);
		$stm->bindString($firstpost['username']);

		$stm->bindInteger($thread);
		$stm->bindInteger($thread);

		$stm->execute();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		/** Ein Thread ohne Postings können wir auch als gelöscht markieren */
		$stm = self::__get('DB')->prepare
			('
			UPDATE
				threads
			SET
				deleted = 1
			WHERE
				id = ?'
			);
		$stm->bindInteger($thread);
		$stm->execute();
		$stm->close();
		}
	}

public static function updateForum($forum)
	{
	try
		{
		$stm = self::__get('DB')->prepare
			('
			UPDATE
				forums AS f
			SET
				lastthread = (SELECT id FROM threads WHERE forumid = f.id AND deleted = 0 ORDER BY lastdate DESC LIMIT 1),
				threads = (SELECT COUNT(*) FROM threads WHERE forumid = f.id AND deleted = 0),
				posts = (SELECT SUM(posts) FROM threads WHERE forumid = f.id AND deleted = 0)
			WHERE
				id = ?'
			);
		$stm->bindInteger($forum);
		$stm->execute();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
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
	$stm = self::__get('DB')->prepare
		('
		SELECT
			id
		FROM
			users
		WHERE
			name = ?
		');
	$stm->bindString(htmlspecialchars($name));

	$id = $stm->getColumn();
	$stm->close();
	return $id;
	}

public static function getUserName($id)
	{
	$stm = self::__get('DB')->prepare
		('
		SELECT
			name
		FROM
			users
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);

	$name =  $stm->getColumn();
	$stm->close();
	return $name;
	}


}

?>