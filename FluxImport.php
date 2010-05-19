#!/usr/bin/php
<?php

require ('modules/Modul.php');
require ('modules/Settings.php');
require ('modules/Exceptions.php');
require ('modules/Functions.php');
require ('modules/Input.php');
require ('modules/Output.php');
require ('modules/L10n.php');

Modul::set('Settings', new Settings());
Modul::set('Input', new Input());
Modul::set('L10n', new L10n());
Modul::set('Output', new Output());

function __autoload($class) {
	Modul::loadModul($class);
}

ini_set('memory_limit', '1024M');

class FluxImport extends Modul {

private $fluxdir = '/home/pierre/public_html/fluxbb-1.4-rc3/upload';


public function run() {
	$this->DB->connect(
		$this->Settings->getValue('sql_host'),
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database'));
	echo "Cleanup old entries\n";
	$this->cleanup();
	echo "Starting Import\n";
	$this->importCategories();
	$this->importForums();
	$this->importPosts();
	$this->importTopics();
	$this->importUsers();
}

private function cleanup() {
	$preal = 1103639163;# al.de creation
	# non-al.de users
	try
		{
		$users = $this->DB->getColumnSet('SELECT
							id
						FROM
							users
						WHERE
							lastlogin<'.$preal.'
							AND regdate<'.$preal.'
							AND lastpost<'.$preal.'
							AND id NOT IN (SELECT userid FROM posts WHERE threadid IN (SELECT id FROM threads WHERE forumid>0))');
		foreach ($users as $user) {
			AdminFunctions::delUser($user);
		}
		}
	catch (DBNoDataException $e)
		{
		}

	# empty threads
	try
		{
		$threads = $this->DB->getColumnSet('SELECT
							id
						FROM
							threads
						WHERE
							id NOT IN (SELECT threadid FROM posts)');
		foreach ($threads as $thread) {
			AdminFunctions::delThread($thread);
		}
		}
	catch (DBNoDataException $e)
		{
		}

	try
		{
		$threads = $this->DB->getColumnSet('SELECT
							id
						FROM
							threads
						WHERE
							lastdate<'.$preal);
		foreach ($threads as $thread) {
			AdminFunctions::delThread($thread);
		}
		}
	catch (DBNoDataException $e)
		{
		}

	# duplicate: "ren hoek" vs. "Ren Höek"
	AdminFunctions::delUser(2678);

	system('rm -f '.$this->fluxdir.'/cache/*.php');
}

private function setAutoIncrement($source, $target) {
	$auto = $this->DB->getColumn('SELECT 
						AUTO_INCREMENT
					FROM
						information_schema.TABLES
					WHERE
						TABLE_SCHEMA = \'ll\'
						AND TABLE_NAME=\''.$source.'\'');
	$this->DB->execute('ALTER TABLE fluxbb.'.$target.' AUTO_INCREMENT = '.$auto);
}

private function importCategories() {
	$this->DB->execute('TRUNCATE TABLE fluxbb.categories');
	$this->setAutoIncrement('cats', 'categories');
	$cats = $this->DB->getRowSet('SELECT * FROM cats');
	$stm = $this->DB->prepare('INSERT INTO 
						fluxbb.categories
					SET
						id = ?,
						cat_name = ?,
						disp_position = ?');
	foreach ($cats as $cat) {
		$stm->bindInteger($cat['id']);
		$stm->bindString(unhtmlspecialchars(cutString($cat['name'], 80)));
		$stm->bindInteger($cat['position']);
		$stm->execute();
	}
	$stm->close();
}

private function importForums() {
	$this->DB->execute('TRUNCATE TABLE fluxbb.forums');
	$this->setAutoIncrement('forums', 'forums');
	$forums = $this->DB->getRowSet('SELECT
						forums.id,
						forums.name,
						forums.description,
						forums.threads,
						forums.posts,
						forums.lastdate,
						(SELECT MAX(posts.id) FROM posts WHERE posts.threadid = forums.lastthread) AS last_post_id,
						(SELECT threads.lastusername FROM threads WHERE threads.id = forums.lastthread) AS last_poster,
						(SELECT forum_cat.position FROM forum_cat WHERE forum_cat.forumid = forums.id) AS disp_position,
						(SELECT forum_cat.catid FROM forum_cat WHERE forum_cat.forumid = forums.id) AS cat_id
					FROM
						forums');
	$stm = $this->DB->prepare('INSERT INTO 
						fluxbb.forums
					SET
						id = ?,
						forum_name = ?,
						forum_desc = ?,
						num_topics = ?,
						num_posts = ?,
						last_post = ?,
						last_post_id = ?,
						last_poster = ?,
						disp_position = ?,
						cat_id = ?');
	foreach ($forums as $forum) {
		$stm->bindInteger($forum['id']);
		$stm->bindString(unhtmlspecialchars(cutString($forum['name'], 80)));
		$stm->bindString(unhtmlspecialchars($forum['description']));
		$stm->bindInteger($forum['threads']);
		$stm->bindInteger($forum['posts']);
		$stm->bindInteger($forum['lastdate']);
		$stm->bindInteger($forum['last_post_id']);
		$stm->bindString(unhtmlspecialchars($forum['last_poster']));
		$stm->bindInteger($forum['disp_position']);
		$stm->bindInteger($forum['cat_id']);
		$stm->execute();
	}
	$stm->close();
}

private function importPosts() {
	$this->DB->execute('TRUNCATE TABLE fluxbb.posts');
	$this->DB->execute('TRUNCATE TABLE fluxbb.search_cache');
	$this->DB->execute('TRUNCATE TABLE fluxbb.search_matches');
	$this->DB->execute('TRUNCATE TABLE fluxbb.search_words');
	$this->setAutoIncrement('posts', 'posts');
	$posts = $this->DB->getRowSet('SELECT
						posts.id AS id,
						posts.username AS poster,
						(IF(posts.userid > 0, posts.userid, 1)) AS poster_id,
						posts.text AS message,
						posts.dat AS posted,
						posts.editdate AS edited,
						COALESCE((SELECT users.name FROM users WHERE users.id = posts.editby), \'Guest\') AS edited_by,
						posts.threadid AS topic_id
					FROM
						posts
					WHERE
						posts.threadid NOT IN (SELECT threads.id FROM threads WHERE threads.forumid=0)');
	$stm = $this->DB->prepare('INSERT INTO 
						fluxbb.posts
					SET
						id = ?,
						poster = ?,
						poster_id = ?,
						message = ?,
						posted = ?,
						edited = ?,
						edited_by = ?,
						topic_id = ?');
	foreach ($posts as $post) {
		$stm->bindInteger($post['id']);
		$stm->bindString(unhtmlspecialchars($post['poster']));
		$stm->bindInteger($post['poster_id']);
		$stm->bindString($this->LLtoBB->convert($post['message']));
		$stm->bindInteger($post['posted']);
		$stm->bindInteger($post['edited']);
		$stm->bindString(unhtmlspecialchars($post['edited_by']));
		$stm->bindInteger($post['topic_id']);
		$stm->execute();
	}
	$stm->close();
	
	$this->DB->execute('UPDATE fluxbb.posts SET edited=NULL, edited_by=NULL WHERE edited=0');
}

private function importTopics() {
	$this->DB->execute('TRUNCATE TABLE fluxbb.topics');
	$this->setAutoIncrement('threads', 'topics');
	$topics = $this->DB->getRowSet('SELECT
						a.id AS id,
						a.firstusername AS poster,
						a.name AS subject,
						a.firstdate AS posted,
						(SELECT MIN(posts.id) FROM posts WHERE posts.threadid=a.id) AS first_post_id,
						a.lastdate AS last_post,
						(SELECT MAX(posts.id) FROM posts WHERE posts.threadid=a.id) AS last_post_id,
						a.lastusername AS last_poster,
						(a.posts-1) AS num_replies,
						a.closed AS closed,
						a.sticky AS sticky,
						a.forumid AS forum_id
					FROM
						threads a
					WHERE
						a.forumid>0');
	$stm = $this->DB->prepare('INSERT INTO 
						fluxbb.topics
					SET
						id = ?,
						poster = ?,
						subject = ?,
						posted = ?,
						first_post_id = ?,
						last_post = ?,
						last_post_id = ?,
						last_poster = ?,
						num_replies = ?,
						closed = ?,
						sticky = ?,
						forum_id = ?');
	foreach ($topics as $topic) {
		$stm->bindInteger($topic['id']);
		$stm->bindString(unhtmlspecialchars($topic['poster']));
		$stm->bindString(unhtmlspecialchars($topic['subject']));
		$stm->bindInteger($topic['posted']);
		$stm->bindInteger($topic['first_post_id']);
		$stm->bindInteger($topic['last_post']);
		$stm->bindInteger($topic['last_post_id']);
		$stm->bindString(unhtmlspecialchars($topic['last_poster']));
		$stm->bindInteger($topic['num_replies']);
		$stm->bindInteger($topic['closed']);
		$stm->bindInteger($topic['sticky']);
		$stm->bindInteger($topic['forum_id']);
		$stm->execute();
	}
	$stm->close();

	$moves = $this->DB->getRowSet('SELECT id, movedfrom FROM threads WHERE movedfrom>0');
	foreach ($moves as $move) {
		$this->DB->execute('INSERT INTO fluxbb.topics (poster, subject, posted, first_post_id, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, moved_to, forum_id) SELECT poster, subject, posted, first_post_id, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, '.$move['id'].', '.$move['movedfrom'].' FROM fluxbb.topics WHERE id = '.$move['id']);
	}
}

private function importUsers() {
	$this->DB->execute('TRUNCATE TABLE fluxbb.users');
	$this->DB->execute('TRUNCATE TABLE fluxbb.online');
	$this->setAutoIncrement('users', 'users');
	$users = $this->DB->getRowSet('SELECT
						id AS id,
						4 AS group_id,
						name AS username,
						password AS password,
						email AS email,
						realname AS realname,
						jabber AS jabber,
						posts AS num_posts,
						lastpost AS last_post,
						regdate AS registered,
						lastlogin AS last_visit
					FROM
						users');
	$stm = $this->DB->prepare('INSERT INTO 
						fluxbb.users
					SET
						id = ?,
						group_id = ?,
						username = ?,
						password = ?,
						email = ?,
						realname = ?,
						jabber = ?,
						num_posts = ?,
						last_post = ?,
						registered = ?,
						last_visit = ?');
	foreach ($users as $user) {
		$stm->bindInteger($user['id']);
		$stm->bindInteger($user['group_id']);
		$stm->bindString(unhtmlspecialchars($user['username']));
		$stm->bindString(unhtmlspecialchars($user['password']));
		$stm->bindString(unhtmlspecialchars($user['email']));
		$stm->bindString(unhtmlspecialchars(cutString($user['realname'], 40)));
		$stm->bindString(unhtmlspecialchars($user['jabber']));
		$stm->bindInteger($user['num_posts']);
		$stm->bindInteger($user['last_post']);
		$stm->bindInteger($user['registered']);
		$stm->bindInteger($user['last_visit']);
		$stm->execute();
	}
	$stm->close();

	$this->DB->execute('UPDATE fluxbb.users SET realname=NULL WHERE realname=\'\'');
	$this->DB->execute('UPDATE fluxbb.users SET jabber=NULL WHERE jabber=\'\'');
	$this->DB->execute('UPDATE fluxbb.users SET last_post=NULL WHERE last_post=0');
	$this->DB->execute('UPDATE fluxbb.users SET last_visit=registered WHERE last_visit=0');

	# Guest
	$this->DB->execute('INSERT INTO fluxbb.users (id, group_id, username, password, email) VALUES(1, 3, \'Guest\', \'Guest\', \'Guest\')');
	# Pierre = admin
	$this->DB->execute('UPDATE fluxbb.users SET group_id=1 WHERE id=486');
	# Mathias, Johannes = mod
	$this->DB->execute('UPDATE fluxbb.users SET group_id=2 WHERE id=1937');
	$this->DB->execute('UPDATE fluxbb.users SET group_id=2 WHERE id=2200');


	system('rm -f '.$this->fluxdir.'/img/avatars/*.{jpg,gif,png}');
	$avatars = $this->DB->getRowSet('SELECT * FROM avatars WHERE id IN (SELECT id FROM users)');
	foreach ($avatars as $avatar) {
		if ($avatar['type'] == 'image/jpeg') {
			$ext = '.jpg';
		} elseif ($avatar['type'] == 'image/gif') {
			$ext = '.gif';
		} elseif ($avatar['type'] == 'image/png') {
			$ext = '.png';
		} else {
			echo "error: $avatar[id]\n";
			continue;
		}
		file_put_contents($this->fluxdir.'/img/avatars/'.$avatar['id'].$ext, $avatar['content']);
	}
}


}


$FI = new FluxImport();
$FI->run();

?>