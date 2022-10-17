<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$forum  = "";
$thread = "";
if (isset($_GET["forum"]))  $forum  = $_GET["forum"];
if (isset($_GET["thread"])) $thread = $_GET["thread"];

if (!(AuthAdminCookie() || AuthModeratorCookie()))
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to close this thread.</b><p>If you feel you should have access to close this thread, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
} 

if (!file_exists("messages/index_$forum"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified forum does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}
else if (!file_exists("messages/threadfiles/$thread"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified thread does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

// if we got here, no errors

// prevent posting at same time
WaitForWriteToFinish();

$threadfile = file("messages/threadfiles/$thread", "r");

list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);

$tempname = tempnam("./forum_temp", "temp_thread_");
$fpTemp = fopen($tempname, "w");

// overwrite old thread file
fwrite($fpTemp, "$thread_title|$forum|$sticky|closed\n");
for ($i=1; $i<sizeof($threadfile); $i++)
{
	fwrite($fpTemp, rtrim($threadfile[$i])."\n");
}

fclose($fpTemp);

copy($tempname, "messages/threadfiles/$thread");
unlink($tempname);


// update forum index

$lastpost = array_pop($threadfile); // get last post
array_push($threadfile, $lastpost);

list($original_membername, $original_to, $original_timeposted, $original_messagetext, $original_postid, $original_replytonum, $original_ip, $original_smilies, $original_signature, $original_deleted) = split('[|]', $threadfile[1]);

list($info_membername, $info_to, $info_timeposted, $info_messagetext, $info_postid, $info_replytonum, $info_ip, $info_smilies, $info_signature, $info_deleted) = split('[|]', $lastpost);

$numofposts = sizeof($threadfile)-1;
$threadinfo = "$thread_title|$original_membername|$info_membername|$info_timeposted|$numofposts|$thread|$sticky||\n";


// open forum index
$threadindex = file("messages/index_".$forum, "r");

// create tempfile
$tempname = tempnam("./forum_temp", "temp_index_");
$fpTemp = fopen($tempname, "w");

foreach ($threadindex as $i => $line)
{
	if (rtrim($line) != rtrim($threadinfo))
	{
		fwrite($fpTemp, rtrim($line)."\n");
	}
	else
	{
		fwrite($fpTemp, "$thread_title|$original_membername|$info_membername|$info_timeposted|$numofposts|$thread|$sticky|closed|\n");
	}
}
fclose($fpTemp);

copy($tempname, "messages/index_".$forum);
unlink($tempname);


header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$thread");
exit();

include("./skins/$forum_skin/forumfooter.php"); ?>