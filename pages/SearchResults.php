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

class SearchResults extends ThreadList {


public function prepare()
	{
	try
		{
		$searchId = $this->Input->Get->getInt('searchId');
		$this->currentThread = $this->Input->Get->getInt('thread', 0);
		}
	catch (RequestException $e)
		{
		$this->showFailure($e->getMessage());
		}
		
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				query,
				COUNT(search_threads.threadid) AS threads
			FROM
				search,
				search_threads
			WHERE
				search.id = ?
				AND search_threads.searchid = search.id
			');
		$stm->bindInteger($searchId);
		$data = $stm->getRow();
		$stm->close();
		$search = $data['query'];
		$this->totalThreads = $data['threads'];
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure($this->L10n->getText('No search results'));
		}
		

	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('Search', array('search' => $search)).'">'.$this->L10n->getText('Edit Search').'</a>');
	$this->setTitle(sprintf($this->L10n->getText('Search results for %s'), '&quot;'.$search.'&quot;'));
	
	try
		{
		$stm = $this->DB->prepare
			('
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
				threads.summary
			FROM
				threads,
				search_threads
			WHERE
				search_threads.searchid = ?
				AND search_threads.threadid = threads.id
			ORDER BY
				search_threads.score DESC
			LIMIT '.$this->currentThread.','.$this->Settings->getValue('max_threads')
			);
		$stm->bindInteger($searchId);
		$this->resultSet = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure($this->L10n->getText('No search results'));
		}

	$this->pageOptions = array('searchId' => $searchId);
	$this->setList();	
	$stm->close();
	}

}

?>