<?
// ---------------------------------
// Administration Support Functions
// by Aaron Bishop
// ---------------------------------

// include our forum settings
if (!file_exists("../settings.inc"))
{
	include("../settings-initial.inc");
}
else
{
	include("../settings.inc");
}

// get forums support functions
include("../skins/$forum_skin/table_headers.php");
include("../support.inc");

if (!VerifyAdmin())
{
	AdminMessage("<b>You are not authorized to view this page.</b><p>If you feel you should have access to post in this forum, check to make sure you have logged in properly");

	exit();
}

function AdminMessage($message)
{
	?>
	<table border=0 cellspacing=0 cellpadding=1 width=100%>
	<td class=tableborder>

	<table border=0 cellspacing=0 cellpadding=6 width=100%>
	<tr><td width=100% class=backtable><b>Forum Message</b></td></tr>
	<tr><td width=100% class=backtable colspan=2>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>

		<table cellspacing=1 cellpadding=8 border=0 width=100%>
		<tr><td class=tablecell1 width=100% valign=top align=center>
		
		<? echo $message ?>
		
		</td></tr></table>
	</td></table>
	</td></tr></table>
	</td></table>
	<?
}

function VerifyAdmin()
{
	global $administrators;

	global $cookiemembername;
	global $cookiepassencrypted;

	if (file_exists("../profiles/$cookiemembername/profile.dat"))
	{
		$profile = file("../profiles/$cookiemembername/profile.dat", "r");
		$stored_pass = rtrim($profile[1]);

		for ($i=0; $i< count($administrators); $i++)
		{
			if ($administrators[$i] == $cookiemembername && $cookiepassencrypted == $stored_pass)
			{
				return true;
			}
		}
	}
	
	return false;
}


// This function writes all settings 
// variables to settings.inc
function WriteForumSettings()
{
	global $mainforumtitle;
	global $forumpath;
	global $forumdomain;
	global $forum_skin;
	global $servertimezone;
	global $forumindex;
	global $forumdescription;
	global $id_to_forum;
	global $forum_flags;
	global $ranks;
	global $ranklevels;
	global $specialranks;
	global $administrators;
	global $moderators;

	$fp = fopen("../settings.inc", 'w');
	fwrite($fp, "<?\n\n// this file contains all settings for the board\n\n");

	// do forum title
	fwrite($fp, "\$mainforumtitle = \"$mainforumtitle\";\n\n");

	// do forum path
	fwrite($fp, "\$forumpath = \"$forumpath\";\n\n");

	// do domain
	fwrite($fp, "\$forumdomain = \"$forumdomain\";\n\n");

	// do skins
	fwrite($fp, "\$forum_skin = \"$forum_skin\";\n\n");
	
	// do timezone
	fwrite($fp, "\$servertimezone = \"$servertimezone\";\n\n");

	// do forum index
	fwrite($fp, "\$forumindex = array(");
	foreach ($forumindex as $categorytitle => $categoryforums)
	{
		fwrite($fp, "\"$categorytitle\" => array(");
		foreach ($categoryforums as $forumid) { fwrite($fp, "\"$forumid\",\n"); }
		fwrite($fp, "),\n");
	}
	fwrite($fp, ");\n\n");

	// do forum descriptions
	fwrite($fp, "\$forumdescription = array(");
	foreach ($forumdescription as $forumid => $description) { fwrite($fp, "\"$forumid\" => \"$description\",\n"); }
	fwrite($fp, ");\n\n");

	// do id_to_forum
	fwrite($fp, "\$id_to_forum = array(");
	foreach ($id_to_forum as $forumid => $forum) { fwrite($fp, "\"$forumid\" => \"$forum\",\n"); }
	fwrite($fp, ");\n\n");

	// do forum_flags
	fwrite($fp, "\$forum_flags = array(");
	foreach ($forum_flags as $forum => $flags)
	{
		fwrite($fp, "\"$forum\" => array(");
		foreach ($flags as $flag_name => $flag_value) { fwrite($fp, "\"$flag_name\" => \"$flag_value\",\n"); }
		fwrite($fp, "),\n");
	}
	fwrite($fp, ");\n\n");

	// do ranks
	fwrite($fp, "\$ranks = array(");
	for ($i=0; $i<sizeof($ranks); $i++) { fwrite($fp, "\"$ranks[$i]\",\n"); }
	fwrite($fp, ");\n\n");

	// do ranklevels
	fwrite($fp, "\$ranklevels = array(");
	for ($i=0; $i<sizeof($ranklevels); $i++) { fwrite($fp, "\"$ranklevels[$i]\","); }
	fwrite($fp, ");\n\n");

	// do special ranks
	fwrite($fp, "\$specialranks = array(");
	foreach ($specialranks as $tag => $rank) { fwrite($fp, "\"$tag\" => \"$rank\",\n"); }
	fwrite($fp, ");\n\n"); ?><?

	// do administrators
	fwrite($fp, "\$administrators = array(");
	for ($i=0; $i<sizeof($administrators); $i++) { fwrite($fp, "\"$administrators[$i]\","); }
	fwrite($fp, ");\n\n");

	// do moderators
	fwrite($fp, "\$moderators = array(");
	for ($i=0; $i<sizeof($moderators); $i++) { fwrite($fp, "\"$moderators[$i]\","); }
	fwrite($fp, ");\n\n?>"); ?><?

	fclose($fp);

}

// This function creates a new index for a forum from
// the threadfiles in the forum store directory
function ReindexForum($forum)
{
	$handle = opendir("../messages/$forum/");

	$sticky_threads = array();

	while (false !== ($file = readdir($handle)))
	{
		if ($file != "." && $file != "..")
		{
			$threadfile = file("../messages/$forum/$file", "r");

			$thread = array_pop($threadfile); // get last post
			array_push($threadfile, $thread);
			list($membername, $to, $timeposted, $messagetext, $postid, $replytonum, $ip, $smilies, $signature) = split('[|]', $thread);
			/*if (sizeof($thread) == 1)
			{
				$originalpost[0] = $membername;
			}
			else
			{*/
				$originalpost = split('[|]', $threadfile[1]);
			//}

			$forumposts += 1;

			$numofposts = sizeof($threadfile)-1;
			$threadfile[0] = rtrim($threadfile[0]);
			$threadinfo = "$threadfile[0]|$originalpost[0]|$membername|$timeposted|$numofposts|$file";

			if (preg_match("/persistent/", $file)) { $sticky_threads["$postid"] = $threadinfo."|sticky"; }
			else { $threads["$postid"] = $threadinfo; }
		}
	}

	if ($forumposts >=1 )
	{
		// sort the threads
		krsort($sticky_threads);
		krsort($threads);
	}

	$threads = array_merge($sticky_threads, $threads);

	// write index to file
	$fp = fopen("../messages/index_".$forum, "w");
	flock($fp, 2);
	foreach ($threads as $postid => $threadinfo)
	{
		if ($postid != 0 )
		{
			fwrite($fp, "\n");
		}

		fwrite($fp, $threadinfo);
	}
	flock($fp, 3);
	fclose($fp);

	unset($threads);
}

?>