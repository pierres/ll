<?php


class UserList extends Page{


private $user = 0;
private $users = 0;

/**
*@TODO: Create Search-Index
*/

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

	try
		{
		$orderby = $this->Io->getString('orderby');
		$allowdorderby = array('id', 'name', 'posts', 'realname');
		if (!in_array($orderby, $allowdorderby))
			{
			$orderby = 'id';
			}
		}
	catch (IoRequestException $e)
		{
		$orderby = 'id';
		}

	try
		{
		$sort = $this->Io->getInt('sort');
		}
	catch (IoRequestException $e)
		{
		$sort = 0;
		}

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
				'.$orderby.' '.($sort > 0 ? 'DESC' : 'ASC').'
			LIMIT
				'.$limit
			);
		}
	catch(SqlNoDataException $e)
		{
		$users = array();
		}

	$link = '?page=UserList;id='.$this->Board->getId().';user='.$this->user;
	$curlink = '?page=UserList;id='.$this->Board->getId().';orderby='.$orderby.';sort='.$sort;

	$this->users = $this->Sql->numRows('users');
	$pages = $this->getPages();

	$next = ($this->users > $this->Settings->getValue('max_users')+$this->user
		? ' <a href="'.$curlink.';user='.($this->Settings->getValue('max_users')+$this->user).'">&#187;</a>'
		: '');

	$last = ($this->user > 0
		? '<a href="'.$curlink.';user='.nat($this->user-$this->Settings->getValue('max_users')).'">&#171;</a>'
		: '');

	$list = '<table style="width:700px;">
		<tr>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=name;sort='.abs($sort-1).'">Benutzer</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=realname;sort='.abs($sort-1).'">Name</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=posts;sort='.abs($sort-1).'">Beiträge</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=id;sort='.abs($sort-1).'">Dabei seit</a></td>
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