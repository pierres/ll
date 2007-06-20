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
		$post['text'] = $this->Markup->toHtml($this->UnMarkup->fromHtml($post['text']));

		$stm->bindString($post['text']);
		$stm->bindInteger($post['id']);
		$stm->execute();
		}

	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

}

?>