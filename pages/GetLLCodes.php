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
class GetLLCodes extends GetFile{

public function prepare()
	{
	$this->exitIfCached();
	}

public function show()
	{
	$smilies = <<<eot
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<meta http-equiv="content-language" content="de" />
	<style type="text/css">
	body	{
		font-family:sans-serif;
		font-size:12px;
		text-align:left;
		color:#002468;
		background-color:#ffffff;
		}

	a	{
		text-decoration:none;
		color:#182263;
		}

	a:hover	{
		color:#FFCC00;
		text-decoration:none;
		}

	table	{
		empty-cells:show;
		text-align:left;
		border-collapse:collapse;
		margin-left:auto;
		margin-right:auto;
		}

	.main	{
		border-style:solid;
		border-width:1px;
		border-color:#182263;
 		}

	.code	{
		color:#002468;
		background-color:#eeeeee;
		border-width:1px;
		border-style:dotted;
		width:300px;
		padding:5px;
		}

	.preview{
		color:#002468;
		background-color:#dddddd;
		border-width:1px;
		border-style:dotted;
		width:300px;
		padding:5px;
		}

	.link	{
		text-decoration:underline;
		color:#6c83af;
		}

	.extlink{
		text-decoration:underline;
		}

	.image	{
		max-width:300px;
		max-height:300px;
		border-style:solid;
		border-width:1px;
		border-color:#182263;
		}

	.image:hover{
		cursor:pointer;
		border-color:#6c83af;
		}

	.hint	{
		color:darkred;
		font-size:8px;
		}

	h1{font-size:20px;}
	h2{font-size:18px;}
	h3{font-size:16px;}
	h4{font-size:14px;}
	h5{font-size:12px;}
	h6{font-size:10px;}
	</style>
	<title>
		LL-Codes
	</title>
</head>
<body>
	<table class="main">
		<tr>
			<td class="code">
<pre>
&lt;code&gt;
Quellcode
&lt;/code&gt;
</pre>
<div class="hint">Tags m&uuml;ssen jeweils in einer Zeile stehen; nicht schachtelbar</div>
			</td>
			<td class="preview">
<pre>
Quellcode
</pre>
			</td>
		</tr><tr>
			<td class="code">
<pre>
&lt;quote&gt;Zitat&lt;/quote&gt;
</pre>
<div class="hint">schachtelbar</div>
			</td>
			<td class="preview">
<blockquote><div>Zitat</div></blockquote>
			</td>
		</tr><tr>
			<td class="code">
<pre>
&lt;quote Autor/Quelle&gt;Zitat&lt;/quote&gt;
</pre>
<div class="hint">schachtelbar</div>
			</td>
			<td class="preview">
<cite>Autor/Quelle</cite><blockquote><div>Zitat</div></blockquote>
			</td>
		</tr><tr>
			<td class="code">
<pre>
* 1
* 2
** 2a
** 2b
*** 2bi
* 3
* 4
</pre>
<div class="hint">schachtelbar</div>
			</td>
			<td class="preview">
<ul><li>1</li><li>2<ul><li>2a</li><li>2b<ul><li>2bi</li></ul></li></ul></li><li>3</li><li>4</li></ul>
			</td>
		</tr><tr>
			<td class="code">
<pre>
!&Uuml;berschrift 1
!!&Uuml;berschrift 2
!!!&Uuml;berschrift 3
!!!!&Uuml;berschrift 4
!!!!!&Uuml;berschrift 5
!!!!!!&Uuml;berschrift 6
</pre>
<div class="hint">Die Reihenfolge 1-6 sollte beibehalten werden</div>
			</td>
			<td class="preview">
<h1>&Uuml;berschrift 1</h1><h2>&Uuml;berschrift 2</h2><h3>&Uuml;berschrift 3</h3><h4>&Uuml;berschrift 4</h4><h5>&Uuml;berschrift 5</h5><h6>&Uuml;berschrift 6</h6>
			</td>
		</tr><tr>
			<td class="code">
<pre>
//Hervorhebung//
</pre>
			</td>
			<td class="preview">
<em>Hervorhebung</em>
			</td>
		</tr><tr>
			<td class="code">
<pre>
**Betonung**
</pre>
			</td>
			<td class="preview">
<strong>Betonung</strong>
			</td>
		</tr><tr>
			<td class="code">
<pre>
==inline Code==
</pre>
			</td>
			<td class="preview">
<code>inline Code</code>
			</td>
		</tr><tr>
			<td class="code">
<pre>
&quot;inline Zitat&quot;
</pre>
			</td>
			<td class="preview">
<q>inline Zitat</q>
			</td>
		</tr><tr>
			<td class="code">
<pre>
----
</pre>
			</td>
			<td class="preview">
<hr />
			</td>
		</tr><tr>
			<td class="code">
<pre>
--gel&ouml;scht--
</pre>
			</td>
			<td class="preview">
<pre>
<span><del>gel&ouml;scht</del></span>
</pre>
			</td>
		</tr><tr>
			<td class="code">
<pre>
++eingef&uuml;gt++
</pre>
			</td>
			<td class="preview">
<pre>
<span><ins>eingef&uuml;gt</ins></span>
</pre>
			</td>
		</tr><tr>
			<td class="code">
<pre>
www.laber-land.de
&lt;www.laber-land.de Laber-Land&gt;
&lt;www.laber-land.de&gt;
</pre>
<div class="hint">beliebige Protokolle werden unterst&uuml;tzt. Bei www. und ftp.-Domains ist die Angabe des Protokolls optional. Auch E-Mail-Adressen werden erkannt.</div>
			</td>
			<td class="preview">
<a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">www.laber-land.de</a><br /><a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">Laber-Land</a><br /><a href="http://www.laber-land.de" onclick="return !window.open(this.href);" rel="nofollow" class="extlink">[1]</a>
			</td>
		</tr><tr>
			<td class="code">
<pre>
www.laber-land.de/images/logo.png
</pre>
<div class="hint">Unterst&uuml;tzte Dateiendungen: png, gif, jpg, jpeg</div>
			</td>
			<td class="preview">
<img src="images/logo.png" alt="" class="image" />
			</td>
		</tr>
	</table>
</body>
</html>
eot;

	$this->Output->writeOutput($smilies);
	}

}

?>