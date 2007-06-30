<?php


class AdminGlobalSettings extends AdminPage{


public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Globale Einstellungen
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<ul>
				<li style="margin:20px;">
				<a href="?page=DeletedThreads;id='.$this->Board->getId().'"><span class="button">Gelöschte Themen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=DeletedPosts;id='.$this->Board->getId().'"><span class="button">Gelöschte Beiträge</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=RenameUser;id='.$this->Board->getId().'"><span class="button">Benutzer umbenennen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=DelBoard;id='.$this->Board->getId().'"><span class="button">Board löschen</span></a>
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminForumsMerge;id='.$this->Board->getId().'"><span class="button">Foren zusammenlegen</span></a>
				</li>
			</ul>
		</td>
	</tr>
	</table>
	';

	$this->setValue('title', 'Globale Einstellungen');
	$this->setValue('body', $body);
	}


}


?>