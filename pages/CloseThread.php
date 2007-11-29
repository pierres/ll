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
class CloseThread extends Form{

protected $forum 		= 0;
protected $closed 		= false;
protected $thread		= 0;

protected function setForm()
	{
	try
		{
		$this->thread = $this->Io->getInt('thread');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid,
				closed
			FROM
				threads
			WHERE
				deleted = 0
				AND id = ?
			');
		$stm->bindInteger($this->thread);
		$result = $stm->getRow();
		$this->forum = $result['forumid'];
		$this->closed = ($result['closed'] == 1);
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden!');
		}

	$this->setValue('title', 'Thema '.($this->closed ? 'öffnen' : 'schließen'));

	$this->addHidden('thread', $this->thread);
	$this->requires('thread');

	$this->addOutput('Soll das Thema wirklich ge'.($this->closed ? 'öffnet' : 'schlossen').' werden?');

	$this->addSubmit('Thema '.($this->closed ? 'öffnen' : 'schließen'));
	}

protected function checkForm()
	{
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Thema gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			closed = ABS(closed - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

}

?>