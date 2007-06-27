<?php

class GetSmilies extends GetFile{

public function prepare()
	{
	$this->exitIfCached();
	}

public function show()
	{
	$smilies = <<<eot
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
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

	.preview{
		color:#002468;
		background-color:#dddddd;
		border-width:1px;
		border-style:dotted;
		width:600px;
		padding:5px;
		}

	.smiley	{
		border:none;
		}
	</style>
	<title>
		Smilies
	</title>
</head>
<body>
<script language="javascript" type="text/javascript">
<!--
function getSmiley(text)
	{
	text = ' ' + text + ' ';
	if (opener.document.forms[0].text.createTextRange && opener.document.forms[0].text.caretPos)
		{
		var caretPos = opener.document.forms[0].text.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		opener.document.forms[0].text.focus();
		}
	else
		{
		opener.document.forms[0].text.value  += text;
		opener.document.forms[0].text.focus();
		}
	}

function getExtraSmiley(text)
	{
	getSmiley('<'+text+'>');
	}

//-->
</script>
	<table class="main">
		<tr>
			<td class="preview">
				<a href="javascript:getSmiley(';-)')"><img src="images/smilies/wink.gif" alt="wink" class="smiley" /></a>
				<a href="javascript:getSmiley(';D')"><img src="images/smilies/grin.gif" alt="grin" class="smiley" /></a>
				<a href="javascript:getSmiley('::)')"><img src="images/smilies/rolleyes.gif" alt="rolleyes" class="smiley" /></a>
				<a href="javascript:getSmiley(':-)')"><img src="images/smilies/smiley.gif" alt="smiley" class="smiley" /></a>
				<a href="javascript:getSmiley(':-\\')"><img src="images/smilies/undecided.gif" alt="undecided" class="smiley" /></a>
				<a href="javascript:getSmiley(':-X')"><img src="images/smilies/lipsrsealed.gif" alt="lipsrsealed" class="smiley" /></a>
				<a href="javascript:getSmiley(':-[')"><img src="images/smilies/embarassed.gif" alt="embarassed" class="smiley" /></a>
				<a href="javascript:getSmiley(':-*')"><img src="images/smilies/kiss.gif" alt="kiss" class="smiley" /></a>
				<a href="javascript:getSmiley('>:(')"><img src="images/smilies/angry.gif" alt="angry" class="smiley" /></a>
				<a href="javascript:getSmiley(':P')"><img src="images/smilies/tongue.gif" alt="tongue" class="smiley" /></a>
				<a href="javascript:getSmiley(':D')"><img src="images/smilies/cheesy.gif" alt="cheesy" class="smiley" /></a>
				<a href="javascript:getSmiley(':-(')"><img src="images/smilies/sad.gif" alt="sad" class="smiley" /></a>
				<a href="javascript:getSmiley(':o')"><img src="images/smilies/shocked.gif" alt="shocked" class="smiley" /></a>
				<a href="javascript:getSmiley('8)')"><img src="images/smilies/cool.gif" alt="cool" class="smiley" /></a>
				<a href="javascript:getSmiley('???')"><img src="images/smilies/huh.gif" alt="huh" class="smiley" /></a>
				<a href="javascript:getSmiley(':\'(')"><img src="images/smilies/cry.gif" alt="cry" class="smiley" /></a>
			</td>
		</tr>
		<tr>
			<td class="preview">
				<a href="javascript:getExtraSmiley('vulcan')"><img src="images/smilies/extra/vulcan.gif" alt="vulcan" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wry')"><img src="images/smilies/extra/wry.gif" alt="wry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('laugh')"><img src="images/smilies/extra/laugh.gif" alt="laugh" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smitten')"><img src="images/smilies/extra/smitten.gif" alt="smitten" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('veryangry2')"><img src="images/smilies/extra/veryangry2.gif" alt="veryangry2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smartass2')"><img src="images/smilies/extra/smartass2.gif" alt="smartass2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sleeping')"><img src="images/smilies/extra/sleeping.gif" alt="sleeping" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('lips')"><img src="images/smilies/extra/lips.gif" alt="lips" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wiseguy')"><img src="images/smilies/extra/wiseguy.gif" alt="wiseguy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('kiss2')"><img src="images/smilies/extra/kiss2.gif" alt="kiss2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('nice')"><img src="images/smilies/extra/nice.gif" alt="nice" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('2thumbsup')"><img src="images/smilies/extra/2thumbsup.gif" alt="2thumbsup" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('drummer')"><img src="images/smilies/extra/drummer.gif" alt="drummer" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('devil')"><img src="images/smilies/extra/devil.gif" alt="devil" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('tongue')"><img src="images/smilies/extra/tongue.gif" alt="tongue" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('afro')"><img src="images/smilies/extra/afro.gif" alt="afro" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('pimp2')"><img src="images/smilies/extra/pimp2.gif" alt="pimp2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('laugh2')"><img src="images/smilies/extra/laugh2.gif" alt="laugh2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('blush')"><img src="images/smilies/extra/blush.gif" alt="blush" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('laugh3')"><img src="images/smilies/extra/laugh3.gif" alt="laugh3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('laugh4')"><img src="images/smilies/extra/laugh4.gif" alt="laugh4" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hair2')"><img src="images/smilies/extra/hair2.gif" alt="hair2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('speechless')"><img src="images/smilies/extra/speechless.gif" alt="speechless" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cheesy')"><img src="images/smilies/extra/cheesy.gif" alt="cheesy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('blank')"><img src="images/smilies/extra/blank.gif" alt="blank" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cry')"><img src="images/smilies/extra/cry.gif" alt="cry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wink2')"><img src="images/smilies/extra/wink2.gif" alt="wink2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wink3')"><img src="images/smilies/extra/wink3.gif" alt="wink3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stooge_moe')"><img src="images/smilies/extra/stooge_moe.gif" alt="stooge_moe" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('confused')"><img src="images/smilies/extra/confused.gif" alt="confused" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('snore')"><img src="images/smilies/extra/snore.gif" alt="snore" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('kid')"><img src="images/smilies/extra/kid.gif" alt="kid" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sick')"><img src="images/smilies/extra/sick.gif" alt="sick" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('yes')"><img src="images/smilies/extra/yes.gif" alt="yes" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wings')"><img src="images/smilies/extra/wings.gif" alt="wings" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('skull')"><img src="images/smilies/extra/skull.gif" alt="skull" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stooge_larry')"><img src="images/smilies/extra/stooge_larry.gif" alt="stooge_larry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('party')"><img src="images/smilies/extra/party.gif" alt="party" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smiley2')"><img src="images/smilies/extra/smiley2.gif" alt="smiley2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('withclever')"><img src="images/smilies/extra/withclever.gif" alt="withclever" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('behead2')"><img src="images/smilies/extra/behead2.gif" alt="behead2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('happy')"><img src="images/smilies/extra/happy.gif" alt="happy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('argue')"><img src="images/smilies/extra/argue.gif" alt="argue" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bandana')"><img src="images/smilies/extra/bandana.gif" alt="bandana" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('lips2')"><img src="images/smilies/extra/lips2.gif" alt="lips2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stupid')"><img src="images/smilies/extra/stupid.gif" alt="stupid" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rifle')"><img src="images/smilies/extra/rifle.gif" alt="rifle" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stunned')"><img src="images/smilies/extra/stunned.gif" alt="stunned" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sad')"><img src="images/smilies/extra/sad.gif" alt="sad" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('singer')"><img src="images/smilies/extra/singer.gif" alt="singer" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('gorgeous')"><img src="images/smilies/extra/gorgeous.gif" alt="gorgeous" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smug2')"><img src="images/smilies/extra/smug2.gif" alt="smug2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('earmuffs')"><img src="images/smilies/extra/earmuffs.gif" alt="earmuffs" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('veryangry')"><img src="images/smilies/extra/veryangry.gif" alt="veryangry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sombrero')"><img src="images/smilies/extra/sombrero.gif" alt="sombrero" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('huh2')"><img src="images/smilies/extra/huh2.gif" alt="huh2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('thinking2')"><img src="images/smilies/extra/thinking2.gif" alt="thinking2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smiley')"><img src="images/smilies/extra/smiley.gif" alt="smiley" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('thinking')"><img src="images/smilies/extra/thinking.gif" alt="thinking" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sunny')"><img src="images/smilies/extra/sunny.gif" alt="sunny" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('punk')"><img src="images/smilies/extra/punk.gif" alt="punk" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('santa')"><img src="images/smilies/extra/santa.gif" alt="santa" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wreck')"><img src="images/smilies/extra/wreck.gif" alt="wreck" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sick2')"><img src="images/smilies/extra/sick2.gif" alt="sick2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('pirate')"><img src="images/smilies/extra/pirate.gif" alt="pirate" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('furious2')"><img src="images/smilies/extra/furious2.gif" alt="furious2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('undecided')"><img src="images/smilies/extra/undecided.gif" alt="undecided" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('furious3')"><img src="images/smilies/extra/furious3.gif" alt="furious3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dizzy')"><img src="images/smilies/extra/dizzy.gif" alt="dizzy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('grin')"><img src="images/smilies/extra/grin.gif" alt="grin" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('beatnik')"><img src="images/smilies/extra/beatnik.gif" alt="beatnik" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('builder2')"><img src="images/smilies/extra/builder2.gif" alt="builder2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('beam')"><img src="images/smilies/extra/beam.gif" alt="beam" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rockstar')"><img src="images/smilies/extra/rockstar.gif" alt="rockstar" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('elf')"><img src="images/smilies/extra/elf.gif" alt="elf" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bigcry')"><img src="images/smilies/extra/bigcry.gif" alt="bigcry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('leer')"><img src="images/smilies/extra/leer.gif" alt="leer" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shy')"><img src="images/smilies/extra/shy.gif" alt="shy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('mad')"><img src="images/smilies/extra/mad.gif" alt="mad" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('moustache')"><img src="images/smilies/extra/moustache.gif" alt="moustache" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('tongue2')"><img src="images/smilies/extra/tongue2.gif" alt="tongue2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('tongue3')"><img src="images/smilies/extra/tongue3.gif" alt="tongue3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sleep')"><img src="images/smilies/extra/sleep.gif" alt="sleep" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rolleyes2')"><img src="images/smilies/extra/rolleyes2.gif" alt="rolleyes2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('end')"><img src="images/smilies/extra/end.gif" alt="end" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rolleyes3')"><img src="images/smilies/extra/rolleyes3.gif" alt="rolleyes3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rolleyes4')"><img src="images/smilies/extra/rolleyes4.gif" alt="rolleyes4" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rolleyes5')"><img src="images/smilies/extra/rolleyes5.gif" alt="rolleyes5" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('uhoh')"><img src="images/smilies/extra/uhoh.gif" alt="uhoh" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('egypt')"><img src="images/smilies/extra/egypt.gif" alt="egypt" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('idea')"><img src="images/smilies/extra/idea.gif" alt="idea" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('chef')"><img src="images/smilies/extra/chef.gif" alt="chef" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('juggle2')"><img src="images/smilies/extra/juggle2.gif" alt="juggle2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sweetheart')"><img src="images/smilies/extra/sweetheart.gif" alt="sweetheart" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('balloon2')"><img src="images/smilies/extra/balloon2.gif" alt="balloon2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('balloon3')"><img src="images/smilies/extra/balloon3.gif" alt="balloon3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('indian_chief')"><img src="images/smilies/extra/indian_chief.gif" alt="indian_chief" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('karate')"><img src="images/smilies/extra/karate.gif" alt="karate" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sleepy')"><img src="images/smilies/extra/sleepy.gif" alt="sleepy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('heart')"><img src="images/smilies/extra/heart.gif" alt="heart" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hair')"><img src="images/smilies/extra/hair.gif" alt="hair" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('iloveyou')"><img src="images/smilies/extra/iloveyou.gif" alt="iloveyou" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cool2')"><img src="images/smilies/extra/cool2.gif" alt="cool2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cool3')"><img src="images/smilies/extra/cool3.gif" alt="cool3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cool4')"><img src="images/smilies/extra/cool4.gif" alt="cool4" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('square')"><img src="images/smilies/extra/square.gif" alt="square" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bomb2')"><img src="images/smilies/extra/bomb2.gif" alt="bomb2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('rolleyes')"><img src="images/smilies/extra/rolleyes.gif" alt="rolleyes" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('santa2')"><img src="images/smilies/extra/santa2.gif" alt="santa2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('santa3')"><img src="images/smilies/extra/santa3.gif" alt="santa3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('charming')"><img src="images/smilies/extra/charming.gif" alt="charming" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('disguise')"><img src="images/smilies/extra/disguise.gif" alt="disguise" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('gossip')"><img src="images/smilies/extra/gossip.gif" alt="gossip" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('beadyeyes')"><img src="images/smilies/extra/beadyeyes.gif" alt="beadyeyes" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cowboy')"><img src="images/smilies/extra/cowboy.gif" alt="cowboy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('chinese')"><img src="images/smilies/extra/chinese.gif" alt="chinese" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dunce')"><img src="images/smilies/extra/dunce.gif" alt="dunce" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('worried2')"><img src="images/smilies/extra/worried2.gif" alt="worried2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shame')"><img src="images/smilies/extra/shame.gif" alt="shame" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('knife')"><img src="images/smilies/extra/knife.gif" alt="knife" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hat2')"><img src="images/smilies/extra/hat2.gif" alt="hat2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('freak')"><img src="images/smilies/extra/freak.gif" alt="freak" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dead')"><img src="images/smilies/extra/dead.gif" alt="dead" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('whip')"><img src="images/smilies/extra/whip.gif" alt="whip" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('inquisitive')"><img src="images/smilies/extra/inquisitive.gif" alt="inquisitive" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('deal')"><img src="images/smilies/extra/deal.gif" alt="deal" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smoking')"><img src="images/smilies/extra/smoking.gif" alt="smoking" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('party2')"><img src="images/smilies/extra/party2.gif" alt="party2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('angel')"><img src="images/smilies/extra/angel.gif" alt="angel" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('party3')"><img src="images/smilies/extra/party3.gif" alt="party3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('kiss')"><img src="images/smilies/extra/kiss.gif" alt="kiss" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('pimp')"><img src="images/smilies/extra/pimp.gif" alt="pimp" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wall')"><img src="images/smilies/extra/wall.gif" alt="wall" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('uneasy')"><img src="images/smilies/extra/uneasy.gif" alt="uneasy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('uhoh2')"><img src="images/smilies/extra/uhoh2.gif" alt="uhoh2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('beadyeyes2')"><img src="images/smilies/extra/beadyeyes2.gif" alt="beadyeyes2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('glasses2')"><img src="images/smilies/extra/glasses2.gif" alt="glasses2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('builder')"><img src="images/smilies/extra/builder.gif" alt="builder" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('mask')"><img src="images/smilies/extra/mask.gif" alt="mask" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('crown')"><img src="images/smilies/extra/crown.gif" alt="crown" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('pumpkin')"><img src="images/smilies/extra/pumpkin.gif" alt="pumpkin" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cool')"><img src="images/smilies/extra/cool.gif" alt="cool" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smash')"><img src="images/smilies/extra/smash.gif" alt="smash" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smart')"><img src="images/smilies/extra/smart.gif" alt="smart" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dozey')"><img src="images/smilies/extra/dozey.gif" alt="dozey" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('zzz')"><img src="images/smilies/extra/zzz.gif" alt="zzz" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smartass')"><img src="images/smilies/extra/smartass.gif" alt="smartass" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wideeyed')"><img src="images/smilies/extra/wideeyed.gif" alt="wideeyed" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('alien')"><img src="images/smilies/extra/alien.gif" alt="alien" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shocked2')"><img src="images/smilies/extra/shocked2.gif" alt="shocked2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('klingon')"><img src="images/smilies/extra/klingon.gif" alt="klingon" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shocked3')"><img src="images/smilies/extra/shocked3.gif" alt="shocked3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smile')"><img src="images/smilies/extra/smile.gif" alt="smile" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('anxious')"><img src="images/smilies/extra/anxious.gif" alt="anxious" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('jester')"><img src="images/smilies/extra/jester.gif" alt="jester" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bobby2')"><img src="images/smilies/extra/bobby2.gif" alt="bobby2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('toilet')"><img src="images/smilies/extra/toilet.gif" alt="toilet" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shout')"><img src="images/smilies/extra/shout.gif" alt="shout" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dizzy2')"><img src="images/smilies/extra/dizzy2.gif" alt="dizzy2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hat')"><img src="images/smilies/extra/hat.gif" alt="hat" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('antlers')"><img src="images/smilies/extra/antlers.gif" alt="antlers" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('crowngrin')"><img src="images/smilies/extra/crowngrin.gif" alt="crowngrin" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('annoyed')"><img src="images/smilies/extra/annoyed.gif" alt="annoyed" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shocked')"><img src="images/smilies/extra/shocked.gif" alt="shocked" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('no')"><img src="images/smilies/extra/no.gif" alt="no" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('happy2')"><img src="images/smilies/extra/happy2.gif" alt="happy2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('toff')"><img src="images/smilies/extra/toff.gif" alt="toff" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('idea2')"><img src="images/smilies/extra/idea2.gif" alt="idea2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('idea3')"><img src="images/smilies/extra/idea3.gif" alt="idea3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('dunce2')"><img src="images/smilies/extra/dunce2.gif" alt="dunce2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('mickey')"><img src="images/smilies/extra/mickey.gif" alt="mickey" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('biker')"><img src="images/smilies/extra/biker.gif" alt="biker" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('evil')"><img src="images/smilies/extra/evil.gif" alt="evil" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('angry')"><img src="images/smilies/extra/angry.gif" alt="angry" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('fireman')"><img src="images/smilies/extra/fireman.gif" alt="fireman" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wink')"><img src="images/smilies/extra/wink.gif" alt="wink" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('scholar')"><img src="images/smilies/extra/scholar.gif" alt="scholar" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('behead')"><img src="images/smilies/extra/behead.gif" alt="behead" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('greedy')"><img src="images/smilies/extra/greedy.gif" alt="greedy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('goofy')"><img src="images/smilies/extra/goofy.gif" alt="goofy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('furious')"><img src="images/smilies/extra/furious.gif" alt="furious" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('xmas')"><img src="images/smilies/extra/xmas.gif" alt="xmas" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('juggle')"><img src="images/smilies/extra/juggle.gif" alt="juggle" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('pleased')"><img src="images/smilies/extra/pleased.gif" alt="pleased" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('vampire')"><img src="images/smilies/extra/vampire.gif" alt="vampire" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('oops')"><img src="images/smilies/extra/oops.gif" alt="oops" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('shifty')"><img src="images/smilies/extra/shifty.gif" alt="shifty" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('grin2')"><img src="images/smilies/extra/grin2.gif" alt="grin2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('grin3')"><img src="images/smilies/extra/grin3.gif" alt="grin3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('Mr-T')"><img src="images/smilies/extra/Mr-T.gif" alt="Mr-T" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('ears')"><img src="images/smilies/extra/ears.gif" alt="ears" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('beatnik2')"><img src="images/smilies/extra/beatnik2.gif" alt="beatnik2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('baby')"><img src="images/smilies/extra/baby.gif" alt="baby" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cyclist')"><img src="images/smilies/extra/cyclist.gif" alt="cyclist" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bulb')"><img src="images/smilies/extra/bulb.gif" alt="bulb" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('lipsrsealed')"><img src="images/smilies/extra/lipsrsealed.gif" alt="lipsrsealed" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('army')"><img src="images/smilies/extra/army.gif" alt="army" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sultan')"><img src="images/smilies/extra/sultan.gif" alt="sultan" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bucktooth')"><img src="images/smilies/extra/bucktooth.gif" alt="bucktooth" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('joker')"><img src="images/smilies/extra/joker.gif" alt="joker" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stare')"><img src="images/smilies/extra/stare.gif" alt="stare" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('helmet')"><img src="images/smilies/extra/helmet.gif" alt="helmet" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stars')"><img src="images/smilies/extra/stars.gif" alt="stars" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('glasses')"><img src="images/smilies/extra/glasses.gif" alt="glasses" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('surprised')"><img src="images/smilies/extra/surprised.gif" alt="surprised" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('elvis')"><img src="images/smilies/extra/elvis.gif" alt="elvis" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('computer')"><img src="images/smilies/extra/computer.gif" alt="computer" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wacko')"><img src="images/smilies/extra/wacko.gif" alt="wacko" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('book2')"><img src="images/smilies/extra/book2.gif" alt="book2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('brood')"><img src="images/smilies/extra/brood.gif" alt="brood" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hanged')"><img src="images/smilies/extra/hanged.gif" alt="hanged" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('clown')"><img src="images/smilies/extra/clown.gif" alt="clown" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('wacky')"><img src="images/smilies/extra/wacky.gif" alt="wacky" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('drunk')"><img src="images/smilies/extra/drunk.gif" alt="drunk" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('batman')"><img src="images/smilies/extra/batman.gif" alt="batman" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('mean')"><img src="images/smilies/extra/mean.gif" alt="mean" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('huh')"><img src="images/smilies/extra/huh.gif" alt="huh" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('vanish')"><img src="images/smilies/extra/vanish.gif" alt="vanish" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('disappointed')"><img src="images/smilies/extra/disappointed.gif" alt="disappointed" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('balloon')"><img src="images/smilies/extra/balloon.gif" alt="balloon" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bomb')"><img src="images/smilies/extra/bomb.gif" alt="bomb" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('snobby')"><img src="images/smilies/extra/snobby.gif" alt="snobby" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sneaky')"><img src="images/smilies/extra/sneaky.gif" alt="sneaky" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('worried')"><img src="images/smilies/extra/worried.gif" alt="worried" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('guitarist')"><img src="images/smilies/extra/guitarist.gif" alt="guitarist" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('devilish')"><img src="images/smilies/extra/devilish.gif" alt="devilish" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bobby')"><img src="images/smilies/extra/bobby.gif" alt="bobby" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('tiny')"><img src="images/smilies/extra/tiny.gif" alt="tiny" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('policeman')"><img src="images/smilies/extra/policeman.gif" alt="policeman" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stop')"><img src="images/smilies/extra/stop.gif" alt="stop" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('thumbsup')"><img src="images/smilies/extra/thumbsup.gif" alt="thumbsup" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('daisy')"><img src="images/smilies/extra/daisy.gif" alt="daisy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('book')"><img src="images/smilies/extra/book.gif" alt="book" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sad2')"><img src="images/smilies/extra/sad2.gif" alt="sad2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sad3')"><img src="images/smilies/extra/sad3.gif" alt="sad3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('sweatdrop')"><img src="images/smilies/extra/sweatdrop.gif" alt="sweatdrop" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bow')"><img src="images/smilies/extra/bow.gif" alt="bow" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('thumbsdown')"><img src="images/smilies/extra/thumbsdown.gif" alt="thumbsdown" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('embarassed')"><img src="images/smilies/extra/embarassed.gif" alt="embarassed" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('curtain')"><img src="images/smilies/extra/curtain.gif" alt="curtain" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('evil2')"><img src="images/smilies/extra/evil2.gif" alt="evil2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('evil3')"><img src="images/smilies/extra/evil3.gif" alt="evil3" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('bulb2')"><img src="images/smilies/extra/bulb2.gif" alt="bulb2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('hippy')"><img src="images/smilies/extra/hippy.gif" alt="hippy" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('indian_brave')"><img src="images/smilies/extra/indian_brave.gif" alt="indian_brave" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('smug')"><img src="images/smilies/extra/smug.gif" alt="smug" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('lost')"><img src="images/smilies/extra/lost.gif" alt="lost" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('deal2')"><img src="images/smilies/extra/deal2.gif" alt="deal2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('love')"><img src="images/smilies/extra/love.gif" alt="love" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('tired')"><img src="images/smilies/extra/tired.gif" alt="tired" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('evilgrin')"><img src="images/smilies/extra/evilgrin.gif" alt="evilgrin" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('stooge_curly')"><img src="images/smilies/extra/stooge_curly.gif" alt="stooge_curly" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('operator')"><img src="images/smilies/extra/operator.gif" alt="operator" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('artist')"><img src="images/smilies/extra/artist.gif" alt="artist" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('help')"><img src="images/smilies/extra/help.gif" alt="help" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('cry2')"><img src="images/smilies/extra/cry2.gif" alt="cry2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('lipsrsealed2')"><img src="images/smilies/extra/lipsrsealed2.gif" alt="lipsrsealed2" class="smiley" /></a>
				<a href="javascript:getExtraSmiley('mellow')"><img src="images/smilies/extra/mellow.gif" alt="mellow" class="smiley" /></a>
			</td>
		</tr>
	</table>
</body>
</html>
eot;

	$this->Io->out($smilies);
	}

}

?>