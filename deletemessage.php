<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$forum  = "";
$messno = "";
$thread = "";

if (isset($_GET["forum"]))  $forum  = $_GET["forum"];
if (isset($_GET["messno"])) $messno = $_GET["messno"];
if (isset($_GET["thread"])) $thread = $_GET["thread"];

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
else if ($forum_flags[$forum]["closed"] == "yes")
{
	include("./headerinternal.php");
	
	ForumMessage("<b>This forum is closed.</b><p>No new posts can be edited in this forum.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

if (!AuthCookie())
{
	// login if we can
	header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."login.php");
	exit();
}

// OPEN OLD THREAD
$threadfile = file("messages/threadfiles/$thread", "r");

list($old_membername, $old_to, $old_timeposted, $old_messagetext, $old_postid, $old_replytonum, $old_ip, $old_smilies, $old_signature, $old_deleted) = split('[|]', $threadfile[$messno]);
$old_deleted = rtrim($old_deleted);

if ($old_membername == $cookiemembername || AuthAdminCookie() || AuthModeratorCookie() || (AuthModeratorCookie() && AccessLevel($membername_display) != "administrator") )
{
	//echo "delete ok";
}
else if ($old_deleted =="deleted")
{
	include("./headerinternal.php");
	
	ForumMessage("<b>This post has already beed deleted.</b><p>You can't delete it again silly =P");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}
else
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to delete this post.</b><p>If you feel you should have access to delete this post, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
} 


// if we got here, no errors

// prevent accessing files at same time as other users
WaitForWriteToFinish();

$tempname = tempnam("./forum_temp", "temp_thread_");
$fpTemp = fopen($tempname, "w");

// overwrite old thread file
for ($i=0; $i<sizeof($threadfile); $i++)
{
	if ($i == $messno)
	{
		fwrite($fpTemp, "$old_membername|");
		fwrite($fpTemp, "$old_to|");
		fwrite($fpTemp, "$old_timeposted|");
		fwrite($fpTemp, "$old_messagetext|");
		fwrite($fpTemp, "$old_postid|");
		fwrite($fpTemp, "$old_replytonum|"); // replytonum
		fwrite($fpTemp, "$old_ip|");
		fwrite($fpTemp, "$old_smilies|");
		fwrite($fpTemp, "$old_signature|");
		fwrite($fpTemp, "deleted\n");
	}
	else
	{
		fwrite($fpTemp, rtrim($threadfile[$i])."\n");
	}
}

fclose($fpTemp);

copy($tempname, "messages/threadfiles/$thread");
unlink($tempname);


// GOTO THREAD
$threadsize = sizeof($threadfile)-1;

$threadpages = (int)($threadsize / $postsperpage);
$threadpages += ( ($threadsize % $postsperpage) != 0 ? 1 : 0);

if ($threadpages > 1)
{
	$page = 1;
	for ($i=1; $i<=$threadpages; $i++)
	{
		if ($messno < $i*$postsperpage)
		{
			$page = $i;
			break;
		}
	}
}

$new = $threadsize;
if ($page < $threadpages) $new = $page*$postsperpage;

if ($page > 1) $pagelinktext = "&page=$page";
else $pagelinktext = "";

header("Cache-Control: must-revalidate");  
header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$thread".$pagelinktext."&new=$new#$old_postid");
exit();

include("./skins/$forum_skin/forumfooter.php"); ?>