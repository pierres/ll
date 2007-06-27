<?php


class ImportTemplates extends Page{

public function prepare()
	{
	$boards = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			boards'
		);

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			html = ?,
			css = ?
		WHERE
			id = ?'
		);

	foreach ($boards as $board)
		{
		$html = file_get_contents('html/'.$board.'.html');
		$css = file_get_contents('html/'.$board.'.css');

		$css = str_replace('../', '', $css);
		$html = str_replace('html/<!-- id -->.css', '?page=GetCss;id='.$board, $html);
		$html = str_replace('<!-- id -->', $board, $html);

		$stm->bindString($html);
		$stm->bindString($css);
		$stm->bindInteger($board);
		$stm->execute();
		}
	$stm->close();

	$this->setValue('body', 'OK');
	}


}

?>