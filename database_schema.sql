-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 26. Mai 2008 um 18:16
-- Server Version: 5.0.51
-- PHP-Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `current`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attachments`
--

CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `size` int(10) unsigned NOT NULL default '0',
  `type` varchar(100) NOT NULL default '',
  `userid` int(10) unsigned NOT NULL default '0',
  `uploaded` int(10) unsigned NOT NULL default '0',
  `content` mediumblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `userid` (`userid`),
  KEY `uploaded` (`uploaded`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attachment_thumbnails`
--

CREATE TABLE `attachment_thumbnails` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `size` int(10) unsigned NOT NULL default '0',
  `type` varchar(100) NOT NULL default '',
  `content` mediumblob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `avatars`
--

CREATE TABLE `avatars` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `size` int(10) unsigned NOT NULL default '0',
  `type` varchar(100) NOT NULL default '',
  `content` mediumblob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `boards`
--

CREATE TABLE `boards` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `admin` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL default '',
  `regdate` int(10) unsigned NOT NULL,
  `posts` int(10) unsigned NOT NULL default '0',
  `threads` int(10) unsigned NOT NULL default '0',
  `lastpost` int(10) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `admins` int(10) unsigned NOT NULL default '0',
  `mods` int(10) unsigned NOT NULL default '0',
  `host` varchar(255) NOT NULL,
  `html` text NOT NULL,
  `css` text NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_address` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_tel` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host` (`host`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumblob NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY  (`key`(30)),
  KEY `expires` (`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cats`
--

CREATE TABLE `cats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `boardid` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `position` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `boardid` (`boardid`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `forums`
--

CREATE TABLE `forums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `boardid` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(200) NOT NULL default '',
  `lastdate` int(10) unsigned default NULL,
  `lastposter` int(10) unsigned default NULL,
  `lastthread` int(10) unsigned default NULL,
  `threads` int(10) unsigned NOT NULL default '0',
  `posts` int(10) unsigned NOT NULL default '0',
  `mods` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `boardid` (`boardid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `forum_cat`
--

CREATE TABLE `forum_cat` (
  `catid` int(10) unsigned NOT NULL default '0',
  `forumid` int(10) unsigned NOT NULL default '0',
  `position` tinyint(3) unsigned NOT NULL default '0',
  KEY `catid` (`catid`),
  KEY `forumid` (`forumid`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `images`
--

CREATE TABLE `images` (
  `url` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `content` mediumblob NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `thumbcontent` mediumblob NOT NULL,
  `thumbsize` int(10) unsigned NOT NULL,
  `lastupdate` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `password_key`
--

CREATE TABLE `password_key` (
  `id` int(10) unsigned NOT NULL,
  `key` varchar(40) NOT NULL,
  `request_time` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plz`
--

CREATE TABLE `plz` (
  `location` varchar(100) NOT NULL default '',
  `country` varchar(3) NOT NULL default '',
  `code` mediumint(5) unsigned NOT NULL default '0',
  `length` varchar(5) NOT NULL default '',
  `width` varchar(5) NOT NULL default '',
  `x` smallint(3) unsigned NOT NULL default '0',
  `y` smallint(3) unsigned NOT NULL default '0',
  KEY `code` (`code`),
  KEY `location` (`location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `polls`
--

CREATE TABLE `polls` (
  `id` int(10) unsigned NOT NULL default '0',
  `question` varchar(200) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll_values`
--

CREATE TABLE `poll_values` (
  `pollid` int(10) unsigned NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `votes` smallint(5) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll_voters`
--

CREATE TABLE `poll_voters` (
  `pollid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  KEY `pollid` (`pollid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posts`
--

CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `threadid` int(10) unsigned NOT NULL default '0',
  `counter` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `username` varchar(25) NOT NULL default '0',
  `dat` int(10) unsigned NOT NULL default '0',
  `editdate` int(10) unsigned NOT NULL default '0',
  `editby` int(10) unsigned NOT NULL default '0',
  `smilies` tinyint(1) NOT NULL default '1',
  `deleted` tinyint(1) NOT NULL default '0',
  `text` text NOT NULL,
  `file` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `threadid` (`threadid`),
  KEY `userid` (`userid`),
  KEY `deleted` (`deleted`),
  KEY `dat` (`dat`),
  KEY `counter` (`counter`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `post_attachments`
--

CREATE TABLE `post_attachments` (
  `postid` int(10) unsigned NOT NULL default '0',
  `attachment_id` int(10) unsigned NOT NULL default '0',
  KEY `postid` (`postid`),
  KEY `fileid` (`attachment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `session`
--

CREATE TABLE `session` (
  `sessionid` varchar(40) NOT NULL,
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(25) NOT NULL default '',
  `level` tinyint(1) unsigned NOT NULL default '0',
  `groups` varchar(200) NOT NULL default '',
  `lastupdate` int(10) unsigned NOT NULL default '0',
  `security_token` varchar(40) NOT NULL,
  `boardid` int(10) unsigned NOT NULL default '0',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sessionid`),
  KEY `lastupdate` (`lastupdate`),
  KEY `id` (`id`),
  KEY `boardid` (`boardid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=300 AVG_ROW_LENGTH=300;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags`
--

CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `boardid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `boardid` (`boardid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `threads`
--

CREATE TABLE `threads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `forumid` int(10) unsigned NOT NULL default '0',
  `counter` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `sticky` tinyint(1) NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `poll` tinyint(1) unsigned NOT NULL default '0',
  `lastdate` int(10) unsigned NOT NULL default '0',
  `lastuserid` int(10) unsigned NOT NULL default '0',
  `posts` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `firstdate` int(10) unsigned NOT NULL default '0',
  `firstuserid` int(10) unsigned NOT NULL default '0',
  `firstusername` varchar(25) NOT NULL default '',
  `lastusername` varchar(25) NOT NULL default '',
  `movedfrom` int(10) unsigned NOT NULL default '0',
  `summary` text,
  `tag` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `forumid` (`forumid`),
  KEY `deleted` (`deleted`),
  KEY `lastuserid` (`lastuserid`),
  KEY `firstuserid` (`firstuserid`),
  KEY `sticky` (`sticky`),
  KEY `movedfrom` (`movedfrom`),
  KEY `closed` (`closed`),
  KEY `firstdate` (`firstdate`),
  KEY `lastdate` (`lastdate`),
  KEY `counter` (`counter`),
  KEY `tag` (`tag`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `threads_log`
--

CREATE TABLE `threads_log` (
  `threadid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `dat` int(10) unsigned NOT NULL default '0',
  KEY `threadid` (`threadid`),
  KEY `userid` (`userid`),
  KEY `dat` (`dat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `thread_user`
--

CREATE TABLE `thread_user` (
  `userid` int(10) unsigned NOT NULL default '0',
  `threadid` int(10) unsigned NOT NULL default '0',
  KEY `userid` (`userid`),
  KEY `threadid` (`threadid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  `realname` varchar(100) NOT NULL default '',
  `password` varchar(40) NOT NULL,
  `email` varchar(100) NOT NULL default '',
  `birthday` int(11) NOT NULL default '0',
  `posts` int(10) unsigned NOT NULL default '0',
  `regdate` int(10) unsigned NOT NULL default '0',
  `level` tinyint(1) unsigned NOT NULL default '0',
  `gender` tinyint(1) unsigned NOT NULL default '0',
  `lastpost` int(10) unsigned NOT NULL default '0',
  `avatar` tinyint(1) unsigned NOT NULL default '0',
  `location` varchar(255) default NULL,
  `plz` mediumint(5) unsigned default NULL,
  `text` text,
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(10)),
  KEY `posts` (`posts`),
  KEY `regdate` (`regdate`),
  KEY `realname` (`realname`),
  KEY `lastpost` (`lastpost`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_group`
--

CREATE TABLE `user_group` (
  `userid` int(10) unsigned NOT NULL default '0',
  `groupid` int(10) unsigned NOT NULL default '0',
  KEY `userid` (`userid`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
