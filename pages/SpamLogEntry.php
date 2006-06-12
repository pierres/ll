<?php


class SpamLogEntry extends Page{



public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showWarning('kein Zutritt');
		}

	$this->setValue('title', 'Spam-Log-Eintrag');

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Spam-Versuche
				</td>
			</tr>
			<tr>
				<td class="main" style="width:900px;">
					'.$this->getSpamLogEntry().'
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}

private function getSpamLogEntry()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					ip,
					`time`,
					request,
					server
				FROM
					spam_log
				WHERE
					ip = ?
					AND `time` = ?
				');
			$stm->bindString($this->Io->getString('ip'));
			$stm->bindInteger($this->Io->getInt('time'));
			$data = $stm->getRow();
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$this->showWarning('Kein Eintrag gefunden');
			}
		catch (IORequestException $e)
			{
			$this->showWarning('Keine Parameter übergeben');
			}

		return '<table style="margin:10px;">
				<tr>
					<td style="padding-bottom:5px;"><strong>IP</strong></td>
					<td>'.$data['ip'].'</td>
				</tr>
				<tr>
					<td style="padding-bottom:5px;"><strong>Host</strong></td>
					<td>'.gethostbyaddr($data['ip']).'</td>
				</tr>
				<tr>
					<td style="padding-bottom:5px;"><strong>Zeit</strong></td>
					<td>'.formatDate($data['time']).'</td>
				</tr>
				<tr>
					<td style="padding-bottom:5px;"><strong>Request</strong></td>
					<td><pre>'.(!empty($data['request']) ? htmlspecialchars(print_r(unserialize(gzuncompress($data['request']))), true) : '').'</pre></td>
				</tr>
					<td style="padding-bottom:5px;"><strong>Server</strong></td>
					<td><pre>'.(!empty($data['request']) ? htmlspecialchars(print_r(unserialize(gzuncompress($data['server'])), true) : '').'</pre></td>
				</tr>
			</table>';
		}
	else
		{
		return '';
		}
	}


}


?>