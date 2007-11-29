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
class PrivateThreads extends Page{


protected $thread 	= 0;
protected $threads 	= 0;
protected $result 		= array();

public function prepare(){

if (!$this->User->isOnline())
	{
	$this->showWarning('Nur für Mitglieder');
	}

try
	{
	$this->thread = nat($this->Io->getInt('thread'));
	}
catch (IoRequestException $e)
	{
	$this->thread = 0;
	}


$limit = $this->thread.','. $this->Settings->getValue('max_threads');

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			threads,
			thread_user
		WHERE
			threads.forumid = 0
			AND thread_user.threadid = threads.id
			AND thread_user.userid = ?'
		);
	$stm->bindInteger($this->User->getId());
	$this->threads = $stm->getColumn();
	$stm->close();
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$this->threads = 0;
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			threads.id,
			threads.poll,
			threads.name,
			threads.lastdate,
			threads.lastuserid,
			threads.lastusername,
			threads.firstdate,
			threads.firstuserid,
			threads.firstusername,
			threads.posts,
			threads.deleted,
			summary
		FROM
			threads,
			thread_user
		WHERE
			threads.forumid = 0
			AND thread_user.threadid = threads.id
			AND thread_user.userid = ?
			AND
				((thread_user.userid = threads.firstuserid
				AND threads.deleted = 1)
				OR threads.deleted = 0)
		ORDER BY
			lastdate DESC
		LIMIT
			'.$limit
		);
	$stm->bindInteger($this->User->getId());
	$this->result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$this->result = array();
	}

$pages = $this->getPages();


$threads = $this->listThreads();
$stm->close();

$body =
	'<script type="text/javascript">
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
	<table class="frame" style="width:100%">
		<tr>
			<td class="title" colspan="2">Thema</td>
			<td class="title">Erster Beitrag</td>
			<td class="title">Beiträge</td>
			<td class="title">Letzter Beitrag</td>
		</tr>
		<tr>
			<td class="path" colspan="5">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<strong>Private Themen</strong>
			</td>
		</tr>
		<tr>
			<td class="pages" colspan="4">'.$pages.'</td>
			<td class="pages">
			<a href="?page=NewPrivateThread;id='.$this->Board->getId().'"><span class="button">Neues Thema</span></a>
			</td>
		</tr>
		'.$threads.'
		<tr>
			<td class="pages" colspan="4">'.$pages.'</td>
			<td class="pages">
			<a href="?page=NewPrivateThread;id='.$this->Board->getId().'"><span class="button">Neues Thema</span></a>
			</td>
		</tr>
		<tr>
			<td class="path" colspan="5">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<strong>Private Themen</strong>
			</td>
		</tr>
	</table>
	';

$this->setValue('title', 'Private Themen');
$this->setValue('body', $body);
}

protected function listThreads()
	{
	$threads = '';

	foreach ($this->result as $data)
		{
		$thread_pages = '';
		for ($i = 0; $i < ($data['posts'] /  $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
				continue;
				}

			$thread_pages .= ' <a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$data['id'].';post='.($this->Settings->getValue('max_posts') * $i).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		if ($this->Log->isNew($data['id'], $data['lastdate']))
			{
			$data['name'] = '<span class="newthread">neu</span>'.$data['name'];
			}
	
		if($data['deleted'] == 1)
			{
			$data['name'] = '<span class="deletedthread">'.$data['name'].'</span>';
			}

		$status = (!empty($data['poll'])    ? '<span class="poll"></span>' : '');

		$lastposter = (empty($data['lastuserid'])
			? $data['lastusername']
			: '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['lastuserid'].'">'.$data['lastusername'].'</a>');

		$firstposter = (empty($data['firstuserid'])
			? $data['firstusername']
			: '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['firstuserid'].'">'.$data['firstusername'].'</a>');

		$data['lastdate'] = formatDate($data['lastdate']);
		$data['firstdate'] = formatDate($data['firstdate']);

		$threads .=
			'
			<tr>
				<td class="threadiconcol">
					'.$status.'
				</td>
				<td class="forumcol"
					 onmouseover="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'visible\'"
					 onmouseout="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'hidden\'">
					<div class="thread">
					<a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$data['id'].'">'.$data['name'].'</a>
					</div>
					<div class="threadpages">
					'.$thread_pages.'
					</div>
				</td>
				<td class="lastpost">
					<div class="summary" style="visibility:hidden;" id="summary'.$data['id'].'">
						<script type="text/javascript">
							/* <![CDATA[ */
							writeText("'.$data['summary'].'");
							/* ]]> */
						</script>
					</div>
					<div>von '.$firstposter.'</div>
					<div>'.$data['firstdate'].'</div>
				</td>
				<td class="countcol">
					'.$data['posts'].'
				</td>
				<td class="lastpost">
					<div>von '.$lastposter.'</div>
					<div><a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$data['id'].';post=-1">'.$data['lastdate'].'</a></div>
				</td>
			</tr>
			';
			}

	return $threads;
	}

protected function getPages()
	{
	$pages = '';

	if ($this->thread > ($this->Settings->getValue('max_threads')))
		{
		$pages .= '<a href="?page='.$this->getName().';id='.$this->Board->getId().'">&laquo;</a>';
		}

	if ($this->thread > 0)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).'">&lsaquo;</a>';
		}

	for ($i = 0; $i < ($this->threads / $this->Settings->getValue('max_threads')) && ($this->threads / $this->Settings->getValue('max_threads')) > 1; $i++)
		{
		if ($this->thread < $this->Settings->getValue('max_threads') * ($i-4))
			{
			$i = $this->Settings->getValue('max_threads') * ($i-4);
			continue;
			}
		elseif($this->thread > $this->Settings->getValue('max_threads') * ($i+4))
			{
			continue;
			}

		if ($this->thread == ($this->Settings->getValue('max_threads') * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads') * $i).'">'.($i+1).'</a>';
			}
		}

	if ($this->threads > $this->Settings->getValue('max_threads')+$this->thread)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_posts')+$this->thread).'">&rsaquo;</a>';
		}

	$lastpage = $this->Settings->getValue('max_threads') *nat($this->threads / $this->Settings->getValue('max_threads'));

	if ($this->thread < $lastpage-$this->Settings->getValue('max_threads'))
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';thread='.$lastpage.'">&raquo;</a>';
		}

	return $pages;
	}

}

?>