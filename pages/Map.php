<?php


class Map extends Page{


public function prepare()
	{
	$this->setValue('title', 'Laber-Landkarte');

	$stm = $this->DB->prepare
		('
		SELECT
			plz.x,
			plz.y,
			users.name,
			users.id
		FROM
			plz,
			users
		WHERE
			users.plz = plz.code
		GROUP BY
			users.id'
		);

	$pins = '';

	foreach ($stm->getRowSet() as $data)
		{
		$data['x'] += rand(-4,4);
		$data['y'] += rand(-4,4);

		$pins .=
			'<a style="position:absolute;top:'.$data['y'].'px;left:'.$data['x'].'px;z-index:'.$data['id'].';color:red;font-size:10px;" href="?id='.$this->Board->getId().';page=ShowUser;user='.$data['id'].'" title="'.$data['name'].'">°</a>';
		}

	$stm->close();

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Laber-Landkarte
				</td>
			</tr>
			<tr>
				<td class="main" style="padding:0px;">
					<div style="width:500px;height:668px;position:relative;background-color:white;background-image:url(images/map.gif)">
						'.$pins.'
					</div>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>