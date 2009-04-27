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

private $search 	= '';


public function prepare()
	{
	try
		{
		$this->search = $this->Input->Get->getHtml('search');
		$this->currentThread = $this->Input->Get->getInt('thread', 0);
		}
	catch (RequestException $e)
		{
		$this->showFailure($e->getMessage());
		}

	$this->pageHead = '<p class="posting"><a class="newpost" href="'.$this->Output->createUrl('Search').'"><span>'.$this->L10n->getText('New search').'</span></a></p>';
	$this->pageFoot = $this->pageHead;

	$this->setTitle(sprintf($this->L10n->getText('Search results for %s'), $this->search));

	if (!($this->resultSet = $this->PersistentCache->getObject('LL:Search:'.$this->Board->getId().'::'.$this->search)))
		{
		$this->showFailure($this->L10n->getText('No search results.'));
		}
	$this->totalThreads = count($this->resultSet);
	$this->resultSet = array_slice($this->resultSet, $this->currentThread, $this->Settings->getValue('max_threads'));

	$this->pageOptions = array('search' => $this->search);
	$this->setBody($this->getBody());
	}

}

?>