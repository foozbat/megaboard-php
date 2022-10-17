<? 
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$folder = "Inbox";
$view = "";
$page  = "";

if (isset($_GET["folder"])) $folder = $_GET["folder"];
if (isset($_GET["view"])) $view = $_GET["view"];
if (isset($_GET["page"]))  $page = $_GET["page"];

if (!AuthCookie())
{
	// login if we can
	header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."login.php");
	exit();
}

$folderfile = "none";

if ($folder == "Inbox")
{
	$folderfile = "inbox.dat";
}
else if ($folder == "Sent Box")
{
	$folderfile = "sentbox.dat";
}

if (!file_exists("profiles/$cookiemembername/$folderfile"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Folder does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}

$subpagetitle = "Private Messages";
include("./headerinternal.php");

if ($view != "")
{
	$boxlist = file("profiles/$cookiemembername/$folderfile", "r");

	if (!isset($boxlist[$view]))
	{
		ForumMessage("<b>Message does not exist!</b><p>Make sure you typed the correct url.");
		
		include("./skins/$forum_skin/forumfooter.php");
		exit();
	}
	else
	{
		list($sender, $subject, $received, $message, $pm_id, $smilies, $signature, $read) = split('[|]', $boxlist[$view]);

		if (rtrim($read) == "unread" && $folder == "Inbox")
		{
			$tempname = tempnam("./forum_temp", "temp_pmbox_");
			$fpTemp = fopen($tempname, "w");

			for ($i=0; $i<sizeof($boxlist); $i++)
			{
				if ($i == $view)
				{
					fwrite($fpTemp, "$sender|$subject|$received|$message|$pm_id|$smilies|$signature|read\n");
				}
				else
				{
					fwrite($fpTemp, rtrim($boxlist[$i])."\n");
				}
			}
			
			fclose($fpTemp);
			copy($tempname, "profiles/$cookiemembername/$folderfile");
			unlink($tempname);

			DecrementCounter("profiles/$cookiemembername/counter_unread_pm");
		}

		TableHeader("View Private Message >> ".$subject);
		
		if ($folder == "Sent Box")
		{
			$sender = $cookiemembername;
		}

		// make lines that start with ; gray
		$lines = preg_split ('/<br>/', $message);
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
		$loaded_profile = LoadProfile($sender);
		$sender_display = str_replace("&pipe;", "|", $sender);

		$message = join('<br>', $lines);
		if ($smilies != 'OFF') { MakeSmilies($message); }
		MBCode2HTML($message);
		$message = str_replace("&pipe;", "|", $message);

		//FixLine($signature);

		$signature = rtrim($signature);
		if ($signature == "ON" && $loaded_profile["signature"] != "")
		{
			$sig_text = $loaded_profile["signature"];
			MBCode2HTML($sig_text);
			$message = "$message<br><br>__________<br>$sig_text";
		}

		$postsawardname = file("awards/posts", "r");
		$popularawardname = file("awards/replies", "r");

		$postsaward = 0;
		$popularaward = 0;
		if ($sender == $postsawardname[0])
		{
			$postsaward = 1;
		}
		if ($sender == $popularawardname[0])
		{
			$popularaward = 1;
		}

		$islamer = 0;
		if ($islamer != 1)
		{
			$rank = DetermineRank($sender_display, $loaded_profile["posts"], $loaded_profile["specialrank"]);
		}
		?>

		<table border=0 cellspacing=0 cellpadding=0 width=100%>
		<td class=tableborder>
			<table border=0 cellspacing=1 cellpadding=4 width=100%>
			<tr><td class=tablecell1 colspan=2 align=center>
				<table border=0 cellspacing=0 cellpadding=4>
				<tr><td class=tablecell1 width=100%><b>[ Folder: <? echo $folder ?> ]</b></td><td class=tablecell1 nowrap><b>[ <a href="pm.php?folder=Inbox">INBOX</a> ] &nbsp [ <a href="pm.php?folder=Sent Box" nowrap>SENT BOX</a> ] &nbsp [ <a href="sendpm.php" nowrap>SEND MESSAGE</a> ]</b>
				</td></tr>
				</table>
			</td></tr>

			<tr><td width="150" class=titlecell valign="top" align="left" nowrap><font face="verdana" size=2><b>Sender Details:</b></font></td>
			<td class=titlecell valign="top" align="left"><font face="verdana" size=2><b>Message:</b></font></td></tr>

			<tr><td class=tablecell1 width=150 valign=top rowspan=2>
				<a href="viewprofile.php?member=<? echo $sender ?>"><b><? echo $sender ?></b></a> <?

				if ($postsaward == 1)
				{
					echo "<img src=skins/$forum_skin/images/trophy.gif alt='$membername has the most posts on the board!' height=15 width=19 border=0>";
				}
				if ($popularaward == 1)
				{
					echo "<img src='skins/$forum_skin/images/thumbsup.gif' alt='$membername is the most popular person on the board!' height=15 width=19 border=0>";
				}
				?><br><b><font face=verdana size=1><? echo $rank ?></b>

			<p>

			Number of posts: <? echo $loaded_profile["posts"] ?><br>
			Times replied to: <? echo $loaded_profile["replies"] ?>
				</td>

				<td class=tablecell2 width=100%>Received: <? ShowDate($received) ?></td></tr>
			<tr><td class=tablecell1 width=100% align=right>
				<table border="0" width="100%" cellspacing="0" cellpadding="10">
				  <tr>
					<td width="100%" class=tablecell1><font face="verdana" size=2><? echo $message ?></font></td>
				  </tr>
				</table>

				<font face="Arial" size="-1"><a href="sendpm.php?folder=<? echo $folder ?>&replyto=<? echo $view ?>"><img src="skins/<? echo $forum_skin ?>/images/reply.gif" border=0 height=18 width=18>Reply</a> | 
				<a href="sendpm.php?folder=<? echo $folder ?>&replyto=<? echo $view ?>&withquote=1"><img src="skins/<? echo $forum_skin ?>/images/reply.gif" border=0 height=18 width=18>Reply w/ Quote</a>
			</td></tr>

			</table>
		</td></table>

		<? TableFooter() ?><?
		include("./skins/$forum_skin/forumfooter.php");
		exit();
	}
}
?>

<script language="JavaScript">
<!--
function check_all() {
	for (var i=0;i<document.form.elements.length;i++)
	{
		var e = document.form.elements[i];
		if ((e.name != 'checkall') && (e.type=='checkbox'))
		{
			e.checked = document.form.checkall.checked;
		}
	}
}
function CheckCheckAll() {
	var CheckBoxes = 0;
	var TotalOn = 0;
	for (var i=0;i<document.form.elements.length;i++)
	{
		var e = document.form.elements[i];
		if ((e.name != 'checkall') && (e.type=='checkbox'))
		{
			CheckBoxes++;
			if (e.checked)
			{
				TotalOn++;
			}
		}
	}
	if (CheckBoxes==TotalOn)
	{
		document.form.checkall.checked=true;
	}
	else
	{
		document.form.checkall.checked=false;
	}
}
//-->
</script>

<? TableHeader("Private Messages") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		<form action="deletepm.php" method=post name=form>
		<input type="hidden" name="folder" value="<? echo $folder?>">
		<tr><td class=tablecell1 colspan=4 align=center>
			<table border=0 cellspacing=0 cellpadding=4>
			<tr><td class=tablecell1 width=100%><b>[ Folder: <? echo $folder ?> ]</b></td><td class=tablecell1 nowrap><b>[ <a href="pm.php?folder=Inbox">INBOX</a> ] &nbsp [ <a href="pm.php?folder=Sent Box" nowrap>SENT BOX</a> ] &nbsp [ <a href="sendpm.php" nowrap>SEND MESSAGE</a> ]</b>
			</td></tr>
			</table>
		</td></tr>
		<tr><td class=titlecell style="padding: 2"><input name="checkall" type="checkbox" value="checkall" title="Select/Deselect All Messages" onClick="check_all();"></td>
			<td width=100% class=titlecell><b>Subject</b></td>
			<td class=titlecell nowrap><b><? if ($folder == "Inbox") { ?>From<? } else if ($folder == "Sent Box") { ?>Sent To<? } ?></b></td>
			<td class=titlecell><b>Received</b></td></tr>
<?

$boxlist = file("profiles/$cookiemembername/$folderfile", "r");

$total_messages = sizeof($boxlist);

$pages = (int)($total_messages / $maxviewthreads);
$pages += ( ($total_messages % $maxviewthreads) != 0 ? 1 : 0);

if ($page==0) { $page = 1; }
$startat = ($page-1)*$maxviewthreads;

$end = ($total_messages-$startat-$maxviewthreads < 0 ? $total_messages : $maxviewthreads+$startat);

if ($total_messages == 0)
{
	?><tr><td class=tablecell1 colspan=4 align=center>No messages in <? echo $folder ?>.</td></tr><?
}
else
{
	for ($i=sizeof($boxlist)-1; $i >= 0; $i--)
	{
		list($name, $subject, $received, $message, $pm_id, $smilies, $signature, $read) = split('[|]', $boxlist[$i]);

		$read = rtrim($read);

		?><tr><td class=tablecell1 style="padding: 2"><input type="checkbox" name="deletemessage[<? echo $i ?>]" value="delete"></td><td class=tablecell1><a href="pm.php?folder=<? echo $folder ?>&view=<? echo $i ?>" class=<? if ($read == "unread") echo "unread"; else echo "read"; ?>><? echo $subject ?></a></td><td class=tablecell1 nowrap align=center>&nbsp;<a href="viewprofile?member=<? echo $name ?>"><? echo $name ?></a>&nbsp;</td><td class=tablecell1 nowrap><? echo ShowDate($received) ?></td></tr><?
	}
}

?>
		<tr><td class=tablecell1><input type="image" src="skins/<? echo $forum_skin ?>/images/delete.gif" border="0" width=18 height=18 alt="Delete Marked Messages"></td>
			<td class=tablecell1 colspan=3></td></tr>
		</form>
		</table>
	</td></table>
<? TableFooter() ?><?php include("./skins/$forum_skin/forumfooter.php"); ?>