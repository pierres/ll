<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

abstract class ThreadList extends Page {

protected $resultSet = array();
protected $currentThread = 0;
protected $totalThreads = 0;
protected $pageOptions = array();
protected $target = 'Postings';


protected function setList()
	{
	$pages = $this->getPages();
	$fromThread = $this->currentThread + 1;
	$toThread = ($this->currentThread + $this->Settings->getValue('max_threads') > $this->totalThreads ? $this->totalThreads : $this->currentThread + $this->Settings->getValue('max_threads'));
	$count = sprintf($this->L10n->getText('Topic %d to %d of %d'), $fromThread, $toThread, $this->totalThreads);

	$body =
	'
	<table id="threads">
		<thead>
			<tr>
				<th colspan="2">'.$this->L10n->getText('Topic').'</th>
				<th>'.$this->L10n->getText('Replies').'</th>
				<th>'.$this->L10n->getText('Last post').'</th>
			</tr>
			<tr>
				<td colspan="2">
					'.$pages.'
				</td>
				<td class="thread-count" colspan="2">
					'.$count.'
				</td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					'.$pages.'
				</td>
				<td class="thread-count" colspan="2">
					'.$count.'
				</td>
			</tr>
		</tfoot>
		<tbody>
			'.$this->listThreads().'
		</tbody>
	</table>
	';

	$this->setBody($body);
	}

private function listThreads()
	{
	$threads = '';

	foreach ($this->resultSet as $data)
		{
// 		$thread_pages = '';
// 		for ($i = 0; $i < ($data['posts'] / $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
// 			{
// 			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
// 				{
// 				$thread_pages .= ' ... ';
// 				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
// 				continue;
// 				}
// 
// 			$thread_pages .= ' <a href="'.$this->Output->createUrl($this->target, array('threads' => $data['id'], 'post' => ($this->Settings->getValue('max_posts') * $i))).'">'.($i+1).'</a>';
// 			}
// 
// 		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		$data['name'] = cutString($data['name'], 80);

// 		if($data['deleted'] == 1)
// 			{
// 			$data['name'] = '<span class="deletedthread">'.$data['name'].'</span>';
// 			}
//
// 		/** FIXME: Schlecht, wenn Thread in anderes Board verschoben wurde */
// 		if ($data['forumid'] != $this->forum)
// 			{
// 			$data['name'] = '<span class="movedthread">'.$data['name'].'</span>';
// 			}

// 		$status .= (!empty($data['closed']) ? '<span class="closed"></span>' : '');
// 		$status .= (!empty($data['sticky']) ? '<span class="sticky"></span>' : '');

		if ($this->User->isOnline() && $this->Log->isNew($data['id'], $data['lastdate']))
			{
			$status = 'new';
			}
		else
			{
			$status = 'old';
			}

		$threads .=
			'
			<tr>
				<th class="thread-status">
					<span class="status-'.$status.'"></span>
				</th>
				<td class="thread-main">
					<a class="thread-title" href="'.$this->Output->createUrl($this->target, array('thread' => $data['id'])).'">'.$data['name'].'</a>
					<div class="thread-summary">
						'.$data['summary'].'
					</div>
					<div>von '.$data['firstusername'].'</div>
				</td>
				<td class="thread-posts">
					'.$data['posts'].'
				</td>
				<td class="thread-lastpost">
					<div><a href="'.$this->Output->createUrl($this->target, array('thread' => $data['id'], 'post' => '-1')).'">'.$this->L10n->getDateTime($data['lastdate']).'</a></div>
					<div>von '.$data['lastusername'].'</div>
				</td>
			</tr>
			';
		}

	return $threads;
	}


private function getPages()
	{
	$pages = '';

	if ($this->currentThread > ($this->Settings->getValue('max_threads')))
		{
		$pages .= '<a href="'.$this->Output->createUrl($this->getName(), $this->pageOptions).'">&laquo;</a>';
		}

	if ($this->currentThread > 0)
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => nat($this->currentThread-$this->Settings->getValue('max_threads'))))).'">&lsaquo;</a>';
		}

	for ($i = 0; $i < ($this->totalThreads / $this->Settings->getValue('max_threads')) && ($this->totalThreads / $this->Settings->getValue('max_threads')) > 1; $i++)
		{
		if ($this->currentThread < $this->Settings->getValue('max_threads') * ($i-4))
			{
			$i = $this->Settings->getValue('max_threads') * ($i-4);
			continue;
			}
		elseif($this->currentThread > $this->Settings->getValue('max_threads') * ($i+4))
			{
			continue;
			}

		if ($this->currentThread == ($this->Settings->getValue('max_threads') * $i))
			{
			$pages .= ' <strong class="current-page">'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => $this->Settings->getValue('max_threads') * $i))).'">'.($i+1).'</a>';
			}
		}

	if ($this->totalThreads > $this->Settings->getValue('max_threads')+$this->currentThread)
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => $this->Settings->getValue('max_posts')+$this->currentThread))).'">&rsaquo;</a>';
		}

	$lastpage = $this->Settings->getValue('max_threads') *nat($this->totalThreads / $this->Settings->getValue('max_threads'));

	if ($this->currentThread < $lastpage-$this->Settings->getValue('max_threads'))
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => $lastpage))).'">&raquo;</a>';
		}

	return $pages;
	}

}

?>
