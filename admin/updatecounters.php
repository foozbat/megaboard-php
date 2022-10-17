<?php

// Megaboard 3.0
// - updatecounters.php
//
// this script loops through all messages and updates counter files

include("../settings.inc"); 
//include("../support.inc");

$totalmessages = 0;				// total posts in entire forum
$totalthreads = 0;
$totalforumthreads = array();	// total threads in subforum
$totalforumposts = array();		// total posts in subforum

?>
<html>
<head><title>Update Counters</title>
<style>
	body { font-family: arial; font-size: 13; }
	td { font-family: arial; font-size: 13; }
</style>

<table width=75% cellspacing=1 cellpadding=4>
<tr><td width=100%><b>Forum</b></td><td><b>Threads</b></td><td><b>Posts</b></td><td nowrap align=center><b>Last Post</b></td></tr>
<?

foreach ($id_to_forum as $forum_id => $forumtitle)
{
	$handle = opendir("../messages/threadfiles");
	
	$totalforumthreads[$forum_id] = 0;
	$totalforumposts[$forum_id] = 0;

	$newestposttime = "never";
	$newestposter = "posted";

	// loop through each thread and count threads / posts
	while (false !== ($file = readdir($handle)))
	{
		if ($file != "." && $file != "..")
		{
			$threadfile = file("../messages/threadfiles/$file", "r");

			list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);

			if ($forum == $forum_id)
			{
				$lastpost = array_pop($threadfile); // get last post
				array_push($threadfile, $lastpost);
				$membername=$to=$timeposted=$messagetext=$postid=$replytonum=$ip=0;  //clear values
				list($membername, $to, $timeposted, $messagetext, $postid, $replytonum, $ip, $smilies, $signature, $deleted) = split('[|]', $lastpost);

				if ($postid > $newestpostid)
				{
					$newestpostid = $postid;
					$newestposttime = $timeposted;
					$newestposter = $membername;
				}
				
				$totalforumthreads[$forum_id]	+= 1;
				$totalforumposts[$forum_id]	+= sizeof($threadfile)-1;
			}
		}
	}

	// display info
	?><tr><td>» <? echo $forumtitle ?></td><td align=center><? echo $totalforumthreads[$forum_id] ?></td><td align=center><? echo $totalforumposts[$forum_id] ?></td><td nowrap align=right><? echo $newestposttime ?><br>by <? echo $newestposter ?></td></tr><?

	// save subforum counters to file
	$fp = fopen("../messages/counters/counter_".$forum_id."_totalthreads", "w");
	fwrite($fp, $totalforumthreads[$forum_id]);
	fclose($fp);

	$fp = fopen("../messages/counters/counter_".$forum_id."_totalposts", "w");
	fwrite($fp, $totalforumposts[$forum_id]);
	fclose($fp);

	$fp = fopen("../messages/info_".$forum_id."_lastpost", "w");
	fwrite($fp, "$newestposttime|$newestposter");
	fclose($fp);

	// increment total forum posts counter
	$totalmessages += $totalforumposts[$forum_id];
	$totalthreads  += $totalforumthreads[$forum_id];

	$newestpostid = $newestposttime = $newestposter = 0; // clear newest post info
}

// update total forum posts counter
$fp = fopen("../messages/counters/counter_totalmessages", "w");
fwrite($fp, $totalmessages);
fclose($fp);
$fp = fopen("../messages/counters/counter_totalthreads", "w");
fwrite($fp, $totalthreads);
fclose($fp);


?></table>
<br><br>

Total Posts: <? echo $totalmessages; ?><br>
Total Threads: <? echo $totalmessages;

//ShowProcessingTime();