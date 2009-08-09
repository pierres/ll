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
require('NewPost.php');

class QuotePost extends NewPost {


protected function checkInput()
	{
	/** Hier noch weitere Test bzgl. PrivateThreads nötig */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.id AS post,
				threads.id AS thread,
				threads.forumid,
				threads.counter,
				posts.text,
				posts.username
			FROM
				posts,
				threads
			WHERE
				posts.deleted = 0
				AND threads.closed = 0
				AND threads.deleted = 0
				AND threads.forumid != 0
				AND posts.threadid = threads.id
				AND posts.id = ?'
			);
		$stm->bindInteger($this->Input->Get->getInt('post'));
		$data = $stm->getRow();
		$stm->close();
		}
	catch (RequestException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('No post specified'));
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('Post not found'));
		}

	$this->text = '<quote '.unhtmlspecialchars($data['username']).'>'.$this->UnMarkup->fromHtml($data['text'])."</quote>\n\n";

	$this->thread = $data['thread'];
	$this->forum = $data['forumid'];
	$this->counter = $data['counter'];

	$this->setParam('post', $data['post']);
	}

protected function checkForm()
	{
	if (!$this->User->isOnline())
		{
		$text = preg_replace('/\s*<quote .+?>.+<\/quote>\s*/s', '', $this->Input->Post->getString('text'));
		if (empty($text))
			{
			$this->showWarning($this->L10n->getText('Post is emnpty'));
			}
		}

	parent::checkForm();
	}

}

?>