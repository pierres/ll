<?php


class ThreadList extends Modul{


public function getList($resultset)
	{
	$threads = '';

	foreach ($resultset as $data)
		{
		if($data['forumid'] == 0)
			{
			$target = 'PrivatePostings';
			$forum = '<a href="?page=PrivateThreads;id='.$this->Board->getId().'">Private Themen</a>';
			}
		else
			{
			$target = 'Postings';
			$forum = '<a href="?page=Threads;forum='.$data['forumid'].';id='.$this->Board->getId().'">'.$data['forumname'].'</a>';
			}


		$thread_pages = '';
		for ($i = 0; $i < ($data['posts'] / $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
				continue;
				}

			$thread_pages .= ' <a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].';post='.($this->Settings->getValue('max_posts') * $i).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		$data['name'] = cutString($data['name'], 80);

		if ($this->User->isOnline() && $this->Log->isNew($data['id'], $data['lastdate']))
			{
			$data['name'] = '<span class="newthread">'.$data['name'].'</span>';
			}

		$status = (!empty($data['poll'])    ? '<span class="poll"></span>' : '');
		$status .= (!empty($data['closed']) ? '<span class="closed"></span>' : '');
		$status .= (!empty($data['sticky']) ? '<span class="sticky"></span>' : '');


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
					<a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].'">'.$data['name'].'</a>
					</div>
					<div class="threadpages">
					'.$thread_pages.'
					</div>
				</td>
				<td class="lastpost">
					<script type="text/javascript">
						<!--
						document.write("<div class=\"summary\" style=\"visibility:hidden;\" id=\"summary'.$data['id'].'\">'.$data['summary'].'<\/div>");
						-->
					</script>
					<div>von '.$firstposter.'</div>
					<div>'.$data['firstdate'].'</div>
				</td>
				<td class="countcol">
					'.$data['posts'].'
				</td>
				<td class="lastpost">
					<div>von '.$lastposter.'</div>
					<div><a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].';post=-1">'.$data['lastdate'].'</a></div>
				</td>
				<td class="forums">
					'.$forum.'
				</td>
			</tr>
			';
		}

	return $threads;
	}

}

?>