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

class Poll extends Form {

private $thread = 0;
private $target = 'Postings';


protected function setForm()
	{
	try
		{
		$this->thread = $this->Input->Get->getInt('thread');
		$this->target = $this->Input->Get->getString('target') == 'PrivatePostings' ? 'PrivatePostings' : 'Postings';
		}
	catch (RequestException $e)
		{
		$this->showFailure($this->L10n->getText('No topic specified.'));
		}

	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder');
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
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$forum = $stm->getColumn();
		$stm->close();

		if ($forum == 0)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					userid
				FROM
					thread_user
				WHERE
					userid = ?
					AND threadid = ?'
				);
			$stm->bindInteger($this->User->getId());
			$stm->bindInteger($this->thread);
			$stm->getColumn();
			$stm->close();
			}

		$stm = $this->DB->prepare
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$question = $stm->getColumn();
		$stm->close();

		$stm = $this->DB->prepare
			('
			SELECT
				value,
				id
			FROM
				poll_values
			WHERE
				pollid = ?
			ORDER BY
				id ASC
			');
		$stm->bindInteger($this->thread);

		$inputRadio = new RadioInputElement('option', 'Optionen');
		foreach ($stm->getRowSet() as $option)
			{
			$inputRadio->addOption($option['value'], $option['id']);
			}
		$this->add($inputRadio);
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->setValue('meta.robots', 'noindex,nofollow');
		$this->showWarning('Keine Umfrage gefunden.');
		}

	$this->setTitle($question);
	$this->add(new SubmitButtonElement('Abstimmen'));

	$this->setParam('thread', $this->thread);
	$this->setParam('target', $this->target);
	}

protected function checkForm()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				poll_voters
			WHERE
				pollid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		$this->showWarning('Du hast bereits abgestimmt!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			poll_voters
		SET
			pollid = ?,
			userid = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			poll_values
		SET
			votes = votes + 1
		WHERE
			id = ?
			AND pollid = ?'
		);
	$stm->bindInteger($this->Input->Post->getInt('option'));
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect($this->target, array('thread' => $this->thread));
	}

}

?>