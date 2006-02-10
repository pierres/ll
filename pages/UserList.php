<?php


class UserList extends Page{


public function prepare()
	{
	$this->setValue('title', 'Benutzerliste');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showWarning('kein Zugriff!');
		}

	$users = $this->Sql->fetch
		('
		SELECT
			id,
			name,
			realname,
			email
		FROM
			users
		ORDER BY
			id DESC
		');

	$list = '<table>';
	foreach ($users as $user)
		{
		$list .= '<tr>
			<td><a href="?page=ShowUser;user='.$user['id'].'" class="link">'.$user['name'].'</a></td>
			<td>'.$user['realname'].'</td>
			<td>'.$user['email'].'</td>
			</tr>';
		}
	$list .= '</table>';

	$body ='	<table class="frame">
				<tr>
					<td class="title">
						'.$this->getValue('title').'
					</td>
				</tr>
				<tr>
					<td class="main">
						'.$list.'
					</td>
				</tr>
			</table>';

	$this->setValue('body', $body);
	}

}

?>