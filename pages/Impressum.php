<?php


class Impressum extends Page{


public function prepare()
	{
	$this->setValue('title', 'Impressum');

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Impressum
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=ShowUser;id=1;user=486">Pierre Schmitz</a>
					<p>
					Rheinweg 88<br />
					53129 Bonn<br /><br />
					Telefon: 0228 1802169<br />
					<a href="?page=Contact;id=1">E-Mail</a>
					</p>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>