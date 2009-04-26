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

class AdminForumsMerge extends AdminForm {

private $source = 0;
private $target = 0;

protected function setForm()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$this->setTitle('Foren zusammenlegen');
	$this->add(new SubmitButtonElement('Zusammenlegen'));

	try
		{
		$forums = $this->DB->getRowSet
			('
			SELECT
				id,
				name,
				(SELECT name FROM boards WHERE id = forums.boardid) AS board
			FROM
				forums
			ORDER BY
				board ASC
			');

		$inputRadioSource = new RadioInputElement('source', 'Quelle');
		$inputRadioTarget = new RadioInputElement('target', 'Ziel');
		foreach ($forums as $forum)
			{
			$inputRadioSource->addOption('<strong>'.$forum['board'].'</strong> '.$forum['name'], $forum['id']);
			$inputRadioTarget->addOption('<strong>'.$forum['board'].'</strong> '.$forum['name'], $forum['id']);
			}
		$this->add($inputRadioSource);
		$this->add($inputRadioTarget);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function checkForm()
	{
	$this->source = $this->Input->Post->getInt('source');
	$this->target = $this->Input->Post->getInt('target');
	if ($this->source == $this->target)
		{
		$this->showWarning('Quell- und Ziel-Forum sind identisch!');
		}
	}

protected function sendForm()
	{
	set_time_limit(0);
	$this->DB->execute('LOCK TABLES
				posts WRITE,
				threads WRITE,
				forum_cat WRITE,
				forums WRITE
			');

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			forumid = ?
		WHERE
			forumid = ?'
		);
	$stm->bindInteger($this->target);
	$stm->bindInteger($this->source);
	$stm->execute();
	$stm->close();

	AdminFunctions::delForum($this->source);
	$this->DB->execute('UNLOCK TABLES');

	AdminFunctions::updateForum($this->target);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminGlobalSettings');
	}

}

?>