<? include("./headerinternal.php"); 

/*$handle = opendir("profiles/");
$moderatornames = array();

while (false !== ($file = readdir($handle)))
{ 
	if ($file != "." && $file != ".." && $file != '.htaccess' && $file != '.htpasswd')
	{
		$profile = file("profiles/$file/profile.dat", "r");

		if ($profile[3] == 'moderator')
		{
			array_push($moderatornames, $file);
			//echo "<a href=viewprofile.php?member=$file>$file</a><br>";
		}

		// get newest member
		$newestmember = $file;

		$totalforumusers++;
	}
}*/

$totalforumposts = file("messages/counters/counter_totalmessages");
$totalforumthreads = file("messages/counters/counter_totalthreads");
$totalforumusers = file("profiles/counter_totalmembers");
$newestmember = file("profiles/info_newestmember");

RePipe($newestmember[0]);



?>

<? TableHeader("Welcome to the Forums!") ?>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>

		<table cellspacing=1 cellpadding=6 border=0 width=100%>
		<tr><td class=tablecell1 width=50% valign=top><b>Forum Information:</b><br><font size=1>
			Total Members: <b><? echo $totalforumusers[0] ?></b><br>
			Total Threads: <b><? echo $totalforumthreads[0] ?></b><br>
			Total Posts: <b><? echo $totalforumposts[0] ?></b><br>
			Our newest member is <a href="viewprofile.php?member=<? echo $newestmember[0] ?>"><? echo $newestmember[0] ?></a>
			</td><td class=tablecell1 width=50% valign=top>
				
				<?
				if (AuthCookie())
				{
					?><b>Logged in as:</b><br><font size=1>
					<b><? echo str_replace("&pipe;", "|", $cookiemembername); ?></b><br>
					<? echo DetermineRank(str_replace("&pipe;", "|", $cookiemembername), $my_profile["posts"], $my_profile["specialrank"]) ?><br>
					number of posts: <? echo $my_profile["posts"] ?><br>
					times replies to: <? echo $my_profile["replies"] ?>
					<?
				}
				else
				{
					?><form action="login.php" method=post>
					<b>You are not logged in:</b><br>

					<table cellspacing=1 cellpadding=0 border=0>
					<tr><td colspan=2></td></tr>
					<tr><td colspan=2></td></tr>
					<tr><td class=tablecell1><font size=1>Membername:&nbsp</td><td><input type=text name="login_member_name" size=10 class=loginsmall tabindex=1></td><td rowspan=2>&nbsp <input type=submit name=submit value="Login" class=formbutton tabindex=3></td></tr>
					<tr><td class=tablecell1><font size=1>Password:</td><td><input type=password name="login_password" size=10 class=loginsmall tabindex=2></td></tr>
					
					</form>
					</table>
					<?
				} ?>
			</td></tr>
			
		</table>

	</td></table>

<? TableFooter() ?>
<p>

<? TableHeader("Main Forum Index") ?>


	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		<tr><td width=100% class=titlecell2><b>Forums:</b></td>
			<td nowrap class=titlecell2><b>Threads</b></td>
			<td nowrap class=titlecell2><b>Posts</b></td>
			<td nowrap align=center class=titlecell2><b>Last Post</b></td></tr>
		<!-- General Forums -->
		<?

		foreach ($forumindex as $categorytitle => $categoryforums)
		{
			// show separator
			?><tr><td colspan=4 class=separatorcell><b><? echo $categorytitle ?></b></td></tr><?
	
			foreach ($categoryforums as $forumid)
			{
				if (($forum_flags[$forumid]["visible"] == "admin_only" && !AuthAdminCookie()) ||
					($forum_flags[$forumid]["visible"] == "moderator_only" && !AuthModeratorCookie()) )
				{
					continue;
				}
					// open forum info
					$forumthreads = file("messages/counters/counter_".$forumid."_totalthreads");
					$forumposts = file("messages/counters/counter_".$forumid."_totalposts");
					$lastpost = file("messages/info_".$forumid."_lastpost");

					list($newestposttime, $newestposter) = split('[|]', $lastpost[0]);

					?>
		<tr><td width=100% class=tablecell1>
			<table border=0 cellspacing=0 cellpadding=0>
			<tr><td valign=middle><img src="skins/<? echo $forum_skin ?>/images/<?
			if ($forumid == "TRASH")
			{
				echo "trashcan.gif";
			}
			else if ($forum_flags[$forumid]["closed"] == "yes")
			{
				echo "folder-closed.gif";
			} 
			else
			{
				echo "folder.gif";
			}?>" width=24 height=22>&nbsp;</td><td width=100% class=tablecell1><font face=verdana size=2><b><a href="viewforum.php?forum=<? echo $forumid ?>"><? echo $id_to_forum[$forumid] ?></b></a>
			<? if ($forumdescription[$forumid ] != '')
			{ ?><br><font size=1>» <? echo $forumdescription[$forumid ] ?>
			<? } ?></td></tr></table></td>
			<td nowrap align=center class=tablecell1><font face=arial size=2><? echo $forumthreads[0] ?></td>
			<td nowrap align=center class=tablecell1><font face=arial size=2><? echo $forumposts[0] ?></td>
			<td nowrap align=right class=tablecell1><font face=arial size=2><? 
				if ($lastpost[0] == "never|posted")
				{
					echo "never posted";
				}
				else
				{
					ShowDate($newestposttime);

				$newestposter_display = str_replace("&pipe;", "|", $newestposter);

				?><br><font face=verdana size=1>by <b><a href="viewprofile.php?member=<? echo $newestposter_display ?>" <? if (AccessLevel($newestposter) == "administrator") { ?> class=specialrank<? } ?>><? echo $newestposter_display ?></a>
				</b>
				<? } ?></td></tr>
		<?
			} 
		} ?>
		</table>
		</td></table>
<? TableFooter() ?><? TimeStampNow() ?>
<p>
<? TableHeader("Users Currently Browsing the Forums") ?>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>

		<table cellspacing=1 cellpadding=6 border=0 width=100%>
		<tr><td class=tablecell1 width=100% valign=top>
		<?	$time_now = time();
			
			ignorecasesort($active_users);

			echo "<font size=1>There are currently <b>", sizeof($active_users), "</b> members using the forums:<br><br></font>";

			for ($i=0; $i<sizeof($active_users); $i++)
			{
				list($cur_user, $cur_user_time) = split('[|]', $active_users[$i]);

				//$time_diff = ($time_now - $cur_user_time);
				$cur_user_display = $cur_user;
				RePipe($cur_user_display);

				echo "<a href=\"viewprofile.php?member=$cur_user_display\"";
				if (AccessLevel($cur_user) == "administrator") echo " class=specialrank";
				echo ">";
				
				echo "$cur_user_display</a>";
				if ($i < sizeof($active_users)-1) echo ", ";
			}
		?>
		</td></tr></table>
	</td></table>
<? TableFooter() ?>
		<? include("./skins/$forum_skin/forumfooter.php"); ?>