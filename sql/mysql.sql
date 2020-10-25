#
# Table structure for table `phpbbi_auth_access`
#

CREATE TABLE `phpbbi_auth_access` (
    `groupid`          MEDIUMINT(8)         NOT NULL DEFAULT '0',
    `forum_id`         SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    `auth_view`        TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_read`        TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_post`        TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_reply`       TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_edit`        TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_delete`      TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_sticky`      TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_announce`    TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_vote`        TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_pollcreate`  TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_attachments` TINYINT(1)           NOT NULL DEFAULT '0',
    `auth_mod`         TINYINT(1)           NOT NULL DEFAULT '0',
    KEY `group_id` (`groupid`),
    KEY `forum_id` (`forum_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_auth_access`
#

# --------------------------------------------------------

#
# Table structure for table `phpbbi_banlist`
#

CREATE TABLE `phpbbi_banlist` (
    `ban_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ban_userid` MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `ban_ip`     VARCHAR(15)           NOT NULL DEFAULT '',
    `ban_email`  VARCHAR(255)                   DEFAULT NULL,
    PRIMARY KEY (`ban_id`),
    KEY `ban_ip_user_id` (`ban_ip`, `ban_userid`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_banlist`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_categories`
#

CREATE TABLE `phpbbi_categories` (
    `cat_id`    MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cat_title` VARCHAR(100)                   DEFAULT NULL,
    `cat_order` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`cat_id`),
    KEY `cat_order` (`cat_order`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_categories`
#

INSERT INTO `phpbbi_categories` (cat_id, cat_title, cat_order)
VALUES (1, 'Test category 1', 10);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_config`
#

CREATE TABLE `phpbbi_config` (
    `config_name`  VARCHAR(255) NOT NULL DEFAULT '',
    `config_value` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`config_name`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_config`
#

INSERT INTO `phpbbi_config`
VALUES ('config_id', '1');
INSERT INTO `phpbbi_config`
VALUES ('board_disable', '0');
INSERT INTO `phpbbi_config`
VALUES ('sitename', 'yourdomain.com');
INSERT INTO `phpbbi_config`
VALUES ('site_desc', 'A _little_ text to describe your forum');
INSERT INTO `phpbbi_config`
VALUES ('cookie_name', 'phpbb2mysql');
INSERT INTO `phpbbi_config`
VALUES ('cookie_path', '/');
INSERT INTO `phpbbi_config`
VALUES ('cookie_domain', '');
INSERT INTO `phpbbi_config`
VALUES ('cookie_secure', '0');
INSERT INTO `phpbbi_config`
VALUES ('session_length', '3600');
INSERT INTO `phpbbi_config`
VALUES ('allow_html', '0');
INSERT INTO `phpbbi_config`
VALUES ('allow_html_tags', 'b,i,u,pre');
INSERT INTO `phpbbi_config`
VALUES ('allow_bbcode', '1');
INSERT INTO `phpbbi_config`
VALUES ('allow_smilies', '1');
INSERT INTO `phpbbi_config`
VALUES ('allow_sig', '1');
INSERT INTO `phpbbi_config`
VALUES ('allow_namechange', '0');
INSERT INTO `phpbbi_config`
VALUES ('allow_theme_create', '0');
INSERT INTO `phpbbi_config`
VALUES ('allow_avatar_local', '0');
INSERT INTO `phpbbi_config`
VALUES ('allow_avatar_remote', '0');
INSERT INTO `phpbbi_config`
VALUES ('allow_avatar_upload', '0');
INSERT INTO `phpbbi_config`
VALUES ('enable_confirm', '0');
INSERT INTO `phpbbi_config`
VALUES ('override_user_style', '1');
INSERT INTO `phpbbi_config`
VALUES ('posts_per_page', '15');
INSERT INTO `phpbbi_config`
VALUES ('topics_per_page', '50');
INSERT INTO `phpbbi_config`
VALUES ('hot_threshold', '25');
INSERT INTO `phpbbi_config`
VALUES ('max_poll_options', '10');
INSERT INTO `phpbbi_config`
VALUES ('max_sig_chars', '255');
INSERT INTO `phpbbi_config`
VALUES ('max_inbox_privmsgs', '50');
INSERT INTO `phpbbi_config`
VALUES ('max_sentbox_privmsgs', '25');
INSERT INTO `phpbbi_config`
VALUES ('max_savebox_privmsgs', '50');
INSERT INTO `phpbbi_config`
VALUES ('board_email_sig', 'Thanks, The Management');
INSERT INTO `phpbbi_config`
VALUES ('board_email', 'youraddress@yourdomain.com');
INSERT INTO `phpbbi_config`
VALUES ('smtp_delivery', '0');
INSERT INTO `phpbbi_config`
VALUES ('smtp_host', '');
INSERT INTO `phpbbi_config`
VALUES ('smtp_username', '');
INSERT INTO `phpbbi_config`
VALUES ('smtp_password', '');
INSERT INTO `phpbbi_config`
VALUES ('sendmail_fix', '0');
INSERT INTO `phpbbi_config`
VALUES ('require_activation', '0');
INSERT INTO `phpbbi_config`
VALUES ('flood_interval', '15');
INSERT INTO `phpbbi_config`
VALUES ('board_email_form', '0');
INSERT INTO `phpbbi_config`
VALUES ('avatar_filesize', '6144');
INSERT INTO `phpbbi_config`
VALUES ('avatar_max_width', '80');
INSERT INTO `phpbbi_config`
VALUES ('avatar_max_height', '80');
INSERT INTO `phpbbi_config`
VALUES ('avatar_path', '../../uploads');
INSERT INTO `phpbbi_config`
VALUES ('avatar_gallery_path', '../../uploads');
INSERT INTO `phpbbi_config`
VALUES ('smilies_path', '../../uploads');
INSERT INTO `phpbbi_config`
VALUES ('default_style', '1');
INSERT INTO `phpbbi_config`
VALUES ('default_dateformat', 'D M d, Y g:i a');
INSERT INTO `phpbbi_config`
VALUES ('board_timezone', '0');
INSERT INTO `phpbbi_config`
VALUES ('prune_enable', '1');
INSERT INTO `phpbbi_config`
VALUES ('privmsg_disable', '0');
INSERT INTO `phpbbi_config`
VALUES ('gzip_compress', '0');
INSERT INTO `phpbbi_config`
VALUES ('coppa_fax', '');
INSERT INTO `phpbbi_config`
VALUES ('coppa_mail', '');
INSERT INTO `phpbbi_config`
VALUES ('record_online_users', '1');
INSERT INTO `phpbbi_config`
VALUES ('record_online_date', '1101771307');
INSERT INTO `phpbbi_config`
VALUES ('server_name', '');
INSERT INTO `phpbbi_config`
VALUES ('server_port', '80');
INSERT INTO `phpbbi_config`
VALUES ('script_path', '/modules/xphpbbi/');
INSERT INTO `phpbbi_config`
VALUES ('version', '.0.9');
INSERT INTO `phpbbi_config`
VALUES ('version_bb', '0.7');
INSERT INTO `phpbbi_config`
VALUES ('board_startdate', '1101700709');
INSERT INTO `phpbbi_config`
VALUES ('default_lang', 'english');

# --------------------------------------------------------

#
# Table structure for table `phpbbi_confirm`
#

CREATE TABLE `phpbbi_confirm` (
    `confirm_id` CHAR(32) NOT NULL DEFAULT '',
    `session_id` CHAR(32) NOT NULL DEFAULT '',
    `code`       CHAR(6)  NOT NULL DEFAULT '',
    PRIMARY KEY (`session_id`, `confirm_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_confirm`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_disallow`
#

CREATE TABLE `phpbbi_disallow` (
    `disallow_id`       MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `disallow_username` VARCHAR(25)           NOT NULL DEFAULT '',
    PRIMARY KEY (`disallow_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_disallow`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_forum_prune`
#

CREATE TABLE `phpbbi_forum_prune` (
    `prune_id`   MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `forum_id`   SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `prune_days` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `prune_freq` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    PRIMARY KEY (`prune_id`),
    KEY `forum_id` (`forum_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_forum_prune`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_forums`
#

CREATE TABLE `phpbbi_forums` (
    `forum_id`           SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `cat_id`             MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `forum_name`         VARCHAR(150)                   DEFAULT NULL,
    `forum_desc`         TEXT,
    `forum_status`       TINYINT(4)            NOT NULL DEFAULT '0',
    `forum_order`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    `forum_posts`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `forum_topics`       MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `forum_last_post_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `prune_next`         INT(11)                        DEFAULT NULL,
    `prune_enable`       TINYINT(1)            NOT NULL DEFAULT '0',
    `auth_view`          TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_read`          TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_post`          TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_reply`         TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_edit`          TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_delete`        TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_sticky`        TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_announce`      TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_vote`          TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_pollcreate`    TINYINT(2)            NOT NULL DEFAULT '0',
    `auth_attachments`   TINYINT(2)            NOT NULL DEFAULT '0',
    PRIMARY KEY (`forum_id`),
    KEY `forums_order` (`forum_order`),
    KEY `cat_id` (`cat_id`),
    KEY `forum_last_post_id` (`forum_last_post_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_forums`
#

INSERT INTO `phpbbi_forums` (forum_id, forum_name, forum_desc, cat_id, forum_order, forum_posts, forum_topics, forum_last_post_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_pollcreate, auth_vote, auth_attachments)
VALUES (1, 'Test Forum 1', 'This is just a test forum.', 1, 10, 1, 1, 1, 0, 0, 0, 0, 1, 1, 3, 3, 1, 1, 3);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_posts`
#

CREATE TABLE `phpbbi_posts` (
    `post_id`         MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `topic_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `forum_id`        SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `poster_id`       MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `post_time`       INT(11)               NOT NULL DEFAULT '0',
    `poster_ip`       VARCHAR(15)           NOT NULL DEFAULT '',
    `post_username`   VARCHAR(25)                    DEFAULT NULL,
    `enable_bbcode`   TINYINT(1)            NOT NULL DEFAULT '1',
    `enable_html`     TINYINT(1)            NOT NULL DEFAULT '0',
    `enable_smilies`  TINYINT(1)            NOT NULL DEFAULT '1',
    `enable_sig`      TINYINT(1)            NOT NULL DEFAULT '1',
    `post_edit_time`  INT(11)                        DEFAULT NULL,
    `post_edit_count` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    PRIMARY KEY (`post_id`),
    KEY `forum_id` (`forum_id`),
    KEY `topic_id` (`topic_id`),
    KEY `poster_id` (`poster_id`),
    KEY `post_time` (`post_time`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_posts`
#

INSERT INTO `phpbbi_posts` (post_id, topic_id, forum_id, poster_id, post_time, post_username, poster_ip)
VALUES (1, 1, 1, 1, 972086460, NULL, '127.0.0.1');

# --------------------------------------------------------

#
# Table structure for table `phpbbi_posts_text`
#

CREATE TABLE `phpbbi_posts_text` (
    `post_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `bbcode_uid`   VARCHAR(10)           NOT NULL DEFAULT '',
    `post_subject` VARCHAR(60)                    DEFAULT NULL,
    `post_text`    TEXT,
    PRIMARY KEY (`post_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_posts_text`
#

INSERT INTO `phpbbi_posts_text` (post_id, post_subject, post_text)
VALUES (1, NULL, 'This is an example post in your phpBB 2 installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!');

# --------------------------------------------------------

#
# Table structure for table `phpbbi_privmsgs`
#

CREATE TABLE `phpbbi_privmsgs` (
    `privmsgs_id`             MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `privmsgs_type`           TINYINT(4)            NOT NULL DEFAULT '0',
    `privmsgs_subject`        VARCHAR(255)          NOT NULL DEFAULT '0',
    `privmsgs_from_userid`    MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `privmsgs_to_userid`      MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `privmsgs_date`           INT(11)               NOT NULL DEFAULT '0',
    `privmsgs_ip`             VARCHAR(15)           NOT NULL DEFAULT '',
    `privmsgs_enable_bbcode`  TINYINT(1)            NOT NULL DEFAULT '1',
    `privmsgs_enable_html`    TINYINT(1)            NOT NULL DEFAULT '0',
    `privmsgs_enable_smilies` TINYINT(1)            NOT NULL DEFAULT '1',
    `privmsgs_attach_sig`     TINYINT(1)            NOT NULL DEFAULT '1',
    PRIMARY KEY (`privmsgs_id`),
    KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
    KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_privmsgs`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_privmsgs_text`
#

CREATE TABLE `phpbbi_privmsgs_text` (
    `privmsgs_text_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `privmsgs_bbcode_uid` VARCHAR(10)           NOT NULL DEFAULT '0',
    `privmsgs_text`       TEXT,
    PRIMARY KEY (`privmsgs_text_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_privmsgs_text`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_search_results`
#

CREATE TABLE `phpbbi_search_results` (
    `search_id`    INT(11) UNSIGNED NOT NULL DEFAULT '0',
    `session_id`   VARCHAR(32)      NOT NULL DEFAULT '',
    `search_array` TEXT             NOT NULL,
    PRIMARY KEY (`search_id`),
    KEY `session_id` (`session_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_search_results`
#

INSERT INTO `phpbbi_search_results`
VALUES (25190736, '', 'a:7:{s:14:"search_results";s:1:"1";s:17:"total_match_count";i:1;s:12:"split_search";a:1:{i:0;s:4:"post";}s:7:"sort_by";i:0;s:8:"sort_dir";s:4:"DESC";s:12:"show_results";s:6:"topics";s:12:"return_chars";i:200;}');

# --------------------------------------------------------

#
# Table structure for table `phpbbi_search_wordlist`
#

CREATE TABLE `phpbbi_search_wordlist` (
    `word_text`   VARCHAR(50) BINARY    NOT NULL DEFAULT '',
    `word_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `word_common` TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
    PRIMARY KEY (`word_text`),
    KEY `word_id` (`word_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_search_wordlist`
#

INSERT INTO `phpbbi_search_wordlist`
VALUES ('example', 1, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('post', 2, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('phpbb', 3, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('installation', 4, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('delete', 5, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('topic', 6, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('forum', 7, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('since', 8, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('everything', 9, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('seems', 10, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('working', 11, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('welcome', 12, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('test', 13, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('block', 14, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('bold', 15, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('code', 16, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('italic', 17, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('item1', 18, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('item2', 19, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('list', 20, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('tags', 21, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('testing', 22, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('underline', 23, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('url', 24, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('cry', 25, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('evil', 26, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('hammer', 27, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('idea', 28, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('lol', 29, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('pint', 30, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('roll', 31, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('smilies', 32, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('diff', 33, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('posting', 34, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('system', 35, 0);
INSERT INTO `phpbbi_search_wordlist`
VALUES ('user', 36, 0);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_search_wordmatch`
#

CREATE TABLE `phpbbi_search_wordmatch` (
    `post_id`     MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `word_id`     MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `title_match` TINYINT(1)            NOT NULL DEFAULT '0',
    KEY `post_id` (`post_id`),
    KEY `word_id` (`word_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_search_wordmatch`
#

INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 1, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 2, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 3, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 4, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 5, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 6, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 7, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 8, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 9, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 10, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 11, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 12, 1);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (1, 3, 1);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (2, 13, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (4, 13, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (4, 6, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (4, 13, 1);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (4, 6, 1);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 13, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 14, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 15, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 16, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 17, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 18, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 19, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 20, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 21, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 22, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 23, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (5, 24, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 13, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 25, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 26, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 27, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 28, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 29, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 30, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 31, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (6, 32, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (7, 33, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (7, 34, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (7, 35, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (7, 22, 0);
INSERT INTO `phpbbi_search_wordmatch`
VALUES (7, 36, 0);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_session_ext`
#

CREATE TABLE `phpbbi_session_ext` (
    `sess_id`           VARCHAR(32)  NOT NULL DEFAULT '',
    `sess_ip`           VARCHAR(15)  NOT NULL DEFAULT '',
    `session_user_id`   MEDIUMINT(8) NOT NULL DEFAULT '0',
    `session_start`     INT(11)      NOT NULL DEFAULT '0',
    `session_page`      INT(11)      NOT NULL DEFAULT '0',
    `session_logged_in` TINYINT(1)   NOT NULL DEFAULT '0',
    PRIMARY KEY (`sess_id`),
    KEY `session_user_id` (`session_user_id`),
    KEY `session_id_ip_user_id` (`sess_id`, `sess_ip`, `session_user_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_session_ext`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_themes`
#

CREATE TABLE `phpbbi_themes` (
    `themes_id`        MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_name`    VARCHAR(30)           NOT NULL DEFAULT '',
    `style_name`       VARCHAR(30)           NOT NULL DEFAULT '',
    `head_stylesheet`  VARCHAR(100)                   DEFAULT NULL,
    `body_background`  VARCHAR(100)                   DEFAULT NULL,
    `body_bgcolor`     VARCHAR(6)                     DEFAULT NULL,
    `body_text`        VARCHAR(6)                     DEFAULT NULL,
    `body_link`        VARCHAR(6)                     DEFAULT NULL,
    `body_vlink`       VARCHAR(6)                     DEFAULT NULL,
    `body_alink`       VARCHAR(6)                     DEFAULT NULL,
    `body_hlink`       VARCHAR(6)                     DEFAULT NULL,
    `tr_color1`        VARCHAR(6)                     DEFAULT NULL,
    `tr_color2`        VARCHAR(6)                     DEFAULT NULL,
    `tr_color3`        VARCHAR(6)                     DEFAULT NULL,
    `tr_class1`        VARCHAR(25)                    DEFAULT NULL,
    `tr_class2`        VARCHAR(25)                    DEFAULT NULL,
    `tr_class3`        VARCHAR(25)                    DEFAULT NULL,
    `th_color1`        VARCHAR(6)                     DEFAULT NULL,
    `th_color2`        VARCHAR(6)                     DEFAULT NULL,
    `th_color3`        VARCHAR(6)                     DEFAULT NULL,
    `th_class1`        VARCHAR(25)                    DEFAULT NULL,
    `th_class2`        VARCHAR(25)                    DEFAULT NULL,
    `th_class3`        VARCHAR(25)                    DEFAULT NULL,
    `td_color1`        VARCHAR(6)                     DEFAULT NULL,
    `td_color2`        VARCHAR(6)                     DEFAULT NULL,
    `td_color3`        VARCHAR(6)                     DEFAULT NULL,
    `td_class1`        VARCHAR(25)                    DEFAULT NULL,
    `td_class2`        VARCHAR(25)                    DEFAULT NULL,
    `td_class3`        VARCHAR(25)                    DEFAULT NULL,
    `fontface1`        VARCHAR(50)                    DEFAULT NULL,
    `fontface2`        VARCHAR(50)                    DEFAULT NULL,
    `fontface3`        VARCHAR(50)                    DEFAULT NULL,
    `fontsize1`        TINYINT(4)                     DEFAULT NULL,
    `fontsize2`        TINYINT(4)                     DEFAULT NULL,
    `fontsize3`        TINYINT(4)                     DEFAULT NULL,
    `fontcolor1`       VARCHAR(6)                     DEFAULT NULL,
    `fontcolor2`       VARCHAR(6)                     DEFAULT NULL,
    `fontcolor3`       VARCHAR(6)                     DEFAULT NULL,
    `span_class1`      VARCHAR(25)                    DEFAULT NULL,
    `span_class2`      VARCHAR(25)                    DEFAULT NULL,
    `span_class3`      VARCHAR(25)                    DEFAULT NULL,
    `img_size_poll`    SMALLINT(5) UNSIGNED           DEFAULT NULL,
    `img_size_privmsg` SMALLINT(5) UNSIGNED           DEFAULT NULL,
    PRIMARY KEY (`themes_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_themes`
#

INSERT INTO `phpbbi_themes`
VALUES (1, 'subSilver', 'subSilver', 'subSilver.css', '', 'E5E5E5', '000000', '006699', '5493B4', '', 'DD6900', 'EFEFEF', 'DEE3E7', 'D1D7DC', '', '', '', '98AAB1', '006699', 'FFFFFF', 'cellpic1.gif', 'cellpic3.gif', 'cellpic2.jpg', 'FAFAFA', 'FFFFFF', '', 'row1', 'row2', '',
        'Verdana, Arial, Helvetica, sans-serif', 'Trebuchet MS', 'Courier, \'Courier New\', sans-serif', 10, 11, 12, '444444', '006600', 'FFA34F', '', '', '', NULL, NULL);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_themes_name`
#

CREATE TABLE `phpbbi_themes_name` (
    `themes_id`        SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    `tr_color1_name`   CHAR(50)                      DEFAULT NULL,
    `tr_color2_name`   CHAR(50)                      DEFAULT NULL,
    `tr_color3_name`   CHAR(50)                      DEFAULT NULL,
    `tr_class1_name`   CHAR(50)                      DEFAULT NULL,
    `tr_class2_name`   CHAR(50)                      DEFAULT NULL,
    `tr_class3_name`   CHAR(50)                      DEFAULT NULL,
    `th_color1_name`   CHAR(50)                      DEFAULT NULL,
    `th_color2_name`   CHAR(50)                      DEFAULT NULL,
    `th_color3_name`   CHAR(50)                      DEFAULT NULL,
    `th_class1_name`   CHAR(50)                      DEFAULT NULL,
    `th_class2_name`   CHAR(50)                      DEFAULT NULL,
    `th_class3_name`   CHAR(50)                      DEFAULT NULL,
    `td_color1_name`   CHAR(50)                      DEFAULT NULL,
    `td_color2_name`   CHAR(50)                      DEFAULT NULL,
    `td_color3_name`   CHAR(50)                      DEFAULT NULL,
    `td_class1_name`   CHAR(50)                      DEFAULT NULL,
    `td_class2_name`   CHAR(50)                      DEFAULT NULL,
    `td_class3_name`   CHAR(50)                      DEFAULT NULL,
    `fontface1_name`   CHAR(50)                      DEFAULT NULL,
    `fontface2_name`   CHAR(50)                      DEFAULT NULL,
    `fontface3_name`   CHAR(50)                      DEFAULT NULL,
    `fontsize1_name`   CHAR(50)                      DEFAULT NULL,
    `fontsize2_name`   CHAR(50)                      DEFAULT NULL,
    `fontsize3_name`   CHAR(50)                      DEFAULT NULL,
    `fontcolor1_name`  CHAR(50)                      DEFAULT NULL,
    `fontcolor2_name`  CHAR(50)                      DEFAULT NULL,
    `fontcolor3_name`  CHAR(50)                      DEFAULT NULL,
    `span_class1_name` CHAR(50)                      DEFAULT NULL,
    `span_class2_name` CHAR(50)                      DEFAULT NULL,
    `span_class3_name` CHAR(50)                      DEFAULT NULL,
    PRIMARY KEY (`themes_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_themes_name`
#

INSERT INTO `phpbbi_themes_name`
VALUES (1, 'The lightest row colour', 'The medium row color', 'The darkest row colour', '', '', '', 'Border round the whole page', 'Outer table border', 'Inner table border', 'Silver gradient picture', 'Blue gradient picture', 'Fade-out gradient on index', 'Background for quote boxes',
        'All white areas', '', 'Background for topic posts', '2nd background for topic posts', '', 'Main fonts', 'Additional topic title font', 'Form fonts', 'Smallest font size', 'Medium font size', 'Normal font size (post body etc)', 'Quote & copyright text', 'Code text colour',
        'Main table header text colour', '', '', '');

# --------------------------------------------------------

#
# Table structure for table `phpbbi_topics`
#

CREATE TABLE `phpbbi_topics` (
    `topic_id`            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `forum_id`            SMALLINT(8) UNSIGNED  NOT NULL DEFAULT '0',
    `topic_title`         CHAR(60)              NOT NULL DEFAULT '',
    `topic_poster`        MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `topic_time`          INT(11)               NOT NULL DEFAULT '0',
    `topic_views`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `topic_replies`       MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `topic_status`        TINYINT(3)            NOT NULL DEFAULT '0',
    `topic_vote`          TINYINT(1)            NOT NULL DEFAULT '0',
    `topic_type`          TINYINT(3)            NOT NULL DEFAULT '0',
    `topic_first_post_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `topic_last_post_id`  MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `topic_moved_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`topic_id`),
    KEY `forum_id` (`forum_id`),
    KEY `topic_moved_id` (`topic_moved_id`),
    KEY `topic_status` (`topic_status`),
    KEY `topic_type` (`topic_type`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_topics`
#

INSERT INTO `phpbbi_topics` (topic_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, forum_id, topic_status, topic_type, topic_vote, topic_first_post_id, topic_last_post_id)
VALUES (1, 'Welcome to phpBB 2', 1, '972086460', 0, 0, 1, 0, 0, 0, 1, 1);

# --------------------------------------------------------

#
# Table structure for table `phpbbi_topics_watch`
#

CREATE TABLE `phpbbi_topics_watch` (
    `topic_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `user_id`       MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `notify_status` TINYINT(1)            NOT NULL DEFAULT '0',
    KEY `topic_id` (`topic_id`),
    KEY `user_id` (`user_id`),
    KEY `notify_status` (`notify_status`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_topics_watch`
#

# --------------------------------------------------------

#
# Table structure for table `phpbbi_user_ext`
#

CREATE TABLE `phpbbi_user_ext` (
    `uid`                   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `user_lastvisit`        INT(11)               NOT NULL DEFAULT '0',
    `user_session_time`     INT(11)               NOT NULL DEFAULT '0',
    `user_session_page`     SMALLINT(5)           NOT NULL DEFAULT '0',
    `user_level`            TINYINT(4)                     DEFAULT '0',
    `user_style`            TINYINT(4)                     DEFAULT NULL,
    `user_lang`             VARCHAR(255)                   DEFAULT NULL,
    `user_dateformat`       VARCHAR(14)           NOT NULL DEFAULT 'd M Y H:i',
    `user_new_privmsg`      SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `user_unread_privmsg`   SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    `user_last_privmsg`     INT(11)               NOT NULL DEFAULT '0',
    `user_emailtime`        INT(11)                        DEFAULT NULL,
    `user_allowhtml`        TINYINT(1)                     DEFAULT '1',
    `user_allowbbcode`      TINYINT(1)                     DEFAULT '1',
    `user_allowsmile`       TINYINT(1)                     DEFAULT '1',
    `user_allowavatar`      TINYINT(1)            NOT NULL DEFAULT '1',
    `user_allow_pm`         TINYINT(1)            NOT NULL DEFAULT '1',
    `user_allow_viewonline` TINYINT(1)            NOT NULL DEFAULT '1',
    `user_notify`           TINYINT(1)            NOT NULL DEFAULT '1',
    `user_notify_pm`        TINYINT(1)            NOT NULL DEFAULT '0',
    `user_popup_pm`         TINYINT(1)            NOT NULL DEFAULT '1',
    `user_avatar_type`      TINYINT(4)            NOT NULL DEFAULT '3',
    `user_sig_bbcode_uid`   VARCHAR(10)                    DEFAULT NULL,
    `user_newpasswd`        VARCHAR(32)                    DEFAULT NULL,
    PRIMARY KEY (`uid`),
    KEY `user_session_time` (`user_session_time`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_user_ext`
#

# --------------------------------------------------------

#
# Table structure for table `phpbbi_vote_desc`
#

CREATE TABLE `phpbbi_vote_desc` (
    `vote_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `topic_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `vote_text`   TEXT                  NOT NULL,
    `vote_start`  INT(11)               NOT NULL DEFAULT '0',
    `vote_length` INT(11)               NOT NULL DEFAULT '0',
    PRIMARY KEY (`vote_id`),
    KEY `topic_id` (`topic_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_vote_desc`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_vote_results`
#

CREATE TABLE `phpbbi_vote_results` (
    `vote_id`          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `vote_option_id`   TINYINT(4) UNSIGNED   NOT NULL DEFAULT '0',
    `vote_option_text` VARCHAR(255)          NOT NULL DEFAULT '',
    `vote_result`      INT(11)               NOT NULL DEFAULT '0',
    KEY `vote_option_id` (`vote_option_id`),
    KEY `vote_id` (`vote_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_vote_results`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_vote_voters`
#

CREATE TABLE `phpbbi_vote_voters` (
    `vote_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `vote_user_id` MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `vote_user_ip` CHAR(15)              NOT NULL DEFAULT '',
    KEY `vote_id` (`vote_id`),
    KEY `vote_user_id` (`vote_user_id`),
    KEY `vote_user_ip` (`vote_user_ip`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_vote_voters`
#


# --------------------------------------------------------

#
# Table structure for table `phpbbi_words`
#

CREATE TABLE `phpbbi_words` (
    `word_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `word`        CHAR(100)             NOT NULL DEFAULT '',
    `replacement` CHAR(100)             NOT NULL DEFAULT '',
    PRIMARY KEY (`word_id`)
)
    ENGINE = ISAM;

#
# Dumping data for table `phpbbi_words`
#


#####  INSERT ANONYMOUS USER  #####
INSERT INTO `phpbbi_user_ext`
VALUES (0, 0, 0, 0, 0, NULL, NULL, 'd M Y H:i', 0, 0, 0, NULL, 1, 1, 1, 0, 0, 1, 1, 0, 1, 3, NULL, NULL);
