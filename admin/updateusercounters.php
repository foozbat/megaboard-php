<?php

// Megaboard 3.0
// - updateusercounters.php
//
// this script loops through all messages and updates counters for each user
// very server intensive (i think)

include("./settings.inc"); 
include("./support.inc");

$totalmessages = 0;				// total posts in entire forum
$totalforumthreads = array();	// total threads in subforum
$totalforumposts = array();		// total posts in subforum

?>
<html>
<head><title>Update User Counters</title>
<style>
	body { font-family: arial; font-size: 13; }
	td { font-family: arial; font-size: 13; }
</style>

<table width=75% cellspacing=1 cellpadding=4>
<tr><td width=100%><b>Membername</b></td><td><b>Posts</b></td><td nowrap><b>X Replied To</b></td></tr>
<?

foreach ($forumindex as $forum => $forumtitle)
{
	if ( !preg_match('/separator/',$forum) )
	{
		$handle = opendir("$DOCUMENT_ROOT/megaboard/hitsquad/messages/$forum/");

		// loop through each thread
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != "..")
			{
				$threadfile = file("$DOCUMENT_ROOT/megaboard/hitsquad/messages/$forum/$file", "r");

				for ($i = 1; $i < sizeof($threadfile); $i++)
				{
					list($membername, $to, $timeposted, $messagetext, $postid, $replytonum, $ip, $smilies, $signature) = split('[|]', $threadfile[$i]);

					$userpostcounter[$membername] += 1;
					$userreplycounter[$to] += 1;
				}				
			}
		}

		// save subforum counters to file
		/*$fp = fopen("$DOCUMENT_ROOT/megaboard/hitsquad/messages/counter_".$forum."_totalthreads", "w");
		fwrite($fp, $totalforumthreads[$forum]);
		fclose($fp);

		$fp = fopen("$DOCUMENT_ROOT/megaboard/hitsquad/messages/counter_".$forum."_totalposts", "w");
		fwrite($fp, $totalforumposts[$forum]);
		fclose($fp);

		$fp = fopen("$DOCUMENT_ROOT/megaboard/hitsquad/messages/info_".$forum."_lastpost", "w");
		fwrite($fp, "$newestposttime|$newestposter");
		fclose($fp);*/
	}
}

// display info
foreach ($userpostcounter as $membername => $posts)
{
	?><tr><td><? echo $membername ?></td><td align=center><? echo $posts ?></td><td align=center><? if ($userreplycounter[$membername] == "") { echo 0; } else { echo $userreplycounter[$membername]; } ?></td></tr><?
}

// update total forum posts counter
/*$fp = fopen("$DOCUMENT_ROOT/megaboard/hitsquad/messages/counter_totalmessages", "w");
fwrite($fp, $totalmessages);
fclose($fp);*/


?></table>
<?

ShowProcessingTime();