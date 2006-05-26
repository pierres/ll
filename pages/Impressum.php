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
					<a href="?page=ShowUser;id='.$this->Board->getId().';user=486" class="link">Pierre Schmitz</a>
					<p>
					Clemens-August-Straße 76<br />
					53115 Bonn<br /><br />
					<table>
						<tr>
							<td style="padding-right:20px;">E-Mail</td>
							<td><a href="?page=Contact;id='.$this->Board->getId().'" class="link">pschmitz&#64;laber-land.de</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;">Telefon</td>
							<td>0228&nbsp;9716608</td>
						</tr>
						<tr>
							<td style="padding-right:20px;">Mobil</td>
							<td>0160&nbsp;95269831</td>
						</tr>
						<tr>
							<td style="padding-right:20px;">Jabber</td>
							<td>pierre&#64;jabber.laber-land.de</td>
						</tr>
					</table>
					</p>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>