<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$forum = "";
$page  = "";

// meh
$my_profile["quickforum"] = 'OFF';

if (isset($_GET["forum"])) $forum = $_GET["forum"];
if (isset($_GET["page"]))  $page = $_GET["page"];

if (!file_exists("messages/index_$forum"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified forum does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

$threadindex =  file("messages/index_".$forum);

$forumthreads = sizeof($threadindex);

$pages = (int)($forumthreads / $maxviewthreads);
$pages += ( ($forumthreads % $maxviewthreads) != 0 ? 1 : 0);

if ($page==0) { $page = 1; }
$startat = ($page-1)*$maxviewthreads;

$end = ($forumthreads-$startat-$maxviewthreads < 0 ? $forumthreads : $maxviewthreads+$startat);

$secondtitle = "";
if ($pages > 1)
{
	$secondtitle =  " - <b>PAGE ".$page."</b>";
}


if (	($forum_flags[$forum]["visible"] == "admin_only" && !AuthAdminCookie()) ||
		($forum_flags[$forum]["visible"] == "moderator_only" && !AuthModeratorCookie()) )
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to view this forum.</b><p>If you feel you should have access to view this forum, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

$subpagetitle = $id_to_forum[$forum];
include("./headerinternal.php");

?>

<? TableHeader("Current Forum Listing".$secondtitle) ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		<tr><td width=100% class=titlecell colspan=2><font face=verdana size=2><b>Forums Topics:</b></td>
				<td nowrap align=center class=titlecell><font face=verdana size=2><b>Posted by</b></td>
				<td nowrap align=center class=titlecell><font face=verdana size=2><b>Posts</b></td>
				<td nowrap align=center class=titlecell><font face=verdana size=2><b>Last Post</b></td></tr>
		<tr><td class=tablecell1 colspan=5>
			<table border=0 cellspacing=0 cellpadding=0 width=100%>
			<tr><td><table border=0 cellspacing=0 cellpadding=0>
			<tr><td valign=middle class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/<?
			if ($forum == "TRASH")
			{
				echo "trashcan.gif";
			}
			else if ($forum_flags[$forum]["closed"] == "yes")
			{
				echo "folder-closed.gif";
			} 
			else
			{
				echo "folder.gif";
			} ?>" width=24 height=22>&nbsp;</td><td width=100% class=tablecell1><font face=verdana size=2><b>
			<? echo $id_to_forum[$forum] ?></b></a><? if ($forumdescription[$forum] != '')
			{ ?><br><font size=1>» <? echo $forumdescription[$forum] ?>
			<? } ?></td></tr></table></td>
			<? if ( !(($forum_flags[$forum]["posting"] == "admin_only" || $forum_flags[$forum]["posting"] == "admin_start_user_reply") && !AuthAdminCookie()) && $forum_flags[$forum]["closed"] == "no")
			{
				?><td align=right><font face=verdana size=2><b><a href="postmessage.php?forum=<? echo $forum ?>"><img src="skins/<? echo $forum_skin ?>/images/post.gif" width=18 height=16 border=0>Post New Message</a></b></td><?
			}
			else if ($forum_flags[$forum]["posting"] == "admin_start_user_reply" && !AuthAdminCookie())
			{
				?><td align=right class=tablecell1><font face=verdana size=2><b>[ This forum is open to replies only ]</b></td><?
			}
			else
			{
				?><td align=right class=tablecell1><font face=verdana size=2><b>[ This forum is closed to posting ]</b></td><?
			}
			?></tr></table></td></tr>
		<?
			if ($forumthreads >= 1)
			{
				for ($i = $startat; $i < $end; $i++)
				{
					$moved_to = "";
					list ($threadtitle, $starter, $lastposter, $timeposted, $numofposts, $threadid, $sticky, $closed, $moved_to) = split('[|]', $threadindex[$i]);

					$threadpages = (int)($numofposts / $postsperpage);
					$threadpages += ( ($numofposts % $postsperpage) != 0 ? 1 : 0);

					$moved_to = rtrim($moved_to);

		?>
		<tr><td class=tablecell1 style="padding: 0px"><img src="<?
		
		if (rtrim($closed) == "closed" && $moved_to == "")
		{
			echo "skins/$forum_skin/images/lock.gif";
		}
		else if ($sticky == "sticky" && $moved_to == "")
		{
			echo "skins/$forum_skin/images/sticky.gif";
		}
		else
		{
			echo "spacer.gif";
		}
		?>" width=16 height=16></td>
			<td class=tablecell1 width=100%><font face=arial size=2><?
			
			if ($moved_to != "")
			{
				?>Moved: <?
			}
			else if ($sticky == "sticky")
			{
				?>[ Sticky: ] <?
			}
			?><a href="viewthread.php?thread=<? echo $threadid ?>&new=<? if ($threadpages > 1) { echo $postsperpage; } else { echo $numofposts; } ?>" class="threadlink"><? echo $threadtitle ?></a><?
					if ($threadpages > 1)
					{
						?>&nbsp <font face=verdana size=1>[ Page: <b><?
						for ($j=1; $j<=$threadpages; $j++)
						{
							?><a href="viewthread.php?thread=<? echo $threadid; if ($j!=1) {?>&page=<? echo $j; } ?>&new=<? if ($j < $threadpages) { echo $j*$postsperpage; } else { echo $numofposts; } ?>" class=threadlink><b><? echo $j ?></b></a> <?	
						}
						?></b>]</font><?
					}
			
			$starter_display = str_replace("&pipe;", "|", $starter);
			$lastposter_display = str_replace("&pipe;", "|", $lastposter);

			?>
					
			</td>
			<td class=tablecell1 align=center nowrap><font face=arial size=2><a href="viewprofile.php?member=<? echo $starter_display ?>"<? if (AccessLevel($starter_display) == "administrator") echo " class=specialrank" ?>><? echo $starter_display ?></a></td>
			<td class=tablecell1 nowrap align=center><font face=arial size=2><? echo $numofposts ?></td>
			<td class=tablecell1 nowrap><font face=arial size=2><? ShowDate($timeposted) ?><font size=1> by <a href="viewprofile.php?member=<? echo $lastposter_display ?>"<? if (AccessLevel($lastposter) == "administrator") echo " class=specialrank" ?>><? echo $lastposter_display ?></a></font></td></tr>
		<?		
				}
			}
			else
			{
				?><tr><td class=tablecell1 colspan=5 align=center><font face=verdana size=2>[ No posts in forum ]<?
			}
			if ($pages > 1)
			{
				?><tr><td class=tablecell1 colspan=5><font face=verdana size=1>This forum is multiple pages: [ PAGE: <? 
				
					for ($j=1; $j<=$pages; $j++)
					{
						$newstartat = ($j-1)*$maxviewthreads+1;

						if ($j == $page)
						{
							echo "<b>$j </b>";
						}
						else
						{
							if ($newstartat != $startat)
							{
								?><a href="viewforum.php?forum=<? echo $forum ?>&page=<? echo $j ?>"><b><?
							} 
							echo $j ?></b></a> <?
						}
					}
					?> ]</b></td></tr><?
			}
			?>
		</table>
	</td></table>
<? TableFooter() ?><? TimeStampNow() ?>

<p>
<? TableHeader("Forum Toolbar") ?>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>

		<table cellspacing=1 cellpadding=4 border=0 width=100%><form action="viewforum.php">
		<tr><td class=tablecell1>
			<b>Forum Jump:</b><br>
			<select name=forum class=forminput style="font-size: 10px"><?
			foreach ($forumindex as $categorytitle => $categoryforums)
			{
				foreach ($categoryforums as $forumid)
				{
					if ($forum_flags[$forumid]["visible"] == "admin_only" && !AuthAdminCookie())
					{
						continue;
					}
					echo "<option value=$forumid>$categorytitle >> $id_to_forum[$forumid]</option>\n";
				}
			} ?></select> <input type=submit value=" go " class=formbutton></td>
			</form>
			<td class=tablecell1>
				<font face=arial class="unread">Has Unread Posts</font><br>
				<font face=arial class="read">Previously Read Threads</font>
			</td>
			<td class=tablecell1><font face=arial>
				<img src="skins/<? echo $forum_skin ?>/images/lock.gif" width=16 height=16> Closed Thread<br>
				<img src="skins/<? echo $forum_skin ?>/images/sticky.gif" width=16 height=16> Sticky Thread
			</td>
			<form action="search.php">
			<td class=tablecell1>
			<b>Search this forum:</b><br>
			<input type=text size=20 name=search value="" class="forminput">
			</td>
			</form>
		</tr></form></table>
	</td></table>
<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>