<?php


class DomainBlacklist extends Page{



public function prepare()
	{
	$this->setValue('title', 'Gesperrte Domains');

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Gesperrte Domains
				</td>
			</tr>
			<tr>
				<td class="main">
					<p>Folgende Domains wurden aufgrund von Spamming gesperrt.<br />Sollte eine Domain fälschlicherweise auf dieser Liste stehen, so <a href="?page=Contact;id='.$this->Board->getId().'" class="link">teile uns dies bitte mit</a>.</p>
					<table style="margin:10px;width:600px;">
						<tr>
							<td style="padding-bottom:5px;"><strong>Domain</strong></td>
							<td style="text-align:right;padding-bottom:5px;"><strong>Spam-Versuche</strong></td>
							<td style="text-align:right;padding-bottom:5px;"><strong>eingefügt</strong></td>
							<td style="text-align:right;padding-bottom:5px;"><strong>zuletzt blockiert</strong></td>
						</tr>
						'.$this->getDomainBlacklist().'
					</table>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}

private function getDomainBlacklist()
	{
	try
		{
		$domains = $this->DB->getRowSet
			('
			SELECT
				domain,
				counter,
				inserted,
				lastmatch
			FROM
				domain_blacklist
			ORDER BY
				lastmatch DESC
			');
		}
	catch (DBNoDataException $e)
		{
		$domains = array();
		}

	$list = '';
	foreach ($domains as $domain)
		{
		$list .= '<tr><td>'.$domain['domain'].'</td>
				<td style="text-align:right;">'.$domain['counter'].'</td>
				<td style="text-align:right;">'.formatDate($domain['inserted']).'</td>
				<td style="text-align:right;">'.formatDate($domain['lastmatch']).'</td></tr>';
		}

	return $list;
	}


}


?>