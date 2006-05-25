<?php


class AdminDesign extends AdminPage{


public function prepare()
	{
	$body=
	'
	<table class="frame" style="width:80%">
	<tr>
		<td class="title" colspan="2">
			Layout &amp; Design
		</td>
	</tr>
	<tr>
		<td class="main">
			<img src="images/dev.png" />
		</td>
		<td class="main">
			<ul>
				<li style="margin:20px;">
				<a href="?page=AdminHtml;id='.$this->Board->getId().'"><span class="button">HTML-Vorlage</span></a>
				Hier kannst Du die HTML-Vorlage für das Forum bearbeiten. Achte auf XHTML 1.1-Kompatibilität!
				</li>
				<li style="margin:20px;">
				<a href="?page=AdminCss;id='.$this->Board->getId().'"><span class="button">CSS-Vorlage</span></a>
				Farben, Schriften, Bilder etc. werden mittels Stylesheet festgelegt.
				</li>
			</ul>
		</td>
	</tr>
	</table>
	';

	$this->setValue('title', 'Layout &amp; Design');
	$this->setValue('body', $body);
	}


}


?>