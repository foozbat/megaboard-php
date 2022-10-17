<?
include("../settings.inc"); 
//include("../support.inc");

?>
<html>
<head><title>Create Sorted Indexes</title>
<style>
	body { font-family: arial; font-size: 13; }
	td { font-family: arial; font-size: 13; }
</style>

<table>

<?

foreach ($id_to_forum as $forum_id => $forumtitle)
{
	$handle = opendir("../messages/threadfiles/");

	$sticky = "";
	$closed = "";
	$forumposts = 0;
	$sticky_threads = array();
	$threads = array();

	?><tr><td colspan=3><b><? echo $forumtitle ?></b></td></tr><? echo "\n";

	while (false !== ($file = readdir($handle)))
	{
		if ($file != "." && $file != "..")
		{
			$threadfile = file("../messages/threadfiles/$file", "r");

			$thread = array_pop($threadfile); // get last post
			array_push($threadfile, $thread);
			list($membername, $to, $timeposted, $messagetext, $postid, $replytonum, $ip, $smilies, $signature, $deleted) = split('[|]', $thread);
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
			list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);

			$closed = rtrim($closed);
			$threadinfo = "$thread_title|$originalpost[0]|$membername|$timeposted|$numofposts|$file|$sticky|$closed|";
			
			if ($forum == $forum_id)
			{
				if ($sticky == "sticky") { $sticky_threads["$postid"] = $threadinfo; }
				else { $threads["$postid"] = $threadinfo; }
			}
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
	$fp = fopen("../messages/index_".$forum_id, "w");

	foreach ($threads as $postid => $threadinfo)
	{
		echo "<tr><td>",$postid, " - ", $threadinfo; ?></td></tr><?
		
		if ($postid != 0 )
		{
			fwrite($fp, "\n");
		}

		fwrite($fp, $threadinfo);
			
	}
	fclose($fp);

	unset($threads);
}

?></table>