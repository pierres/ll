<?php

set_time_limit(0);

class UpdatePosts extends Page{


public function prepare()
	{
	if (!$this->User->isAdmin())
		{
		$this->showWarning('Zutritt verboten!');
		}

	$this->update();

	$this->setValue('body', 'fertig');
	}

private function update()
	{
	$this->DB->execute('LOCK TABLES posts WRITE');

	$posts = $this->DB->getRowSet
		('
		SELECT
			id,
			text
		FROM
			posts
		');

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			text = ?
		WHERE
			id = ?
		');

	foreach ($posts as $post)
		{
		$post['text'] = preg_replace(
			'#<a href="\?title=(.+?)" class="link">\?title=.+?</a>#s',
			'<a href="http://wiki.archlinux.de/?title=$1" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">http://wiki.archlinux.de/?title=$1</a>',
			$post['text']);

		$post['text'] = preg_replace(
			'#<a href="\?title=(.+?)" class="link">(.+?)</a>#s',
			'<a href="http://wiki.archlinux.de/?title=$1" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">$2</a>',
			$post['text']);

		$stm->bindString($post['text']);
		$stm->bindInteger($post['id']);
		$stm->execute();
		}

	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

}

?>