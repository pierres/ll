<?php


class DomainBlacklist extends Page{



public function prepare()
	{
	$this->setValue('title', 'Gesperrte Domains');
	$stat = $this->getStat();

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Gesperrte Domains
				</td>
			</tr>
			<tr>
				<td class="main" style="width:950px;">
					<p>Leider treten in letzter Zeit vermehrt Probleme mit Werbung auf, die automatisiert in Foren, Wikis und Gästebüchern veröffentlicht wird. Um diesem <a href="http://de.wikipedia.org/wiki/Spam#Wiki-.2C_Link-_und_Blogspam" class="extlink" onclick="return !window.open(this.href);" rel="nofollow">Spam</a> entgegen zu wirken, werden wir Domains, für die Werbung gemacht wird sperren.</p>
					<p>Bisher wurden '.$stat['counts'].' Spam-Versuche für '.$stat['domains'].' Domains blockiert. Sollte eine Domain fälschlicherweise auf dieser Liste stehen, so <a href="?page=Contact;id='.$this->Board->getId().'" class="link">teile uns dies bitte mit</a>.</p>
					'.$this->getDomainBlacklist().'
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}

private function getStat()
	{
	try
		{
		$stat = $this->DB->getRow
			('
			SELECT
				COUNT(*) as domains,
				SUM(counter) as counts
			FROM
				domain_blacklist
			');
		}
	catch (DBNoDataException $e)
		{
		$stat = array('domains'=>'', ''=>'counts');
		}

	return $stat;
	}

private function getDomainBlacklist()
	{
	if ($this->User->isOnline())
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

		$list = '<table style="margin:10px;width:900px;">
				<tr>
					<td style="padding-bottom:5px;padding-right:200px;;"><strong>Domain</strong></td>
					<td style="text-align:right;padding-bottom:5px;"><strong>Spam-Versuche</strong></td>
					<td style="text-align:right;padding-bottom:5px;padding-right:100px;"><strong>eingefügt</strong></td>
					<td style="text-align:right;padding-bottom:5px;"><strong>zuletzt blockiert</strong></td>
				</tr>';
		foreach ($domains as $domain)
			{
			$list .= '<tr><td style="padding-right:200px;">'.$domain['domain'].'</td>
					<td style="text-align:center;">'.$domain['counter'].'</td>
					<td style="text-align:right;padding-right:100px;">'.formatDate($domain['inserted']).'</td>
					<td style="text-align:right;">'.formatDate($domain['lastmatch']).'</td></tr>';
			}

		$list .= '</table>';
		}
	else
		{
		$list = '<p>Um die Liste der gesperrten Domains hier einzusehen, muß Du Dich <a href="?page=Register;id='.$this->Board->getId().'" class="link">registrieren</a> und <a href="?page=Login;id='.$this->Board->getId().'" class="link">anmelden</a>.';
		}

	return $list;
	}


}


?>