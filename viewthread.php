<? 
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$forum = "";
$thread = "";
$page = "";
$new = "";

if (isset($_GET["forum"])) $forum = $_GET["forum"];
if (isset($_GET["thread"])) $thread = $_GET["thread"];
if (isset($_GET["page"]))  $page = $_GET["page"];
if (isset($_GET["new"]))  $new = $_GET["new"];

// do authentication
$auth_cookie = AuthCookie();
$auth_admin_cookie = AuthAdminCookie();
$auth_moderator_cookie = AuthModeratorCookie();


if (!file_exists("messages/threadfiles/$thread"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified thread does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}


// this is where we will open the thread and loop through messages
	$threadfile = file("messages/threadfiles/$thread");

	if ($page==0) { $page = 1; }
	$startat = ($page-1)*$postsperpage+1;
	$end = (sizeof($threadfile)-$startat-$postsperpage < 0 ? sizeof($threadfile) : $postsperpage+$startat);
	$totalposts = sizeof($threadfile)-1;

	$pages = (int)($totalposts / $postsperpage);
	$pages += ( ($totalposts % $postsperpage) != 0 ? 1 : 0);

$secondtitle = "";
if ($pages > 1)
{
	$secondtitle =  " - PAGE ".$page;
} 

list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);


$subpagetitle = $thread_title;
include("./headerinternal.php");


$closed_text = "";
if (rtrim($closed) == "closed")
{
	$closed_text = " - CLOSED";
}

	//echo "page: ".$page."<br>";
	//echo "pages: ".$pages."<br>";
	//echo "postsperpage: ".$postsperpage."<br>";
	//echo "startat: ".$startat."<br>";

?>

<script language="javascript">
function confirmDelete(messno)
{
	if (confirm('Are you sure you want to delete this post?'))
	{
		window.location = "deletemessage.php?forum=<? echo $forum ?>&thread=<? echo $thread ?>&messno="+messno;
	}
}

<?
if ($auth_cookie || $auth_cookie)
{
?>	
	function confirmCloseThread(messno)
	{
		if (confirm('Are you sure you want to close this thread?'))
		{
			window.location = "closethread.php?forum=<? echo $forum ?>&thread=<? echo $thread ?>";
		}
	}
	
	function confirmOpenThread(messno)
	{
		if (confirm('Are you sure you want to open this thread?'))
		{
			window.location = "openthread.php?forum=<? echo $forum ?>&thread=<? echo $thread ?>";
		}
	}
<?
}
?>
</script>

<? TableHeader($id_to_forum[$forum]." >> ".$thread_title.$closed_text.$secondtitle) ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		  <tr>
    <td width="150" class=titlecell valign="top" align="left" nowrap><font face="verdana" size=2><b>Member Details:</b></font></td>
    <td class=titlecell valign="top" align="left" colspan=4><font face="verdana" size=2><b>Messages:</b></font></td>
  </tr>

<?

	for ($i = $startat; $i < $end; $i++)
	{
		$postsaward = $popularaward = 0; // reset awards

		$threadfile[$i] = rtrim($threadfile[$i]);

		list($membername, $to, $timeposted, $messagetext, $postid, $replytonum, $ip, $smilies, $signature, $deleted) = split('[|]', $threadfile[$i]);

		$messagetext = str_replace("&pipe;", "|", $messagetext);
		if ($smilies != 'OFF') { MakeSmilies($messagetext); }

		// make lines that start with ; gray
		$lines = preg_split ('/<br>/', $messagetext);
		$match = "";
		for ($j=0; $j<sizeof($lines); $j++)
		{
			if ($lines[$j] != "")
			{
				$match = $lines[$j];
				if ( $match[0] == ';' ) // if the line starts with a ";"
				{
					$lines[$j] = "<font class=quotetext><i>$lines[$j]</i></font>";
				}
			}
		}

		// get user profile
		$loaded_profile = LoadProfile($membername);
		$membername_display = str_replace("&pipe;", "|", $membername);

		$messagetext = join('<br>', $lines);
		MBCode2HTML($messagetext);

		$messagetext = wordwrap2($messagetext, 60, '<br>');

		//FixLine($signature);

		if ($signature == "ON" && $loaded_profile["signature"] != "")
		{
			$sig_text = $loaded_profile["signature"];
			MBCode2HTML($sig_text);
			$sig_text = str_replace("&pipe;", "|", $sig_text);
			$messagetext = "$messagetext<br><br>__________<br>$sig_text";
		}

		//$membername = str_replace("&pipe;", "|", $membername);
		$to = str_replace("&pipe;", "|", $to);

		$postsawardname = file("awards/posts");
		$popularawardname = file("awards/replies");

		if ($membername == $postsawardname[0])
		{
			$postsaward = 1;
		}
		if ($membername == $popularawardname[0])
		{
			$popularaward = 1;
		}

		$islamer = 0;
		if ($islamer != 1)
		{
			$rank = DetermineRank($membername_display, $loaded_profile["posts"], $loaded_profile["specialrank"]);
		}

		if ($deleted == "deleted")
		{
			?><tr><td width="150" class=tablecell1 nowrap><font face=verdana size=2><a name="<? echo "$postid"; ?>"></a><a href="viewprofile.php?member=<? echo $membername_display ?>"><b><? echo $membername_display ?></b></a></td>
			<td colspan=4 class=tablecell1 align=center><font face=verdana size=2>This post has been deleted.</font></td></tr><?
		}
		else
		{
?>
	  <tr><td width="150" class=tablecell1 valign="top" align="left" rowspan=2 nowrap><font
		face=verdana size=2><a name="<? echo "$postid"; ?>"></a><a href="viewprofile.php?member=<? echo $membername_display ?>"><b><? echo $membername_display ?></b></a> <?

			if ($postsaward == 1)
			{
				echo "<img src=skins/$forum_skin/images/trophy.gif alt='$membername has the most posts on the board!' height=15 width=19 border=0>";
			}
			if ($popularaward == 1)
			{
				echo "<img src='skins/$forum_skin/images/thumbsup.gif' alt='$membername is the most popular person on the board!' height=15 width=19 border=0>";
			}
			?><br><b><font face=verdana size=1><? echo $rank ?></b>
		
		<br>
		<? if (file_exists("profiles/$membername/avatar.gif")) 
		{
			?><img src="profiles/<? echo $membername ?>/avatar.gif"><?
		}
		else if (file_exists("profiles/$membername/avatar.jpg"))
		{
			?><img src="profiles/<? echo $membername ?>/avatar.jpg"><?
		} ?>
		<br><br>

		Number of posts: <? echo $loaded_profile["posts"] ?><br>
		Times replied to: <? echo $loaded_profile["replies"] ?>
		</font></td>
			<td class=tablecell2 nowrap><font face=verdana size=2><font size=1>To: </font><? if ($to != 'ALL') { ?><a href="viewprofile.php?member=<? echo $to ?>"><? } ?><b><? echo $to ?> </b></a></font></td>
			<td class=tablecell2 nowrap><font face="verdana" size="1"><? echo "#".$postid ?>
					<? if ($replytonum) { echo " in reply to <a href='viewthread.php?forum=$forum&thread=$thread&new=$new#".$replytonum."'>#".$replytonum."</a>"; } ?></a></font></td>
			<td class=tablecell2 align="center" nowrap><font face="verdana" size="1"><? ShowDate($timeposted) ?></font></td>
			<td class=tablecell2 align="center" nowrap><font face="verdana" size="1">[<? echo $i ?> of	<? echo $totalposts ?>]</font></td>
		  </tr>
		<tr>
		<td class=tablecell1 valign="top" align="right" colspan=4>
		<table border="0" width="100%" cellspacing="0" cellpadding="10">
		  <tr>
			<td width="100%" class=tablecell1><font face="verdana" size=2><? echo $messagetext ?></font></td>
		  </tr>
		</table>
		<? if ($forum_flags[$forum]["posting"] == "admin_only" && !$auth_cookie)
		{

		}
		else
		{ ?><font face="Arial" size="-1"><a href="postmessage.php?forum=<? echo $forum ?>&thread=<? echo $thread ?>&replyto=<? echo $i ?>"><img src="skins/<? echo $forum_skin ?>/images/reply.gif" border=0 height=18 width=18>Reply</a> | 
			<a href="postmessage.php?forum=<? echo $forum ?>&thread=<? echo $thread ?>&replyto=<? echo $i ?>&withquote=1"><img src="skins/<? echo $forum_skin ?>/images/reply.gif" border=0 height=18 width=18>Reply w/ Quote</a>
		  <? }
		  
		  if ( ($membername == $cookiemembername && $auth_cookie) || $auth_admin_cookie || ($auth_moderator_cookie && AccessLevel($membername_display) != "administrator") )
		  { ?>
		  | <a href="editmessage.php?thread=<? echo $thread ?>&messno=<? echo $i ?>"><img src="skins/<? echo $forum_skin ?>/images/edit.gif" border=0 heigh=18 width=18>Edit</a>  | <a href="Javascript:confirmDelete('<? echo $i ?>')"><img src="skins/<? echo $forum_skin ?>/images/delete.gif" border=0 height=18 width=18>Delete</a>
		  <? } ?>
		  </td>
	  </tr>
<?
		}	
	}
	if (/*$i >= $startat+$postsperpage || ( $i >= $totalposts && $totalposts > $postsperpage)*/ $pages > 1 )
	{
		?><tr><td class=tablecell1 colspan=5><font face=verdana size=1>This thread is multiple pages: [ PAGE: <?

			for ($j=1; $j<=$pages; $j++)
			{
				$newstartat = ($j-1)*$postsperpage+1;
				if ($newstartat != $startat)
				{
					$new = $totalposts;
					if ($j < $pages) $new = $j*$postsperpage;

					?><a href="viewthread.php?thread=<? echo $thread ?><? if ($j > 1) { ?>&page=<? echo $j; } ?>&new=<? echo $new ?>"><b><?
				}
				echo $j ?></b></a> <?
			}
			?> ]</b></td></tr><?
	}

?>
		</table>
		</td></table>
<? TableFooter() ?><? TimeStampNow() ?>

<? if ($auth_admin_cookie || $auth_moderator_cookie)
{ ?>

<p>
<? TableHeader("Administration Options") ?>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>
		<table cellspacing=1 cellpadding=8 border=0 width=100%>
		<form action="movethread.php" action=post>
		<tr><td class=tablecell1 width=100% valign=top align=center>
			<b>[ <?
			if (rtrim($closed) == "closed")
			{
				?><a href="Javascript:confirmOpenThread()">OPEN THREAD</a><?
			}
			else
			{			
				?><a href="Javascript:confirmCloseThread()">CLOSE THREAD</a><?
			}
			?> ] &nbsp; [ MOVE THREAD: <select class=forminput name=to_forum style="font-size: 10px"><?
		foreach ($forumindex as $categorytitle => $categoryforums)
		{
			foreach ($categoryforums as $forumid)
			{
				echo "<option value=$forumid>$categorytitle >> $id_to_forum[$forumid]</option>\n";
			}
		} ?></select> <input type=hidden name="from_forum" value="<? echo $forum ?>"><input type=hidden name="thread" value="<? echo $thread ?>"><input type=submit name=submit value="Move" class=formbutton> ]
		</td></tr></table>
	</td></table>
<? TableFooter() ?><? } include("./skins/$forum_skin/forumfooter.php"); ?>