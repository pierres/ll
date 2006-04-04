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
					Clemens-August-Straße 76<br />
					53115 Bonn<br /><br />
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