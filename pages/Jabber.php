<?php


class Jabber extends Page{


public function prepare()
	{
	$this->setValue('title', 'jabber.laber-land.de');

	$body = '
		<table class="frame">
			<tr>
				<td class="title" colspan="2">
					jabber.laber-land.de
				</td>
			</tr>
			<tr>
				<td class="main" style="vertical-align:top;">
						<img src="images/jabber/jabber-powered.png" alt="Jabber" />
					</td>
					<td class="main">
<p>
Laber-Land bietet allen die Möglichkeit sich in Echtzeit zu unterhalten und Diskussionen in &quot;Chat&quot;-Rämen zu führen.Hier findest Du ein Kurzanleitung zur Einrichtung und Nutzung des Dienstes.</p>

<p>
Zur Realisierung nutzen wir das <a href="http://www.jabber.org" class="extlink">Jabber</a>-Protokoll. Dis ist ein freies und standardisiertes Protokoll für Instant-Messaging. Mehr Informationen, warum man Jabber und uzm Beispiel nicht ICQ nutzen sollte findet man unter <a href="http://www.deshalbfrei.org/software:instant_messaging" class="extlink">deshalbfrei.org</a>
</p>

<h1 style="font-size:14px">Voraussetzungen</h1>
<p>
Du mußt entweder im <a href="?page=Register" class="link">Forum registriert</a> sein oder bereits eine Jabber-Kennung eines anderen Dienstes besitzen.
</p>

<h1 style="font-size:14px">Benötigte Software</h1>
<p>
Um Jabber nutzen zu können ist ein sog. Instant-Messenger notwenidg. Die meisten Multiprotokoll-Proramme wie zum Beispiel <a href="http://www.miranda-im.org" class="extlink">Miranda</a> oder <a href="http://www.kde.org">Kopete</a> unterstützen Jabber bereits. Empfehlenswert ist auch der reine Jabber-Client <a href="http://psi.sf.net" class="extlink">PSI</a>.
</p>


<h1 style="font-size:14px">Einrichtung der Software</h1>
<p>
Nach dem Start von PSI muß ein neues Konto angelegt werden.<br /><img src="images/jabber/1.png" alt="" /><br />
Nun mußt Du Deine Jabber-ID angeben, die "<em>DeinBenutzername</em>@jabber.laber-land.de" lautet. Das Passwort ist ebenfalls das gleiche wie im Forum. <br /><br /><img src="images/jabber/2.png" alt="" /><br />
Unter &quot;Verbindung&quot; mußt Du noch "Kennwort unverschlüsselt übermitteln" aktivieren.<br /><img src="images/jabber/3.png" alt="" /><br />
Optinal kann unter "Details" noch die Visitenkarte angelegt werden. Diese Informationen können von anderen Nutzern abgerufen werden.
Hiernnach brauchst Du nur noch Deinen Status auf "Online" zu ändern und Du bist "drin". (Man kann sich auch beim Programmstart automatisch verbinden lassen)
<br /><img src="images/jabber/4.png" alt="" /><br />
Um mit anderen in Kontakt zu treten, mußt Du deren Jabber-ID zu Deiner Kontakliste hinzufüen.
</p>

<h1 style="font-size:14px">Chat-Räume betreten</h1>
<p>
Um mit mehreren gleichzeitig zu chatten, müssen alle einen sog. Chat-Raum betreten. Wähle "Chatroom betreten" und trage folgende Einstellungen ein. (Der Spitzname ist frei wählbar)<br /><img src="images/jabber/5.png" alt="" /><br /><br /><img src="images/jabber/6.png" alt="" />
</p>
				</td>
			</tr>
		</table>
		<div style="font-size:8px;text-align:left;">JabberPowered is a trademark of <a href="http://www.jabber.org" class="extlink">Jabber, Inc.</a> and its use is licensed through the Jabber Software Foundation.</div>
		';

	$this->setValue('body', $body);
	}
}


?>