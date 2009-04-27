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
/** FIXME: Nicht geschützt via Form */
class StickThread extends Page {

protected $forum = 0;
protected $thread = 0;

public function prepare()
	{
	$this->checkInput();
	$this->checkAccess();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			sticky = ABS(sticky - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->updateForum();
	}

protected function checkInput()
	{
	try
		{
		$this->thread = $this->Input->Get->getInt('thread');
		}
	catch (RequestException $e)
		{
		$this->showFailure($this->L10n->getText('No topic specified.'));
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				deleted = 0
				AND id = ?
			');
		$stm->bindInteger($this->thread);
		$this->forum = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('Topic not found.'));
		}
	}

protected function checkAccess()
	{
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure($this->L10n->getText('Topic not found.'));
		}
	}

protected function updateForum()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateForum($this->forum);
	}

public function show()
	{
	$this->Output->redirect('Postings', array('thread' => $this->thread));
	}

}

?>