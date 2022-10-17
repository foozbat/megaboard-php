<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

// form variables
$postit        = "";
$preview       = "";
$message_text  = "";
$forum         = "";
$replyto       = "";
$message_title = "";
$login_member_name = "";
$login_password    = "";
$msg_edited    = "";
$smilies   = "";
$signature = "";
$thread    = "";
$withquote = "";
$to = "";
$messno = "";

if (isset($_POST["postit"]))        $postit        = $_POST["postit"];
if (isset($_POST["preview"]))       $preview       = $_POST["preview"];
if (isset($_POST["message_text"]))  $message_text  = $_POST["message_text"];
if (isset($_POST["forum"]))         $forum         = $_POST["forum"];
if (isset($_GET["forum"]))          $forum         = $_GET["forum"];
if (isset($_POST["replyto"]))       $replyto       = $_POST["replyto"];
if (isset($_GET["replyto"]))        $replyto       = $_GET["replyto"];
if (isset($_POST["message_title"])) $message_title = $_POST["message_title"];
if (isset($_POST["login_member_name"])) $login_member_name = $_POST["login_member_name"];
if (isset($_POST["login_password"]))    $login_password    = $_POST["login_password"];
if (isset($_POST["msg_edited"]))    $msg_edited    = $_POST["msg_edited"];
if (isset($_POST["smilies"]))   $smilies   = $_POST["smilies"];
if (isset($_POST["signature"])) $signature = $_POST["signature"];
if (isset($_POST["thread"]))    $thread    = $_POST["thread"];
if (isset($_GET["thread"]))     $thread    = $_GET["thread"];
if (isset($_POST["thread"]))    $thread    = $_POST["thread"];
if (isset($_GET["withquote"]))  $withquote = $_GET["withquote"];
if (isset($_POST["to"]))      $to    = $_POST["to"];
if (isset($_GET["messno"]))  $messno = $_GET["messno"];
if (isset($_POST["messno"])) $messno = $_POST["messno"];

// error variables
$membernamefailed = 0;
$passwordfailed = 0;
$message_title_blank = 0;
$messagetext_blank = 0;
$message_text_too_long = 0;


// OPEN OLD THREAD
$threadfile = file("messages/threadfiles/$thread", "r");
list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);


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

list($old_membername, $old_to, $old_timeposted, $old_messagetext, $old_postid, $old_replytonum, $old_ip, $old_smilies, $old_signature, $old_deleted) = split('[|]', $threadfile[$messno]);


if ($forum_flags[$forum]["closed"] == "yes")
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

if ($old_membername == $cookiemembername || AuthAdminCookie() || AuthModeratorCookie() || (AuthModeratorCookie() && AccessLevel($membername_display) != "administrator"))
{
	//echo $old_membername;
}
else
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to edit this post.</b><p>If you feel you should have access to edit this post, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}

if ($old_deleted == "deleted")
{
	include("./headerinternal.php");
	
	ForumMessage("<b>This post has been deleted.</b><p>You cannot edit this post.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}


//
if ($postit == "Edit Message" || $preview == "Preview Message")
{
	// ERROR CHECK THIS POST
	////////////////////////

	if (strlen($message_text) > 20000)
	{
		$message_text_too_long = 1;
	}
	
	if (strlen($message_title) > 100)
	{
		$message_text_too_long = 1;
	}
	else
	{
		// parse baddies
		$message_title = preg_replace('/\s+/', ' ', $message_title);
		$message_title = str_replace("|", "&pipe;", $message_title);

		if ( ($message_title == '' || $message_title == ' ') && $postit == "Post Message" ) { $message_title_blank = 1; }
	}

	if ($message_text == '') { $messagetext_blank = 1; }
}


$message_text_for_editing = $message_text;

if ($postit == "Edit Message")
{
	if ( !($message_title_blank || $messagetext_blank || $message_text_too_long) )
	{
		// get rid of html baddies
		FixLine($message_title);

		$time_now = date("m-d-Y h:ia");

		FixParagraph($message_text);
		MakeHTMLSpaces($message_text);

		if ($msg_edited == "ON") $message_text .="<br><br>[ Message edited by ".$cookiemembername." on ".$time_now." ]";

		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else
			$ip = $_SERVER["REMOTE_ADDR"];

		if ($smilies != "ON") { $smilies = 'OFF'; }

		// prevent posting at same time
		WaitForWriteToFinish();

		$replytonum = "";
		$to = "ALL";

		$tempname = tempnam("./forum_temp", "temp_thread_");
		$fpTemp = fopen($tempname, "w");

 		$old_deleted = rtrim($old_deleted);

		// overwrite old thread file
		for ($i=0; $i<sizeof($threadfile); $i++)
		{
			if ($i == $messno)
			{
				fwrite($fpTemp, "$old_membername|");
				fwrite($fpTemp, "$old_to|");
				fwrite($fpTemp, "$old_timeposted|");
				fwrite($fpTemp, "$message_text|");
				fwrite($fpTemp, "$old_postid|");
				fwrite($fpTemp, "$old_replytonum|"); // replytonum
				fwrite($fpTemp, "$ip|");
				fwrite($fpTemp, "$smilies|");
				fwrite($fpTemp, "$signature|");
				fwrite($fpTemp, "$old_deleted\n");
			}
			else
			{
				fwrite($fpTemp, rtrim($threadfile[$i])."\n");
			}
		}

		fclose($fpTemp);

		copy($tempname, "messages/threadfiles/$thread");
		unlink($tempname);


		// GOTO NEW THREAD
		$threadsize = sizeof($threadfile)-1;

		$threadpages = (int)($threadsize / $postsperpage);
		$threadpages += ( ($threadsize % $postsperpage) != 0 ? 1 : 0);

		$page = 1;
		if ($threadpages > 1)
		{
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
	}
}

$subpagetitle = "Edit This Message";
include("./headerinternal.php");

$dialogue_title = "Edit This Message";
$thread_title = "";

list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);


// DO PREVIEW
if ($preview == "Preview Message")
{
	if ( !($message_title_blank || $messagetext_blank || $passwordfailed || $membernamefailed || $message_text_too_long) )
	{
		$message_text_display = $message_text;
		FixParagraph($message_text_display);
		MBCode2HTML($message_text_display);
		MakeHTMLSpaces($message_text_display);
		$message_text_display = str_replace("&pipe;", "|", $message_text_display);

		// make lines that start with ; gray
		$lines = preg_split ('/<br>/', $message_text_display);
		$match = "";
		for ($j=0; $j<sizeof($lines); $j++)
		{
			if ($lines[$j] != "")
			{
				$match = $lines[$j];
				if ( $match[0] == ';' ) // if the line starts with a ";"
				{
					$lines[$j] = "<font color=silver><i>$lines[$j]</i></font>";
				}
			}
		}

		// get user profile
		$loaded_profile = LoadProfile($old_membername);

		$message_text_display = join('<br>', $lines);
		if ($smilies != 'OFF') { MakeSmilies($message_text_display); }

		FixLine($message_title);

		if ($signature == 'ON' && $loaded_profile["signature"] != '')
		{
			$sig_text = $loaded_profile["signature"];
			MBCode2HTML($sig_text);
			$message_text_display .= "<br><br>__________<br>$sig_text";
		}

		$cookiemembername = str_replace("&pipe;", "|", $cookiemembername);
		$to = str_replace("&pipe;", "|", $to);

		$postsawardname = file("awards/posts", "r");
		$popularawardname = file("awards/replies", "r");

		/*if ($membername == $postsawardname[0])
		{
			$postsaward = 1;
		}
		if ($membername == $popularawardname[0])
		{
			$popularaward = 1;
		}*/

		//if ($islamer != 1)
		//{
			$rank = DetermineRank($old_membername, $loaded_profile["posts"], $loaded_profile["specialrank"]);
		//}

		$time_now = time();

		?>

	<? TableHeader("$id_to_forum[$forum] >> $thread_title [ PREVIEW ]") ?>
	
		<table border=0 cellspacing=0 cellpadding=0 width=100%>
		<td class=tableborder>
			<table border=0 cellspacing=1 cellpadding=4 width=100%>
			<tr><td width="150" class=tablecell1 valign="top" align="left" nowrap><font face="verdana" size=2><b>Member Details:</b></font></td>
				<td class=tablecell1 valign="top" align="left" colspan=4><font face="verdana" size=2><b>Messages:</b></font></td></tr>

			<tr><td width="150" class=tablecell1 valign="top" align="left" rowspan=2 nowrap><font face=verdana size=2><a name="6.50.1"></a>
				<a href="viewprofile.php?member=<? echo $old_membername ?>"><b><? echo $old_membername ?></b></a> <?

					/*if ($postsaward == 1)
					{
						echo "<img src=/megaboard/skins/$forum_skin/images/trophy.gif alt='$membername has the most posts on the board!' height=15 width=19 border=0>";
					}
					if ($popularaward == 1)
					{
						echo "<img src='/megaboard/skins/$forum_skin/images/thumbsup.gif' alt='$membername is the most popular person on the board!' height=15 width=19 border=0>";
					}*/
					?><br><b><font face=verdana size=1><? echo $rank ?></b>

				<p>
				Number of posts: <? echo $loaded_profile["posts"] ?><br>
				Times replied to: <? echo $loaded_profile["replies"] ?>
				</font></td>
				<td class=tablecell2 nowrap><font face=verdana size=2 color=silver><font size=1>To: </font><b>ALL</b></font></td>
				<td class=tablecell2 nowrap><font face="verdana" size="1" color=silver>#<? echo "$old_postid" ?><?
					if ($old_replytonum)
					{
						echo " in reply to #".$old_replytonum;
					}
					?></font></td>
				<td class=tablecell2 align="center" nowrap><font face="verdana" size="1" color=silver><? ShowDate($time_now) ?></font></td>
				<td class=tablecell2 align="center" nowrap><font face="verdana" size="1" color=silver>[1 of 1]</font></td></tr>
				<tr>
				<td class=tablecell1 valign="top" align="right" colspan=4>
					<table border="0" width="100%" cellspacing="0" cellpadding="10">
					<tr><td width="100%"><font face="verdana" size=2><? echo $message_text_display ?></font></td></tr>
					</table><img src=spacer.gif height=25></td></tr></table>
		</td></table>
		<? TableFooter() ?><table cellspacing=0 cellpadding=4 border=0 width=100%><tr><td align=right><font face=verdana size=1 class=lightcolor>All times GMT 
<? if (isset($my_profile["timezone"])) echo $my_profile["timezone"];
else echo $servertimezone; ?> 
Hours</font></td></tr></table><p><?
	} 
}

// DISPLAY FORM
$thread_title .= " >> ";



?>

<? TableHeader($id_to_forum[$forum]." >> ".$thread_title.$dialogue_title) ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%><form action="editmessage.php" method="post">
				
<? if ($old_replytonum != "")
{
				?>
				<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>To:</b></td>
				<td class=tablecell1 valign=top width=75%>
				<select name="to" class=forminput tabindex=1>
				<option selected value="<? echo $old_to ?>"><? echo $old_to ?></option>
				<option value="ALL">ALL</option>
				</select>
				</td></tr>
<?
}
?>
		<tr><td class=tablecell1 align=right valign=top><font face=verdana size=2><?
					if ($messagetext_blank)
					{
						?><font class=error>You must enter some text!</font><?
					}
					else
					{
						?><b>Message Text:</b><?
					}
			?><font size=1><p>Enter your message here.<p>If you wish to quote a line of text, simply put a semicolon ";" in front of the line.<p>For a list of smilies, <a href="faq.php">click here</a>..<?
			if ($message_text_too_long)
			{
				?><font class=error>Maximum length is 20000 characters!</font><?
			}
			?></td>
			<td class=tablecell1 valign=top width=75%><textarea cols=50 rows=15 wrap="VIRTUAL" name="message_text" class=forminput tabindex=2><? 
			if ($message_text != "") 
			{
				FixEditingText($message_text);
				echo $message_text;
			}
			else
			{
				$old_messagetext = str_replace("<br>","\n",$old_messagetext);
				$old_messagetext = str_replace("&pipe;","|",$old_messagetext);

				FixEditingText($old_messagetext);

				echo $old_messagetext;
			}
			?></textarea></td></tr>
		<tr><td class=tablecell1 align=right valign=top nowrap><font face=verdana size=2><b>Post Options:</b></td>
			<td class=tablecell1 valign=top width=75%><font face="arial" size=2>
			<?
			if (AuthAdminCookie())
			{ ?>
			<input type="checkbox" name="msg_edited" value="ON" checked tabindex=4>[<font class=error>ADMIN</font>] Check here to show "Message Edited" text.<br>
			<? } ?>
			<input type="checkbox" name="smilies" value="ON" checked tabindex=4>Check here to enable smilie icons.<br>
			<input type="checkbox" name="signature" value="ON" checked tabindex=5>Check here to include your signature in this post.
			</td></tr>
			<? if ($replyto > 0) { ?>
			<input type="hidden" name="replyto" value="<? echo $old_replyto ?>">
			<? } ?>
			<input type="hidden" name="thread" value="<? echo $thread ?>">
			<input type="hidden" name="messno" value="<? echo $messno ?>">
		<tr><td class=tablecell1 align=center valign=top colspan=2 width=100%><input type=submit name=postit value="<? if ($replyto > 0) echo "Post Reply"; else echo "Edit Message"; ?>" class=formbutton tabindex=6> <input type=submit name=preview value="Preview Message" class=formbutton tabindex=7> <input type=reset value="Clear Form" class=formbutton tabindex=8></td></tr></form>
		</table>
		</td></table>
		<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>