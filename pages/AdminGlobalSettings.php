<?php


class AdminGlobalSettings extends AdminPage{


public function prepare()
	{
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
				<a href="?page=DomainBlacklist;id='.$this->Board->getId().'"><span class="button">Gesperrte Domains</span></a>
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