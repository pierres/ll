<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class MarkupHelp extends Page {


public function prepare()
	{
	$this->setTitle($this->L10n->getText('Markup Help'));

	$this->setBody
		('
		<div class="box">
			<table id="markup-help">
				<tr>
					<th colspan="2" class="markup-help-title">'.$this->L10n->getText('Structure').'</th>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Paragraph').'</th>
					<td>
						<em>'.$this->L10n->getText('separated by two or more new lines').'</em>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('List').'</th>
					<td>
						<pre>* ...<br />* ...<br />** ...<br />*** ...</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Block Quote').'</th>
					<td>
						<pre>'.htmlentities('<quote>...</quote>').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Cited Quote').'</th>
					<td>
						<pre>'.htmlentities('<quote ...>...</quote>').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Code').'</th>
					<td>
						<pre>'.htmlentities('<code>...</code>').'</pre>
					</td>
				</tr>
				<tr>
					<th colspan="2" class="markup-help-title">'.$this->L10n->getText('Format').'</th>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Quote').'</th>
					<td>
						<pre>'.htmlentities('"..."').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Emphasized Text').'</th>
					<td>
						<pre>'.htmlentities("''...''").'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Strong Emphasized Text').'</th>
					<td>
						<pre>'.htmlentities("'''...'''").'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Smilies').'</th>
					<td>
						<pre>'.implode('<br />', array_keys(Markup::$smilies)).'</pre>
					</td>
				</tr>
				<tr>
					<th colspan="2" class="markup-help-title">'.$this->L10n->getText('Links').'</th>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Hint').'</th>
					<td>
						<ul>
							<li>'.$this->L10n->getText('<em>URL</em>s have to begin with <code>http://</code>, <code>https://</code> or <code>ftp://</code>').'</li>
							<li>'.$this->L10n->getText('If automatic detection does not work, use the explicit notation').'</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Automatic Detection').'</th>
					<td>
						<ul>
							<li>'.$this->L10n->getText('Image-<em>URL</em>s have to end with <code>.png</code>, <code>.jpg</code>, <code>.jpeg</code> or <code>.gif</code>').'</li>
							<li>'.$this->L10n->getText('Video-<em>URL</em>s have to end with <code>.ogg</code>, <code>.ogv</code> or <code>.ogm</code>').'</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Named Link').'</th>
					<td>
						<pre>'.htmlentities('<a href="url">...</a>').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Image Media').'</th>
					<td>
						<pre>'.htmlentities('<img src="url" />').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Audio Media').'</th>
					<td>
						<pre>'.htmlentities('<audio src="url" />').'</pre>
					</td>
				</tr>
				<tr>
					<th>'.$this->L10n->getText('Video Media').'</th>
					<td>
						<pre>'.htmlentities('<video src="url" />').'</pre>
					</td>
				</tr>
			</table>					
		</div>
		');
	}

}

?>