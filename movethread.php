<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$from_forum  = "";
$to_forum  = "";
$thread = "";
if (isset($_GET["from_forum"])) $from_forum = $_GET["from_forum"];
if (isset($_GET["to_forum"]))   $to_forum   = $_GET["to_forum"];
if (isset($_GET["thread"])) $thread = $_GET["thread"];

if (!(AuthAdminCookie() || AuthModeratorCookie()))
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to move this thread.</b><p>If you feel you should have access to move this thread, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
} 

if (!file_exists("messages/index_$from_forum"))
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
else if (!file_exists("messages/index_$to_forum"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified forum does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

if ($from_forum == $to_forum)
{
	header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$thread");
	exit();
}

// if we got here, no errors

$threadfile = file("messages/threadfiles/$thread", "r");

list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);
$closed = rtrim($closed);

$lastpost = array_pop($threadfile); // get last post
array_push($threadfile, $lastpost);

list($original_membername, $original_to, $original_timeposted, $original_messagetext, $original_postid, $original_replytonum, $original_ip, $original_smilies, $original_signature, $original_deleted) = split('[|]', $threadfile[1]);

list($info_membername, $info_to, $info_timeposted, $info_messagetext, $info_postid, $info_replytonum, $info_ip, $info_smilies, $info_signature, $info_deleted) = split('[|]', $lastpost);

$numofposts = sizeof($threadfile)-1;
$threadinfo = "$thread_title|$original_membername|$info_membername|$info_timeposted|$numofposts|$thread|$sticky|$closed|\n";


// prevent accessing files at same time
WaitForWriteToFinish();

// open old forum index
$threadindex = file("messages/index_".$from_forum, "r");

// create tempfile
$tempname = tempnam("./forum_temp", "temp_index_");
$fpTemp = fopen($tempname, "w");

foreach ($threadindex as $i => $line)
{
	if (rtrim($line) == rtrim($threadinfo))
	{
		if ($to_forum != "TRASH")
		{
			fwrite($fpTemp, "$thread_title|$original_membername|$info_membername|$info_timeposted|-|$thread|$sticky|$closed|$to_forum\n");
		}
	}
	else
	{
		fwrite($fpTemp, rtrim($line)."\n");
	}
}
fclose($fpTemp);

copy($tempname, "messages/index_".$from_forum);
unlink($tempname);

// decrement old counters
DecrementCounter("messages/counters/counter_".$from_forum."_totalthreads");
for ($i=0; $i<sizeof($threadfile)-1; $i++)
{
	DecrementCounter("messages/counters/counter_".$from_forum."_totalposts");
}

// open new forum index
$threadindex = file("messages/index_".$to_forum, "r");

// create tempfile
$tempname = tempnam("./forum_temp", "temp_index_");
$fpTemp = fopen($tempname, "w");

// separate stickies
$sticky_threads = array();
$count_stickies = 0;
for ($i=0; $i<sizeof($threadindex); $i++)
{
	$cur_thread = split('[|]', $threadindex[$i]);
	if (rtrim($cur_thread[6]) == "sticky")
	{
		array_push($sticky_threads, $threadindex[$i]);
		unset($threadindex[$i]);
		$count_stickies += 1;
	}
	else
	{
		break;
	}
}

$thread_moved = 0;

// write sticky indexes
for ($i=0; $i<sizeof($sticky_threads); $i++)
{
	list ($cur_threadtitle, $cur_starter, $cur_lastposter, $cur_timeposted, $cur_numofposts, $cur_threadid, $cur_sticky, $cur_closed, $cur_moved_to) = split('[|]', $sticky_threads[$i]);

	if ($i == 0 && $info_timeposted > $cur_timeposted && $sticky == "sticky")
	{
		fwrite($fpTemp, $threadinfo);

		$thread_moved = 1;
	}

	if ($cur_timeposted != $info_timeposted)
	{
		fwrite($fpTemp, rtrim($sticky_threads[$i])."\n");
	}

	if (isset($sticky_threads[$i+1]))
	{
		list ($next_threadtitle, $next_starter, $next_lastposter, $next_timeposted, $next_numofposts, $next_threadid, $next_sticky, $next_closed, $next_moved_to) = split('[|]', $sticky_threads[$i+1]);

		if ($sticky == "sticky" && $cur_timeposted == $info_timeposted)
		{
			fwrite($fpTemp, $threadinfo);

			$thread_moved = 1;
		}
		else if ($sticky == "sticky" && $cur_timeposted > $info_timeposted && $next_timeposted < $info_timeposted)
		{
			fwrite($fpTemp, $threadinfo);

			$thread_moved = 1;
		}
	}
}

$newest_nonsticky = 1;
// write new post index
foreach ($threadindex as $i => $line)
{
	list ($cur_threadtitle, $cur_starter, $cur_lastposter, $cur_timeposted, $cur_numofposts, $cur_threadid, $cur_sticky, $cur_closed, $cur_moved_to) = split('[|]', $threadindex[$i]);
	
	if ($newest_nonsticky && $info_timeposted > $cur_timeposted)
	{
		fwrite($fpTemp, $threadinfo);

		$thread_moved = 1;
	}
	
	$newest_nonsticky = 0;

	if ($cur_timeposted != $info_timeposted)
	{
		fwrite($fpTemp, rtrim($threadindex[$i])."\n");
	}

	if (isset($threadindex[$i+1]))
	{
		list ($next_threadtitle, $next_starter, $next_lastposter, $next_timeposted, $next_numofposts, $next_threadid, $next_sticky, $next_closed, $next_moved_to) = split('[|]', $threadindex[$i+1]);

		/*echo "<br>".$i."<br>";
		echo "cur_timeposted: ".$cur_timeposted."<br>";
		echo "next_timeposted: ".$next_timeposted."<br>";
		echo "info_timeposted: ".$info_timeposted."<br>";
		echo ($cur_timeposted > $info_timeposted && $next_timeposted < $info_timeposted)."<br><br>";*/

		if ($cur_timeposted == $info_timeposted && !$thread_moved)
		{
			fwrite($fpTemp, $threadinfo);

			$thread_moved = 1;
		}
		else if ($cur_timeposted > $info_timeposted && $next_timeposted < $info_timeposted && !$thread_moved)
		{
			fwrite($fpTemp, $threadinfo);

			$thread_moved = 1;
		}
	}
}

if (!$thread_moved)
{
	fwrite($fpTemp, $threadinfo);
}

fclose($fpTemp);

copy($tempname, "messages/index_".$to_forum);
unlink($tempname);

// increment new counters
IncrementCounter("messages/counters/counter_".$to_forum."_totalthreads");
for ($i=0; $i<sizeof($threadfile)-1; $i++)
{
	IncrementCounter("messages/counters/counter_".$to_forum."_totalposts");
}

// update lastpost if this is newest in target forum
$lastpost = file("messages/info_".$to_forum."_lastpost"," r");
list($newestposttime, $newestposter) = split('[|]', $lastpost[0]);

if ($info_timeposted > $newestposttime || $newestposttime == "never")
{
	// save last post info
	$fp = fopen("messages/info_".$to_forum."_lastpost", "w");
	fwrite($fp, "$info_timeposted|$info_membername");
	fclose($fp);
}

// update threadfile
$tempname = tempnam("./forum_temp", "temp_thread_");
$fpTemp = fopen($tempname, "w");

fwrite($fpTemp, "$thread_title|$to_forum|$sticky|$closed\n");
for ($i=1; $i<sizeof($threadfile); $i++)
{
	fwrite($fpTemp, rtrim($threadfile[$i])."\n");
}
fclose($fpTemp);

copy($tempname, "messages/threadfiles/$thread");
unlink($tempname);


$new = sizeof($threadfile);
if ($new > $postsperpage) $new = $postsperpage;

$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$thread&new=$new\">";
include("./headerinternal.php");
ForumMessage("<b>Thread Moved</b><p>If your browser does not automatically return to the thread, <a href=viewthread.php?forum=$to_forum&thread=$thread>click here</a>.");

exit();

include("./skins/$forum_skin/forumfooter.php"); ?>