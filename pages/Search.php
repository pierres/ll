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

private $search 	= '';

protected function setForm()
	{
	$this->setTitle($this->L10n->getText('Search'));

	$this->add(new SubmitButtonElement($this->L10n->getText('Submit')));

	$searchInput = new TextInputElement('search', '', $this->L10n->getText('Search'));
	$searchInput->setMinLength(3);
	$searchInput->setMaxLength(50);
	$searchInput->setSize(50);
	$this->add($searchInput);
	}

protected function checkForm()
	{
	$this->search = $this->Input->Post->getHtml('search');

	$this->storeResult();
	}

private function storeResult()
	{
	try
		{
		if (!$this->PersistentCache->isObject('LL:Search:'.$this->Board->getId().'::'.$this->search))
			{
			$stm = $this->DB->prepare
			('
			(
				SELECT
					threads.id,
					threads.name,
					threads.lastdate,
					threads.posts,
					threads.lastusername,
					threads.firstdate,
					threads.firstusername,
					threads.closed,
					threads.sticky,
					threads.posts,
					(
						MATCH (threads.name) AGAINST (? IN BOOLEAN MODE)
						* (threads.lastdate + threads.firstdate)
					) AS score,
					threads.summary
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
					threads.id,
					threads.name,
					threads.lastdate,
					threads.posts,
					threads.lastusername,
					threads.firstdate,
					threads.firstusername,
					threads.closed,
					threads.sticky,
					threads.posts,
					(
						MATCH (posts.text) AGAINST (? IN BOOLEAN MODE)
						* (threads.lastdate + threads.firstdate)
					) AS score,
					threads.summary
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
			ORDER BY score DESC
			LIMIT 1000'
			);
			$stm->bindString($this->search);
			$stm->bindString($this->search);
			$stm->bindInteger($this->Board->getId());
			$stm->bindString($this->search);
			$stm->bindString($this->search);
			$stm->bindInteger($this->Board->getId());
			$result = $stm->getRowSet()->toArray();
			$this->PersistentCache->addObject('LL:Search:'.$this->Board->getId().'::'.$this->search, $result, 3*60*60);
			}
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning($this->L10n->getText('No search results.'));
		}
	}

protected function sendForm()
	{
 	$this->Output->redirect('SearchResults', array('search' => $this->search));
	}

}

?>