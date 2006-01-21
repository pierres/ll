-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. Januar 2006 um 17:47
-- Server Version: 5.0.18
-- PHP-Version: 5.1.1
--
-- Datenbank: `current`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `boards`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:36
--

CREATE TABLE `boards` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `admin` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `regdate` int(11) unsigned NOT NULL default '0',
  `posts` mediumint(8) unsigned NOT NULL default '0',
  `threads` mediumint(8) unsigned NOT NULL default '0',
  `lastpost` int(11) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `admins` mediumint(8) unsigned NOT NULL default '0',
  `mods` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cats`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `cats` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `boardid` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `position` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `boardid` (`boardid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `files`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `files` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `size` mediumint(6) unsigned NOT NULL default '0',
  `type` varchar(100) NOT NULL default '',
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `uploaded` int(11) unsigned NOT NULL default '0',
  `content` mediumblob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `forum_cat`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `forum_cat` (
  `catid` mediumint(8) unsigned NOT NULL default '0',
  `forumid` mediumint(8) unsigned NOT NULL default '0',
  `position` tinyint(3) unsigned NOT NULL default '0',
  KEY `catid` (`catid`),
  KEY `forumid` (`forumid`,`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `forums`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `forums` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `boardid` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(200) NOT NULL default '',
  `lastdate` int(11) unsigned default NULL,
  `lastposter` mediumint(8) unsigned default NULL,
  `lastthread` mediumint(8) unsigned default NULL,
  `threads` mediumint(8) unsigned NOT NULL default '0',
  `posts` mediumint(8) unsigned NOT NULL default '0',
  `mods` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `boardid` (`boardid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `images`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `images` (
  `url` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `content` mediumblob NOT NULL,
  `size` mediumint(6) unsigned NOT NULL,
  `thumbcontent` mediumblob NOT NULL,
  `thumbsize` mediumint(6) unsigned NOT NULL,
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plz`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `plz` (
  `location` varchar(100) NOT NULL default '',
  `country` varchar(3) NOT NULL default '',
  `code` mediumint(5) unsigned NOT NULL default '0',
  `length` varchar(5) NOT NULL default '',
  `width` varchar(5) NOT NULL default '',
  `x` smallint(3) unsigned NOT NULL default '0',
  `y` smallint(3) unsigned NOT NULL default '0',
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll_values`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `poll_values` (
  `pollid` mediumint(8) unsigned NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `votes` smallint(5) unsigned NOT NULL default '0',
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll_voters`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `poll_voters` (
  `pollid` mediumint(8) unsigned NOT NULL default '0',
  `userid` mediumint(8) unsigned NOT NULL default '0',
  KEY `pollid` (`pollid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `polls`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `polls` (
  `id` mediumint(8) unsigned NOT NULL default '0',
  `question` varchar(200) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `post_file`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `post_file` (
  `postid` mediumint(8) unsigned NOT NULL default '0',
  `fileid` mediumint(8) unsigned NOT NULL default '0',
  KEY `postid` (`postid`),
  KEY `fileid` (`fileid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posts`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `posts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `threadid` mediumint(8) unsigned NOT NULL default '0',
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(25) NOT NULL default '0',
  `dat` int(11) unsigned NOT NULL default '0',
  `editdate` int(11) unsigned NOT NULL default '0',
  `editby` mediumint(8) unsigned NOT NULL default '0',
  `smilies` tinyint(1) NOT NULL default '1',
  `deleted` tinyint(1) NOT NULL default '0',
  `text` text NOT NULL,
  `file` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `threadid` (`threadid`),
  KEY `userid` (`userid`),
  KEY `deleted` (`deleted`),
  KEY `dat` (`dat`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `session`
--

CREATE TABLE `session` (
  `sessionid` varchar(32) NOT NULL default '',
  `id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(25) NOT NULL default '',
  `level` tinyint(1) unsigned NOT NULL default '0',
  `groups` varchar(200) NOT NULL default '',
  `lastupdate` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sessionid`),
  KEY `id` (`id`),
  KEY `lastupdate` (`lastupdate`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=300 AVG_ROW_LENGTH=300;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `thread_user`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `thread_user` (
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `threadid` mediumint(8) unsigned NOT NULL default '0',
  KEY `userid` (`userid`),
  KEY `threadid` (`threadid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `threads`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `threads` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `forumid` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `sticky` tinyint(1) unsigned NOT NULL default '0',
  `closed` tinyint(1) unsigned NOT NULL default '0',
  `poll` tinyint(1) unsigned NOT NULL default '0',
  `lastdate` int(11) unsigned NOT NULL default '0',
  `lastuserid` mediumint(8) unsigned NOT NULL default '0',
  `posts` mediumint(8) unsigned NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `firstdate` int(11) unsigned NOT NULL default '0',
  `firstuserid` mediumint(8) unsigned NOT NULL default '0',
  `firstusername` varchar(25) NOT NULL default '',
  `lastusername` varchar(25) NOT NULL default '',
  `movedfrom` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `forumid` (`forumid`),
  KEY `dat` (`lastdate`),
  KEY `deleted` (`deleted`),
  KEY `lastuserid` (`lastuserid`),
  KEY `firstuserid` (`firstuserid`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `threads_log`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `threads_log` (
  `threadid` mediumint(8) unsigned NOT NULL default '0',
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `dat` int(11) unsigned NOT NULL default '0',
  KEY `threadid` (`threadid`),
  KEY `userid` (`userid`),
  KEY `dat` (`dat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_group`
--
-- Erzeugt am: 21. Januar 2006 um 17:18
-- Aktualisiert am: 21. Januar 2006 um 17:18
--

CREATE TABLE `user_group` (
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `groupid` mediumint(8) unsigned NOT NULL default '0',
  KEY `userid` (`userid`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--
-- Erzeugt am: 21. Januar 2006 um 17:40
-- Aktualisiert am: 21. Januar 2006 um 17:40
--

CREATE TABLE `users` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  `realname` varchar(100) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `birthday` int(11) NOT NULL default '0',
  `posts` smallint(5) unsigned NOT NULL default '0',
  `regdate` int(11) unsigned NOT NULL default '0',
  `level` tinyint(1) unsigned NOT NULL default '0',
  `gender` tinyint(1) unsigned NOT NULL default '0',
  `lastpost` int(11) unsigned NOT NULL default '0',
  `avatar` mediumint(8) unsigned NOT NULL default '0',
  `location` varchar(255) default NULL,
  `plz` mediumint(5) unsigned default NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;