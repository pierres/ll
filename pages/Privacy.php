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

class Privacy extends Page {


public function prepare()
	{
	$this->setTitle('Datenschutzerklärung');

	$body = '
		<div class="box">
			<ol>
			<li><strong>Speicherung und Verarbeitung von Daten</strong>
				<p>
				Alle Zugriffe auf diese Seiten werden protokolliert. Hierbei werden folgende Daten erfasst:
				<ul>
					<li><em>Datum und Uhrzeit</em> des Zugriffs</li>
					<li><em>Anfrage</em> des Clients</li>
					<li><em>Rückgabecode</em> des Servers</li>
					<li><em>Größe</em> der übertragenen Daten</li>
					<li>vom Client gesendeter <em>Referer</em></li>
					<li>vom Client gesendete <em>Kennung</em></li>
				</ul>
				Diese Daten werden für statistische Zwecke oder zur Erkennung von Angriffen verwendet. Diese Daten werden eine Woche lang archiviert. Hier ein beispielhafter Log-Eintrag:
				<pre>
				127.0.0.1 - - [31/Jan/2007:09:49:14 +0100] "GET /images/bg.png
				HTTP/1.1" 200 154 "'.$this->Input->getURL().'" "Mozilla/5.0
				(compatible; Konqueror/3.5) KHTML/3.5.6 (like Gecko)"
				</pre>
				</p>
			</li>
			<li><strong>Speicherung persönlicher Daten bei der Registrierung</strong>
				<p>
				Bei der Registrierung im Forum wird neben einem Benutzernamen auch eine E-Mail-Adresse gespeichert. Diese wird nur intern verwendet und nicht weitergegeben oder veröffentlicht. Alle weiteren Angaben im Profil des Nutzers sind freiwillig und öffentlich zugänglich. Alle Angaben kann der Nutzer jederzeit unter &quot;Mein Profil&quot; einsehen und ändern.
				</p>
			</li>
			<li><strong>Beendigung der Mitgliedschaft</strong>
				<p>
				Jeder registrierte Nutzer kann seine Mitgliedschaft jederzeit über sein Profil beenden. Hierbei werden alle im Profil befindlichen Daten gelöscht. Alle vom Nutzer verfassten Beiträge im Forum bleiben jedoch bestehen. Der Benutzername selbst wird weiterhin als Verfasser der Beiträge angezeigt. Auf besonderne Wunsch hin kann der Benutzername allerdings geändert werden. Diese Anfrage sollte jedoch unbedingt vor dem Löschen des Benutzerkontos erfolgen. (Kontakt-Adresse: siehe <a class="" href="'.$this->Output->createUrl('Impressum').'">Impressum</a>)
				</p>
			</li>
			<li><strong>Cookies</strong>
				<p>
				Beim Anmelden oder Verfassen von Beiträgen werden Cookies verwendet. Diese enhalten eine für jeden Nutzer eindeutige Kennung.
				</p>
			</li>
			<li><strong>Sicherheitshinweis</strong>
				<p>
				Dem Nutzer ist bewusst, dass alle Angaben im Profil, alle Beiträge und Dateien im Forum öffentlich zugänglich sind. Für alle Folgen dieser Veröffentlichung ist der Nutzer selbst verantwortlich.
				</p>
			</li>
			</ol>
		</div>
		';

	$this->setBody($body);
	}
}


?>
