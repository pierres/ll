<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
class AdminFunctions extends Modul{


private function __construct()
	{
	}


public static function delThread($thread)
	{
	try
		{
		$stm = self::get('DB')->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				id = ?'
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
		$stm = self::get('DB')->prepare
			('
			SELECT
				threads.forumid,
				threads.id
			FROM
				posts
			JOIN 
				threads
			ON
				posts.id = ?
			AND
				posts.threadid = threads.id
				'
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
		$stm = self::get('DB')->prepare
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
	/** FIXME: redundant */
	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			postid IN (SELECT id FROM posts WHERE threadid = ?)
		');
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			posts
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			polls
		WHERE
			id = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			poll_values
		WHERE
			pollid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			poll_voters
		WHERE
			pollid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			threads
		WHERE
			id = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			threads_log
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($thread);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
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
	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			postid = ?
		');
	$stm->bindInteger($post);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
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
		$stm = self::get('DB')->prepare
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

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			forums
		WHERE
			id = ?'
		);
	$stm->bindInteger($forum);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
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
		$stm = self::get('DB')->prepare
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

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			forum_cat
		WHERE
			catid = ?'
		);
	$stm->bindInteger($cat);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
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
		$stm = self::get('DB')->prepare
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

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($board);
	$stm->execute();
	$stm->close();
	}

/** TODO: Summary erstellen; Änderungen optimieren (nur falls nötig) */
//---------------------------------------------------------------------------------------------------------
public static function updateThread($thread)
	{
	try
		{
 		self::get('DB')->execute('LOCK TABLES posts READ, threads WRITE');

		$stm = self::get('DB')->prepare
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
			LIMIT 1
			');
		$stm->bindInteger($thread);
		$lastpost = $stm->getRow();
		$stm->close();

		$stm = self::get('DB')->prepare
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
			LIMIT 1
			');
		$stm->bindInteger($thread);
		$firstpost = $stm->getRow();
		$stm->close();

		$stm = self::get('DB')->prepare
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

 		self::get('DB')->execute('UNLOCK TABLES');

		self::updatePostCounter($thread);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		/** Ein Thread ohne Postings können wir auch als gelöscht markieren */
		$stm = self::get('DB')->prepare
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

 		self::get('DB')->execute('UNLOCK TABLES');
		}
	}

public static function updatePostCounter($thread)
	{
 	self::get('DB')->execute('LOCK TABLES posts WRITE');

	try
		{
		$stm = self::get('DB')->prepare
			('
			SELECT
				id,
				threadid
			FROM
				posts
			WHERE
				threadid = ?
			ORDER BY
				dat ASC
			');
		$stm->bindInteger($thread);
		$posts = $stm->getRowSet();

		$counter = 0;

		$stm2 = self::get('DB')->prepare
			('
			UPDATE
				posts
			SET
				counter = ?
			WHERE
				id = ?
			');

		foreach ($posts as $post)
			{
			$stm2->bindInteger($counter);
			$stm2->bindInteger($post['id']);
			$stm2->execute();

			$counter++;
			}

		$stm2->close();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		self::get('DB')->execute('LOCK TABLES threads WRITE');
		$stm->close();
		$stm2 = self::get('DB')->prepare
			('
			UPDATE
				threads
			SET
				deleted = 1
			WHERE
				id = ?
			');
		$stm2->bindInteger($thread);
		$stm2->execute();
		$stm2->close();
		}

 	self::get('DB')->execute('UNLOCK TABLES');
	}

public static function updateForum($forum)
	{
	try
		{
		$stm = self::get('DB')->prepare
			('
			UPDATE
				forums
			SET
				lastthread = (SELECT id FROM threads WHERE forumid = forums.id AND deleted = 0 ORDER BY lastdate DESC LIMIT 1),
				threads = (SELECT COUNT(*) FROM threads WHERE forumid = forums.id AND deleted = 0),
				posts = (SELECT COALESCE(SUM(posts), 0) FROM threads WHERE forumid = forums.id AND deleted = 0)
			WHERE
				id = ?'
			);
		$stm->bindInteger($forum);
		$stm->execute();
		$stm->close();

		self::updateThreadCounter($forum);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

public static function updateThreadCounter($forum)
	{
	self::get('DB')->execute('LOCK TABLES threads WRITE');

	try
		{
		/** FIXME: movedfrom.counter wird von forumid überschrieben oder umgekehrt
		 -> deshalb wird es erstmal deaktiviert */
		$stm = self::get('DB')->prepare
			('
			SELECT
				id,
				forumid
			FROM
				threads
			WHERE
				forumid = ?
			ORDER BY
				lastdate ASC
			');
		$stm->bindInteger($forum);
		$threads = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$threads = array();
		}

	$counter = 0;

	$stm2 = self::get('DB')->prepare
		('
		UPDATE
			threads
		SET
			counter = ?
		WHERE
			id = ?
		');

	foreach ($threads as $thread)
		{
		$stm2->bindInteger($counter);
		$stm2->bindInteger($thread['id']);
		$stm2->execute();

		$counter++;
		}

	$stm2->close();
	$stm->close();

 	self::get('DB')->execute('UNLOCK TABLES');
	}

public static function getUserId($name)
	{
	$stm = self::get('DB')->prepare
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
	$stm = self::get('DB')->prepare
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

public static function delUser($id)
	{
	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			users
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			avatars
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			poll_voters
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			attachments
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	self::get('DB')->execute
		('
		DELETE FROM
			attachment_thumbnails
		WHERE
			id NOT IN (SELECT id FROM attachments)
		');

	self::get('DB')->execute
		('
		DELETE FROM
			post_attachments
		WHERE
			attachment_id NOT IN (SELECT id FROM attachments)
		');

	self::get('DB')->execute
		('
		UPDATE
			posts
		SET
			file = 0
		WHERE
			file = 1
			AND id NOT IN (SELECT postid FROM post_attachments)
		');

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			thread_user
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	/** Remove orphaned PrivateThreads */
	try
		{
		$privateThreads = self::get('DB')->getColumnSet
			('
			SELECT
				id
			FROM
				threads
			WHERE
				forumid = 0
				AND id NOT IN (SELECT threadid FROM thread_user)
			');
		foreach ($privateThreads as $privateThread)
			{
			AdminFunctions::delThread($privateThread);
			}
		}
	catch (DBNoDataException $e)
		{
		}

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			threads_log
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			user_group
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			password_key
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		UPDATE
			threads
		SET
			firstuserid = 0
		WHERE
			firstuserid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		UPDATE
			threads
		SET
			lastuserid = 0
		WHERE
			lastuserid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		UPDATE
			posts
		SET
			userid = 0
		WHERE
			userid = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		UPDATE
			posts
		SET
			editby = 0
		WHERE
			editby = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = self::get('DB')->prepare
		('
		DELETE FROM
			session
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();
	}

}

?>
