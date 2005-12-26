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
					Wülscheider Kirchweg 8<br />
					53604 Bad Honnef<br /><br />
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