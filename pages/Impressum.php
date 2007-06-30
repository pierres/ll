<?php


class Impressum extends Page{


public function prepare()
	{
	$this->setValue('title', 'Impressum');

	$stm = $this->DB->prepare
		('
		SELECT
			admin_name,
			admin_email,
			admin_address,
			admin_tel,
			description
		FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$board = $stm->getRow();
	$stm->close();

	$body = '
		<table class="frame" style="width:50%;">
			<tr>
				<td class="title">
					Impressum
				</td>
			</tr>
			<tr>
				<td class="main">
					<table style="margin:10px;padding:20px;width:80%;">
						<tr>
							<td colspan="2" style="padding:10px;font-weight:bold;font-size:14px;text-align:center;">Inhaltlich verantwortlich</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-bottom:10px;padding-right:20px;font-weight:bold;"><a href="?page=ShowUser;id='.$this->Board->getId().';user='.$this->Board->getAdmin().'" class="link">'.$board['admin_name'].'</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;vertical-align:top;font-weight:bold;">Adresse</td>
							<td>'.$board['admin_address'].'</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">E-Mail</td>
							<td><a href="mailto:'.$board['admin_email'].'" class="link">'.$board['admin_email'].'</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Telefon</td>
							<td>'.$board['admin_tel'].'</td>
						</tr>

						<tr>
							<td colspan="2" style="padding:10px">'.$board['description'].'</td>
						</tr>
					
						<tr>
							<td colspan="2" style="padding:10px;font-weight:bold;font-size:14px;text-align:center;">Technik</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-bottom:10px;padding-right:20px;font-weight:bold;"><a href="?page=ShowUser;id='.$this->Board->getId().';user=486" class="link">Pierre Schmitz</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;vertical-align:top;font-weight:bold;">Adresse</td>
							<td>Clemens-August-Straße 76<br />
							53115 Bonn</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">E-Mail</td>
							<td><a href="?page=Contact;id='.$this->Board->getId().'" class="link">pschmitz&#64;laber-land.de</a></td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Telefon</td>
							<td>0228&nbsp;9716608</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Mobil</td>
							<td>0160&nbsp;95269831</td>
						</tr>
						<tr>
							<td style="padding-right:20px;font-weight:bold;">Jabber</td>
							<td>pierre&#64;jabber.laber-land.de</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>