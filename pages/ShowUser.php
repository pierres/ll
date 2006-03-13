<?php


class ShowUser extends Page{

private $id = 0;


public function prepare()
	{
	try
		{
		$this->id = $this->Io->getInt('user');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('Kein Benutzer angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				realname,
				birthday,
				posts,
				regdate,
				gender,
				lastpost,
				avatar,
				location,
				plz,
				text
			FROM
				users
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$data = $stm->getRow();
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning('Kein Benutzer gefunden!');
		}

	$age = (!empty($data['birthday']) ? $this->calcAge($data['birthday']) : '');

	$now = time();
	$regdays = ($now - $data['regdate']) / 86400;

	$gender = (!empty($data['gender']) ? ($data['gender'] == 1 ? 'männlich' : 'weiblich') : '');

	$regdays = floor($regdays);
	$postsperday = round($data['posts'] / (($now - $data['regdate']) / 86400));
	$lastpostdays = ($data['lastpost'] > 0 ? floor(($now - $data['lastpost']) / 86400) : 0);

	$data['regdate'] = formatDate($data['regdate']);
	$data['lastpost'] = formatDate($data['lastpost']);

	$avatar = (empty($data['avatar']) || !$this->User->isOnline() ? '' : '<img src="?page=GetFile;file='.$data['avatar'].'" class="avatar" alt="" />');

	$body =
		'
		<table class="frame" style="width:50%">
			<tr>
				<td class="title" colspan="3">
					Profil von '.$data['name'].'
				</td>
			</tr>
			<tr>
				<td class="main" style="width:150px;">
					Name:
				</td>
				<td class="main">
					'.$data['realname'].'
				</td>
				<td class="main" rowspan="7">
					<div style="height:100px;width:150px;overflow:hidden;">'.$avatar.'</div>
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;width:150px;">
					Wohnort:
				</td>
				<td class="main">
					'.(!empty($data['plz']) ? $data['plz'] : '').'<br />'.$data['location'].'
				</td>
			</tr>
			<tr>
				<td class="main" style="width:150px;">
					Alter:
				</td>
				<td class="main">
					'.$age.'
				</td>
			</tr>
			<tr>
				<td class="main" style="width:150px;">
					Geschlecht:
				</td>
				<td class="main">
					'.$gender.'
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;width:150px;">
					Dabei seit:
				</td>
				<td class="main" colspan="2">
					'.$data['regdate'].'<br />
					das sind '.$regdays.' Tage
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;width:150px;">
					Letzter Beitrag:
				</td>
				<td class="main" colspan="2">
					'.$data['lastpost'].'<br />
					das war vor '.$lastpostdays.' Tagen
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;width:150px;">
					Beitr&auml;ge:
				</td>
				<td class="main" colspan="2">
					'.$data['posts'].'<br />
					das sind '.$postsperday.' pro Tag
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;width:150px;">
					Freier Text
				</td>
				<td class="main" colspan="2">
					'.$data['text'].'
				</td>
			</tr>
			<tr>
				<td class="main" colspan="3">
					<a href="?page=UserRecent;id='.$this->Board->getId().';user='.$this->id.'"><span class="button">aktuelle Beiträge</span></a>
					'.($this->User->isOnline() ? '<a href="?page=NewPrivateThread;id='.$this->Board->getId().';recipients='.$data['name'].'"><span class="button">Neues privates Thema</span></a>' : '').'
					'.($this->User->isLevel(User::ROOT) ? '<a href="?page=DeleteUser;id='.$this->Board->getId().';user='.$this->id.'"><span class="button">Benutzerkonto löschen</span></a>' : '')
					.'
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Profil von '.$data['name']);
	$this->setValue('body', $body);
	}

private function calcAge($data)
	{
	if ($data == 0)
		{
		return 0;
		}

	$age = time() - $data;

	return floor($age/60/60/24/365);
	}

}


?>