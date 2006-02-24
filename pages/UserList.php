<?php


class UserList extends Page{


private $user = 0;
private $users = 0;


public function prepare()
	{
	$this->setValue('title', 'Benutzerliste');

	try
		{
		$this->user = $this->Io->getInt('user');
		}
	catch (IoRequestException $e)
		{
		$this->user = 0;
		}

	$limit = $this->user.','.$this->Settings->getValue('max_users');
	$this->users = $this->Sql->numRows('users');

	try
		{
		$users = $this->Sql->fetch
			('
			SELECT
				id,
				name,
				realname,
				posts,
				regdate
			FROM
				users
			ORDER BY
				id DESC
			LIMIT
				'.$limit
			);
		}
	catch(SqlNoDataException $e)
		{
		$users = array();
		}

	$pages = $this->getPages();

	$next = ($this->users > $this->Settings->getValue('max_users')+$this->user
		? ' <a href="?page=UserList;id='.$this->Board->getId().';user='.($this->Settings->getValue('max_users')+$this->user).'">&#187;</a>'
		: '');

	$last = ($this->user > 0
		? '<a href="?page=UserList;id='.$this->Board->getId().';user='.nat($this->user-$this->Settings->getValue('max_users')).'">&#171;</a>'
		: '');

	$list = '<table style="width:700px;">
		<tr>
			<td class="path">Benutzer</td>
			<td class="path">Name</td>
			<td class="path">Beiträge</td>
			<td class="path">Dabei seit</td>
		</tr>';
	foreach ($users as $user)
		{
		$list .= '<tr>
			<td><a href="?page=ShowUser;user='.$user['id'].'">'.$user['name'].'</a></td>
			<td>'.$user['realname'].'</td>
			<td>'.$user['posts'].'</td>
			<td>'.date('d.m.Y', $user['regdate']).'</td>
			</tr>';
		}
	$list .= '</table>';

	$body ='	<table class="frame">
				<tr>
					<td class="title" colspan="2">
						'.$this->getValue('title').'
					</td>
				</tr>
				<tr>
					<td class="pages">'.$last.$pages.$next.'</td>
					<td class="pages" style="text-align:right;">'.$this->user.' bis '.($this->user+$this->Settings->getValue('max_users')).' von '.$this->users.'</td>
				</tr>
				<tr>
					<td class="main" style="padding-top:0px;" colspan="2">
						'.$list.'
					</td>
				</tr>
				<tr>
					<td class="pages">'.$last.$pages.$next.'</td>
					<td class="pages" style="text-align:right;">'.$this->user.' bis '.($this->user+$this->Settings->getValue('max_users')).' von '.$this->users.'</td>
				</tr>
			</table>';

	$this->setValue('body', $body);
	}

protected function getPages()
	{
	$pages = '';

	for ($i = 0; $i < ($this->users / $this->Settings->getValue('max_users')) && ($this->users / $this->Settings->getValue('max_users')) > 1; $i++)
		{
		if ($this->user < $this->Settings->getValue('max_users') * ($i-4))
			{
			$i = $this->Settings->getValue('max_users') * ($i-4);
			continue;
			}
		elseif($this->user > $this->Settings->getValue('max_users') * ($i+4))
			{
			continue;
			}

		if ($this->user == ($this->Settings->getValue('max_users') * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=UserList;id='.$this->Board->getId().';user='.($this->Settings->getValue('max_users') * $i).'">'.($i+1).'</a>';
			}
		}

	return $pages;
	}

}

?>