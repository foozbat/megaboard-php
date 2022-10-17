<?php
// this file contains all settings for the board

$mainforumtitle = "Megaboard PHP v4.0";
$forumpath = "/mypath/";
$forumdomain = "mydomain.com";
$forum_skin = "default";

$servertimezone = "-6";

$forumindex = array("General Forums" => array("TRASH",
),
);

$forumdescription = array("TRASH" => "All deleted threads are placed here.");

$id_to_forum = array("TRASH" => "Trash Can");

$forum_flags = array("TRASH" => array("visible" => "admin_only",
"closed" => "yes",
"posting" => "admin_only",
),
);

$ranks = array(	"New Member",
				"Regular Poster",
				"Frequent Poster",
				"Heavy Poster",
				"INSANE Poster!");

$ranklevels = array( 0, 50, 250, 500, 1000 );

$specialranks = array(	"moderator" => "Moderator",
						"administrator" => "Board Administrator");

$administrators = array();

$moderators = array();
?>