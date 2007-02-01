<?php


class Privacy extends Page{


public function prepare()
	{
	$this->setValue('title', 'Datenschutzerklärung');

	$body = '
		<table class="frame">
			<tr>
				<td class="title">
					Datenschutzerklärung
				</td>
			</tr>
			<tr>
				<td class="main" style="width:600px;">
					<ol>
						<li>
						<strong>Speicherung und Verarbeitung von Daten</strong>
						<p>Alle Zugriffe auf diese Seiten werden protokolliert. Hierbei werden folgende Daten erfasst:
						<ul style="margin:10px;">
							<li><em>IP</em> des Clients</li>
							<li><em>Datum und Uhrzeit</em> des Zugriffs</li>
							<li><em>Anfrage</em> des Clients</li>
							<li><em>Rückgabecode</em> des Servers</li>
							<li><em>Größe</em> der übertragenen Daten</li>
							<li>vom Client gesendeter <em>Referer</em></li>
							<li>vom Client gesendete <em>Kennung</em></li>
						</ul>
						Diese Daten werden für statistische Zwecke oder zur Erkennung von Angriffen verwendet. Diese Daten werden 4 Wochen lang archiviert. Hier ein beispielhafter Log-Eintrag:
						<div style="margin:5px;margin-left:30px;color:black;background-color:white;border:1px black solid;padding:2px;font-family:monospace;width:600px">127.0.0.1 - - [31/Jan/2007:09:49:14 +0100] "GET /images/bg.png<br />
						HTTP/1.1" 200 154 "http://www.laber-land.de/?page=Privacy;id=1" "Mozilla/5.0<br />
						(compatible; Konqueror/3.5) KHTML/3.5.6 (like Gecko)"</div>
						</p>
						</li>
						<li>
						<strong>Speicherung persönlicher Daten bei der Registrierung</strong>
						<p>Bei der Registrierung im Forum wird neben einem Benutzernamen auch eine E-Mail-Adresse gespeichert. Diese wird nur intern verwendet und nicht weitergegeben oder veröffentlicht. Alle weiteren Angaben im Profil des Nutzers sind freiwillig und öffentlich zugänglich. Alle Angaben kann der Nutzer jederzeit unter &quot;Mein Profil&quot; einsehen und ändern.</p>
						</li>
						<li>
						<strong>Beendigung der Mitgliedschaft</strong>
						<p>Jeder registrierte Nutzer kann seine Mitgliedschaft jederzeit über sein Profil beenden. Hierbei werden alle im Profil befindlichen Daten gelöscht. Alle vom Nutzer verfassten Beiträge im Forum bleiben jedoch bestehen. Der Benutzername selbst wird weiterhin als Verfasser der Beiträge angezeigt. Auf besonderne Wunsch hin kann der Benutzername allerdings geändert werden. Diese Anfrage sollte jedoch unbedingt vor dem Löschen des Benutzerkontos erfolgen. (Kontakt-Adresse: siehe <a class="link" href="?page=Impressum;id='.$this->Board->getId().'">Impressum</a>)</p>
						</li>
						<li>
						<strong>Cookies</strong>
						<p>Beim Anmelden oder Verfassen von Beiträgen werden Cookies verwendet. Diese enhalten eine für jeden Nutzer eindeutige Kennung.</p>
						</li>
						<li>
						<strong>Sicherheitshinweis</strong>
						<p>Dem Nutzer ist bewusst, dass alle Angaben im Profil, alle Beiträge und Dateien im Forum öffentlich zugänglich sind. Für alle Folgen dieser Veröffentlichung ist der Nutzer selbst verantwortlich.</p>
						</li>
					</ol>
				<div style="text-align:right;font-size:10px;margin:5px;">
					Stand: <strong>1.2.2007</strong>
				</div>
				</td>
			</tr>
		</table>
		';

	$this->setValue('body', $body);
	}
}


?>