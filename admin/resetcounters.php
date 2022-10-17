<?
// Reset ALL Counters
// only do this if you're sure you want to reset EVERYTHING to zero

include("../settings.inc"); 
include("../support.inc");

$profile_dir = opendir("../profiles/");
while (false !== ($member = readdir($profile_dir)))
{ 
	if ($member != "." && $member != ".." && $member != '.htaccess' && $member != '.htpasswd' &&
		$member != "counter_totalmembers" && $member != "info_newestmember")
	{
		$fp = fopen("../profiles/$member/posts.dat", "w");
		fwrite($fp, "0");
		fclose($fp);

		$fp = fopen("../profiles/$member/replies.dat", "w");
		fwrite($fp, "0");
		fclose($fp);
	}
}

?>User Counters Reset<?

$fp = fopen("../messages/counters/counter_lastmessage", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/counters/counter_lastthread", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/counters/counter_totalmessages", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/counters/counter_totalthreads", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/counters/counter_TRASH_totalposts", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/counters/counter_TRASH_totalthreads", "w");
fwrite($fp, "0");
fclose($fp);

$fp = fopen("../messages/info_TRASH_lastpost", "w");
fwrite($fp, "never|posted");
fclose($fp);

?><p>Forum Counters Reset<?