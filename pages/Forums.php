<?php


class Forums extends Page{


public function prepare(){


try
	{
	$result = $this->Sql->fetch
		('
		SELECT
			cats.id AS catid,
			cats.name AS catname,
			forums.id,
			forums.boardid,
			forums.name,
			forums.description,
			forums.lastthread,
			forums.threads,
			forums.posts,
			threads.lastusername,
			threads.lastuserid,
			threads.lastdate,
			threads.name AS threadname
		FROM
			cats,
			forums
				LEFT JOIN threads
				ON forums.lastthread = threads.id,
			forum_cat
		WHERE
			cats.boardid = '.$this->Board->getId().'
			AND forum_cat.forumid = forums.id
			AND forum_cat.catid = cats.id
		ORDER BY
			cats.position,
			forum_cat.position
		');
	}
catch (SqlNoDataException $e)
	{
	$result = array();
	}

$cat 		= 0;
$catheader 	= '';
$forums 	= '';

foreach ($result as $data)
	{
	if ($cat != $data['catid'])
		{
		$catheader =
			'
			<tr>
				<td class="cat" colspan="5">
					<a class="catname" id="cat'.$data['catid'].'">&#171; '.$data['catname'].' &#187;</a>
				</td>
			</tr>
			';
		}
	else
		{
		$catheader = '';
		}

	if ($data['boardid'] == $this->Board->getId())
		{
		$new = '<span class="new"></span>';
		$old = '<span class="old"></span>';
		}
	else
		{
		$new = '<span class="newex"></span>';
		$old = '<span class="oldex"></span>';
		}

if ($this->User->isOnline())
	{
	$icon = ($this->Log->isNew($data['lastthread'], $data['lastdate']) ? '<a href="?page=MarkAsRead;id='.$this->Board->getId().';forum='.$data['id'].'">'.$new.'</a>' : $old);
	}
else
	{
	$icon = $old;
	}

	$data['lastdate'] = formatDate($data['lastdate']);

	$lastposter =
		(empty($data['lastuserid'])
		? (empty($data['lastusername']) ? '' : 'von '.$data['lastusername'])
		: 'von <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['lastuserid'].'">'.$data['lastusername'].'</a>'
		);

	$forums .= $catheader.
			'
			<tr>
				<td class="iconcol">
					'.$icon.'
				</td>
				<td class="forumcol">
					<div class="forumtitle"><a href="?page=Threads;id='.$this->Board->getId().';forum='.$data['id'].'">'.$data['name'].'</a></div>
					<div class="forumdescr">'.$data['description'].'</div>
				</td>
				<td class="countcol">
					'.$data['threads'].'
				</td>
				<td class="countcol">
					'.$data['posts'].'
				</td>
				<td class="lastpost">
					<div><a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['lastthread'].';post=-1">'.cutString($data['threadname'], 20).'</a></div>
					<div>'.$lastposter.'</div>
					<div>'.$data['lastdate'].'</div>
				</td>
			</tr>
		';

	$cat = $data['catid'];
	}

$online = array();
foreach($this->User->getOnline() as $user)
	{
	$online[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$user['id'].'">'.$user['name'].'</a>';
	}
$online = implode(', ', $online);

$body =
	'
	<table class="frame" style="width:100%">
		<tr>
			<td colspan="2" class="title">
				Forum
			</td>
			<td class="title">
				Themen
			</td>
			<td class="title">
				Beiträge
			</td>
			<td class="title">
				Letzter Beitrag
			</td>
		</tr>
		'.$forums.'
		<tr>
			<td colspan="5" class="cat">
				Wer ist auch hier?
			</td>
		</tr>
		<tr>
			<td class="iconcol">
				&nbsp;
			</td>
			<td colspan="4" class="forumcol">
				<div class="forumdescr">'.$online.'</div>
			</td>
		</tr>
	</table>
	';

$this->setValue('title', $this->Board->getName());
$this->setValue('body', $body);
}
}


?>