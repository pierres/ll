<?php


class SpamLog extends Page{



public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showWarning('kein Zutritt');
		}

	$this->setValue('title', 'Spam-Versuche');

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Spam-Versuche
				</td>
			</tr>
			<tr>
				<td class="main" style="width:900px;">
					<p>Leider treten in letzter Zeit vermehrt Probleme mit Werbung auf, die automatisiert in Foren, Wikis und Gästebüchern veröffentlicht wird. Um diesem <a href="http://de.wikipedia.org/wiki/Spam#Wiki-.2C_Link-_und_Blogspam" class="extlink" onclick="return !window.open(this.href);" rel="nofollow">Spam</a> entgegen zu wirken, werden wir Domains, für die Werbung gemacht wird sperren.</p>
					<p>Bisher wurden '.$this->getStat().' Spam-Versuche blockiert.</p>
					'.$this->getSpamLog().'
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
		$count = $this->DB->getColumn
			('
			SELECT
				COUNT(*)
			FROM
				spam_log
			');
		}
	catch (DBNoDataException $e)
		{
		$count = 0;
		}

	return $count;
	}

private function getSpamLog()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$spams = $this->DB->getRowSet
				('
				SELECT
					ip,
					`time`
				FROM
					spam_log
				ORDER BY
					`time` DESC
				');
			}
		catch (DBNoDataException $e)
			{
			$spams = array();
			}

		$list = '<table style="margin:10px;">
				<tr>
					<td style="padding-bottom:5px;width:300px;"><strong>IP</strong></td>
					<td style="text-align:right;padding-bottom:5px;width:200px;"><strong>zuletzt blockiert</strong></td>
				</tr>';
		foreach ($spams as $spam)
			{
			$list .= '<tr><td><a href="?page=SpamLogEntry;id='.$this->Board->getId().';ip='.$spam['ip'].';time='.$spam['time'].'" class="link">'.$spam['ip'].'</a></td>
				<td style="text-align:right;">'.formatDate($spam['time']).'</td></tr>';
			}

		$list .= '</table>';
		}
	else
		{
		$list = '<p>Um die Liste der blockierten IPs hier einzusehen, muß Du Dich <a href="?page=Register;id='.$this->Board->getId().'" class="link">registrieren</a> und <a href="?page=Login;id='.$this->Board->getId().'" class="link">anmelden</a>.';
		}

	return $list;
	}


}


?>