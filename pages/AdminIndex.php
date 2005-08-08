<?php


class AdminIndex extends AdminPage{


public function prepare()
	{
	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Administration
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<h1>Willkommen zur Administration, '.$this->User->getName().'!</h1>
			<p>
			In diesem Bereich kannst Du Dein Board verwalten. Hier können neue Kategorien und Foren eingerichtet und verändert werden, aber auch das komplette Aussehen Deines Boards kannst Du hier mit wenigen Handgriffen anpassen.
			</p><p>
			Solltest Du Probleme mit einer Funktion haben, so konsultiere die Hilfe oder frage im Support-Forum nach.
			</p>
		</td>
	</tr>
	</table>
	';

	$this->setValue('title', 'Administration');
	$this->setValue('body', $body);
	}


}


?>