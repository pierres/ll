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

abstract class ThreadList extends Page {

protected $pageHead = '';
protected $pageFoot = '';
protected $mainFoot = '';
protected $resultSet = array();
protected $currentThread = 0;
protected $totalThreads = 0;
protected $pageOptions = array();
protected $target = 'Postings';


protected function getBody()
	{
	$pages = $this->getPages();

	$body =
		'<div id="brd-main" class="main paged">

			<div class="paged-head">
				<p class="paging"><span class="pages">'.$this->L10n->getText('Pages').':</span> '.$pages.'</p>
				'.$this->pageHead.'
			</div>

			<div class="main-head">
				<h2><span>'.$this->getTitle().'</span></h2>
				<p class="main-options">Thema '.($this->currentThread+1).' bis '.($this->currentThread+$this->Settings->getValue('max_threads')).' von '.$this->totalThreads.'</p>
			</div>

			<script type="text/javascript">
				/* <![CDATA[ */
				function writeText(text)
					{
					var pos;
					pos = document;
					while ( pos.lastChild && pos.lastChild.nodeType == 1 )
						pos = pos.lastChild;
					pos.parentNode.appendChild( document.createTextNode(text));
					}
				/* ]]> */
			</script>

			<div id="forum1" class="main-content forum">
				<table cellspacing="0">
					<thead>
						<tr>
							<th class="tcl" scope="col">'.$this->L10n->getText('Topic').'</th>
							<th class="tc2" scope="col">'.$this->L10n->getText('Replies').'</th>
							<th class="tcr" scope="col">'.$this->L10n->getText('Last post').'</th>
						</tr>
					</thead>
					<tbody class="statused">
						'.$this->listThreads().'
					</tbody>
				</table>
			</div>

			<div class="main-foot">
				'.$this->mainFoot.'
			</div>

			<div class="paged-foot">
				'.$this->pageFoot.'
				<p class="paging"><span class="pages">'.$this->L10n->getText('Pages').':</span> '.$pages.'</p>
			</div>

		</div>
		';

	return $body;
	}

private function listThreads()
	{
	$threads = '';
	$threadcount = 1;

	foreach ($this->resultSet as $data)
		{
		$thread_pages = '';
		for ($i = 0; $i < ($data['posts'] / $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
				continue;
				}

			$thread_pages .= ' <a href="'.$this->Output->createUrl($this->target, array('threads' => $data['id'], 'post' => ($this->Settings->getValue('max_posts') * $i))).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


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

		$position = ($threadcount % 2 == 0 ? 'even' : 'odd');

		if ($this->User->isOnline() && $this->Log->isNew($data['id'], $data['lastdate']))
			{
			$status = 'new';
			$icon = '<span class="status '.$status.'" title="Topic containing new posts since your last visit."><img src="images/status.png" alt="Topic" /></span>';
			}
		else
			{
			$status = 'normal';
			$icon = '<span class="status '.$status.'" title="Forum"><img src="images/status.png" alt="Forum" /></span>';
			}

		$data['lastdate'] = $this->L10n->getDateTime($data['lastdate']);
		$data['firstdate'] = $this->L10n->getDateTime($data['firstdate']);

		$threads .=
			'
				<tr class="'.$position.' normal">

					<td class="tcl"
					 onmouseover="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'visible\'"
					 onmouseout="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'hidden\'">'.$icon.' <a href="'.$this->Output->createUrl($this->target, array('thread' => $data['id'])).'">'.$data['name'].'</a> <span class="byuser">by&#160;'.$data['firstusername'].'</span></td>
					<td class="tc2">
					<div class="summary" style="visibility:hidden;" id="summary'.$data['id'].'">
						<script type="text/javascript">
							/* <![CDATA[ */
							writeText("'.$data['summary'].'");
							/* ]]> */
						</script>
					</div>
					'.$this->L10n->getNumber($data['posts']).'</td>
					<td class="tcr"><a href="'.$this->Output->createUrl($this->target, array('thread' => $data['id'], 'post' => '-1')).'"><span>'.$data['lastdate'].'</span></a> <span class="byuser">'.$this->L10n->getText('by').' '.$data['lastusername'].'</span></td>

				</tr>
			';

		$threadcount++;
		}

	return $threads;
	}


private function getPages()
	{
	$pages = '';
	$firstitem = ' class="item1"';

	if ($this->currentThread > ($this->Settings->getValue('max_threads')))
		{
		$pages .= '<a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), $this->pageOptions).'">&laquo;</a>';
		$firstitem = '';
		}

	if ($this->currentThread > 0)
		{
		$pages .= ' <a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => nat($this->currentThread-$this->Settings->getValue('max_threads'))))).'">&lsaquo;</a>';
		$firstitem = '';
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
			$pages .= ' <strong'.$firstitem.'>'.($i+1).'</strong>';
			$firstitem = '';
			}
		else
			{
			$pages .= ' <a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), array_merge($this->pageOptions, array('thread' => $this->Settings->getValue('max_threads') * $i))).'">'.($i+1).'</a>';
			$firstitem = '';
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