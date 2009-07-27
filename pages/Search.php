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

class Search extends Form {

private $search = '';
private $searchId = 0;
private $ttl = 10800;

protected function setForm()
	{
	$this->setTitle($this->L10n->getText('Search'));

	$this->add(new SubmitButtonElement($this->L10n->getText('Submit')));

	$searchInput = new TextInputElement('search', $this->Input->Get->getHtml('search', ''), $this->L10n->getText('Search'));
	$searchInput->setMinLength(3);
	$searchInput->setMaxLength(100);
	$searchInput->setSize(100);
	$searchInput->setFocus();
	$this->add($searchInput);
	}

protected function checkForm()
	{
	$this->search = $this->Input->Post->getHtml('search');

	$this->storeResult();
	}

private function storeResult()
	{
	$stm = $this->DB->prepare
		('
		DELETE
			search,
			search_threads
		FROM
			search,
			search_threads
		WHERE
			search.expires < ?
			AND search.id = search_threads.searchid
		');
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				search
			WHERE
				query = ?
			');
		$stm->bindString($this->search);
		$this->searchId = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				search
			SET
				query = ?,
				expires = ?
			');
		$stm->bindString($this->search);
		$stm->bindInteger($this->Input->getTime() + $this->ttl);
		$stm->execute();
		$this->searchId = $this->DB->getInsertId();
		$stm->close();

		$stm = $this->DB->prepare
			('
			INSERT INTO
				search_threads
				(
				SELECT
					LAST_INSERT_ID(),
					threads.id,
					(
						MATCH (threads.name) AGAINST (? IN BOOLEAN MODE)
						* (threads.lastdate + threads.firstdate)
					)
				FROM
					threads,
					forums
				WHERE MATCH
					(threads.name) AGAINST (? IN BOOLEAN MODE)
					AND threads.forumid = forums.id
					AND threads.deleted = 0
					AND forums.boardid = ?
				)
				UNION
				(
				SELECT
					LAST_INSERT_ID(),
					threads.id,
					(
						MATCH (posts.text) AGAINST (? IN BOOLEAN MODE)
						* (threads.lastdate + threads.firstdate)
					)
				FROM
					posts,
					threads,
					forums
				WHERE MATCH
					(posts.text) AGAINST (? IN BOOLEAN MODE)
					AND posts.threadid = threads.id
					AND threads.forumid = forums.id
					AND threads.deleted = 0
					AND posts.deleted = 0
					AND forums.boardid = ?
					GROUP BY threads.id
				)
				LIMIT 500
			');
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindInteger($this->Board->getId());
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}
	}

protected function sendForm()
	{
 	$this->Output->redirect('SearchResults', array('searchId' => $this->searchId));
	}

}

?>