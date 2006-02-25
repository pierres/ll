<?php


class UserList extends Page{


private $user 		= 0;
private $users 		= 0;
private $orderby 	= 'posts';
private $sort		= 1;

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
		$this->orderby = $this->Io->getString('orderby');

		if (!in_array($this->orderby, array('regdate', 'name', 'posts', 'realname')))
			{
			$this->orderby = 'posts';
			}
		}
	catch (IoRequestException $e)
		{
		$this->orderby = 'posts';
		}

	try
		{
		$this->sort = $this->Io->getInt('sort');
		}
	catch (IoRequestException $e)
		{
		$this->sort = 1;
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
				'.$this->orderby.' '.($this->sort > 0 ? 'DESC' : 'ASC').'
			LIMIT
				'.$limit
			);
		}
	catch(SqlNoDataException $e)
		{
		$users = array();
		}

	$link = '?page=UserList;id='.$this->Board->getId().';user='.$this->user;
	$curlink = '?page=UserList;id='.$this->Board->getId().';orderby='.$this->orderby.';sort='.$this->sort;

	$this->users = $this->Sql->numRows('users');
	$pages = $this->getPages();

	$next = ($this->users > $this->Settings->getValue('max_users')+$this->user
		? ' <a href="'.$curlink.';user='.($this->Settings->getValue('max_users')+$this->user).'">&#187;</a>'
		: '');

	$last = ($this->user > 0
		? '<a href="'.$curlink.';user='.nat($this->user-$this->Settings->getValue('max_users')).'">&#171;</a>'
		: '');

	$list = '<tr>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=name;sort='.abs($this->sort-1).'">Benutzer</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=realname;sort='.abs($this->sort-1).'">Name</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=posts;sort='.abs($this->sort-1).'">Beiträge</a></td>
			<td class="path"><a class="pathlink" href="'.$link.';orderby=id;sort='.abs($this->sort-1).'">Dabei seit</a></td>
		</tr>
		<tr>
			<td class="pages" colspan="2">'.$last.$pages.$next.'</td>
			<td class="pages" style="text-align:right;" colspan="2">'.$this->user.' bis '.($this->user+$this->Settings->getValue('max_users')).' von '.$this->users.'</td>
		</tr>';
	foreach ($users as $user)
		{
		$list .= '<tr>
			<td class="main" style="min-width:200px;"><a href="?page=ShowUser;user='.$user['id'].'">'.$user['name'].'</a></td>
			<td class="main" style="min-width:200px;">'.$user['realname'].'</td>
			<td class="main">'.$user['posts'].'</td>
			<td class="main">'.date('d.m.Y', $user['regdate']).'</td>
			</tr>';
		}
	$list .= '<tr>
			<td class="pages" colspan="2">'.$last.$pages.$next.'</td>
			<td class="pages" style="text-align:right;" colspan="2">'.$this->user.' bis '.($this->user+$this->Settings->getValue('max_users')).' von '.$this->users.'</td>
		</tr>';

	$body ='	<table class="frame" style="width:700px;">
				<tr>
					<td class="title" colspan="4">
						'.$this->getValue('title').'
					</td>
				</tr>
					'.$list.'
			</table>';

	$this->setValue('body', $body);
	}

protected function getPages()
	{
	$showpages = 20;
	$pages = '';

	for ($i = 0; $i < ($this->users / $this->Settings->getValue('max_users')) && ($this->users / $this->Settings->getValue('max_users')) > 1; $i++)
		{
		if ($this->user < $this->Settings->getValue('max_users') * ($i-$showpages))
			{
			$i = $this->Settings->getValue('max_users') * ($i-$showpages);
			continue;
			}
		elseif($this->user > $this->Settings->getValue('max_users') * ($i+$showpages))
			{
			continue;
			}

		if ($this->user == ($this->Settings->getValue('max_users') * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=UserList;id='.$this->Board->getId().';orderby='.$this->orderby.';sort='.$this->sort.';user='.($this->Settings->getValue('max_users') * $i).'">'.($i+1).'</a>';
			}
		}

	return $pages;
	}

}

?>