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
$sticky    = "";
$smilies   = "";
$signature = "";
$thread    = "";
$withquote = "";
$to = "";

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
if (isset($_POST["sticky"]))    $sticky    = $_POST["sticky"];
if (isset($_POST["smilies"]))   $smilies   = $_POST["smilies"];
if (isset($_POST["signature"])) $signature = $_POST["signature"];
if (isset($_POST["thread"]))    $thread    = $_POST["thread"];
if (isset($_GET["thread"]))     $thread    = $_GET["thread"];
if (isset($_GET["withquote"]))  $withquote = $_GET["withquote"];
if (isset($_POST["to"]))    $to    = $_POST["to"];

// error variables
$membernamefailed = 0;
$passwordfailed = 0;
$message_title_blank = 0;
$messagetext_blank = 0;
$message_text_too_long = 0;
$message_title_too_long = 0;

if (!file_exists("messages/index_$forum"))
{
	include("./headerinternal.php");

	ForumMessage("<b>Specified forum does not exist!</b><p>Make sure you typed the correct url.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}
else if ($forum_flags[$forum]["closed"] == "yes")
{
	include("./headerinternal.php");
	
	ForumMessage("<b>This forum is closed.</b><p>No new posts can be made in this forum.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();

}
else if ($forum_flags[$forum]["posting"] == "admin_only" && !AuthAdminCookie())
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to post in this forum</b><p>If you feel you should have access to post in this forum, check to make sure you have logged in properly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}
else if ($replyto == "" && ($forum_flags[$forum]["posting"] == "admin_start_user_reply" && !AuthAdminCookie()))
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to post in this forum</b><p>If you feel you should have access to post in this forum, check to make sure you have logged in properly");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}
else if ($forum_flags[$forum]["posting"] == "moderator_only" && !AuthModeratorCookie())
{
	include("./headerinternal.php");
	
	ForumMessage("<b>You are not authorized to post in this forum</b><p>If you feel you should have access to post in this forum, check to make sure you have logged in properly");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}

if ($postit == "Post Message" || $postit == "Post Reply" || $preview == "Preview Message")
{
	// ERROR CHECK THIS POST
	////////////////////////

	if (!AuthCookie())
	{
		// login if we can
		if (file_exists("profiles/$login_member_name/profile.dat"))
		{
			$profile = file("profiles/$login_member_name/profile.dat", "r");
			$stored_pass = chop($profile[1]);

			$encrypted_pass = crypt($login_password,'limabean');

			if ($encrypted_pass == $stored_pass)
			{
				setcookie ("mb4php_member_name",    $login_member_name, time()+5184000, $forumpath, $forumdomain);
				setcookie ("mb4php_pass_encrypted", $encrypted_pass, time()+5184000, $forumpath, $forumdomain);
			}
			else
			{
				$passwordfailed = 1;

				setcookie ("mb4php_member_name",    " ", time()-5184000, $forumpath, $forumdomain);
				setcookie ("mb4php_pass_encrypted", " ", time()-5184000, $forumpath, $forumdomain);

			}
		}
		else
		{
			$membernamefailed = 1;

			setcookie ("mb4php_member_name",    " ", time()-5184000, $forumpath, $forumdomain);
			setcookie ("mb4php_pass_encrypted", " ", time()-5184000, $forumpath, $forumdomain);
		}
	}


	if (strlen($message_text) > 20000)
	{
		$message_text_too_long = 1;
	}

	if (strlen($message_title) > 60)
	{
		$message_title_too_long = 1;
	}
	else
	{
		// parse baddies
		$message_title = preg_replace('/\s+/', ' ', $message_title);
		$message_title = str_replace("|", "&pipe;", $message_title);

		if ( ($message_title == '' || $message_title == ' ') && $postit == "Post Message" ) { $message_title_blank = 1; }
	}
}

$counter_lastthread = file("messages/counters/counter_lastthread", "r");
$counter_lastmessage = file("messages/counters/counter_lastmessage", "r");
$postid = $counter_lastmessage[0] + 1;

if ($postit == "Post Message")							$newthread = $counter_lastthread[0] + 1;
else if ($postit == "Post Reply")						$newthread = $thread;
else if ($preview == "Preview Message" && $replyto=="")	$newthread = $counter_lastthread[0] + 1;
else if ($preview == "Preview Message" && $replyto!="")	$newthread = $thread;

$message_text_for_editing = $message_text;

if ($postit == "Post Message" || $postit == "Post Reply")
{
	if ( !(	$message_title_blank || $messagetext_blank || $passwordfailed || 
			$membernamefailed || $message_text_too_long || $message_title_too_long) )
	{
		// get rid of html baddies
		FixLine($message_title);

		FixParagraph($message_text);
		MakeHTMLSpaces($message_text);

		// CREATE THE POST
		$time_now = time();//date("m-d-Y h:ia");

		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else
			$ip = $_SERVER["REMOTE_ADDR"];

		if ($smilies != "ON") { $smilies = 'OFF'; }
		if ($signature != "ON") { $signature = 'OFF'; }

		// prevent posting at same time
		WaitForWriteToFinish();

		// open forum index
		$threadindex = file("messages/index_".$forum, "r");

		// create tempfile
		$tempname = tempnam("./forum_temp", "temp_index_");
		$fpTemp = fopen($tempname, "w");

		
		if ($postit == "Post Message")
		{
			$replytonum = "";
			$to = "ALL";

			if ($sticky == "ON")
			{
				$sticky = "sticky";

				// write STICKY post index
				fwrite($fpTemp, "$message_title|$login_member_name|$login_member_name|$time_now|1|$newthread|$sticky||\n");
			}

			// separate stickies
			$sticky_threads = array();
			$non_sticky_threads = array();
			foreach ($threadindex as $cur_thread_raw)
			{
				$cur_thread = split('[|]', $cur_thread_raw);
				if (rtrim($cur_thread[6]) == "sticky")
				{
					array_push($sticky_threads, $cur_thread_raw);
				}
				else
				{
					array_push($non_sticky_threads, $cur_thread_raw);
				}
			}

			$threadindex = $non_sticky_threads;

			// write sticky indexes
			for ($i=0; $i<sizeof($sticky_threads); $i++)
			{
				fwrite($fpTemp, rtrim($sticky_threads[$i])."\n");
			}

			// write new post index
			if ($sticky == "" ) fwrite($fpTemp, "$message_title|$login_member_name|$login_member_name|$time_now|1|$newthread|$sticky||\n");
			
			// write remaining indexes
			foreach ($threadindex as $i => $line)
			{
				fwrite($fpTemp, rtrim($line)."\n");
			}
			fclose($fpTemp);

			copy($tempname, "messages/index_".$forum);
			unlink($tempname);

			// create new threadfile
			$fp = fopen("messages/threadfiles/$newthread", "w");

			// write title to message file
			fwrite($fp, "$message_title|$forum|$sticky|\n");
		}
		else if ($postit == "Post Reply")
		{
			// open current thread file and get info
			$threadfile = file("messages/threadfiles/$thread", "r");

			$lastpost = array_pop($threadfile); // get last post
			array_push($threadfile, $lastpost);

			list($original_membername, $original_to, $original_timeposted, $original_messagetext, $original_postid, $original_replytonum, $original_ip, $original_smilies, $original_signature, $original_deleted) = split('[|]', $threadfile[1]);

			list($info_membername, $info_to, $info_timeposted, $info_messagetext, $info_postid, $info_replytonum, $info_ip, $info_smilies, $info_signature, $info_deleted) = split('[|]', $lastpost);

			list($thread_title, $forum, $sticky, $closed) = split('[|]', $threadfile[0]);
			if ($message_title == "") $message_title == $thread_title;

			$numofposts = sizeof($threadfile)-1;
			$threadinfo = "$thread_title|$original_membername|$info_membername|$info_timeposted|$numofposts|$thread|$sticky||\n";
			$numofposts += 1;

			if ($sticky == "sticky")
			{
				$sticky = "sticky";

				// write STICKY post index
				fwrite($fpTemp, "$thread_title|$original_membername|$login_member_name|$time_now|$numofposts|$thread|$sticky||\n");
			}

			// separate stickies
			/*$sticky_threads = array();
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
			}*/
			
			$sticky_threads = array();
			$non_sticky_threads = array();
			foreach ($threadindex as $cur_thread_raw)
			{
				$cur_thread = split('[|]', $cur_thread_raw);
				if (rtrim($cur_thread[6]) == "sticky")
				{
					array_push($sticky_threads, $cur_thread_raw);
				}
				else
				{
					array_push($non_sticky_threads, $cur_thread_raw);
				}
			}

			// write sticky indexes
			for ($i=0; $i<sizeof($sticky_threads); $i++)
			{
				if (rtrim($sticky_threads[$i]) != rtrim($threadinfo))
				{
					fwrite($fpTemp, rtrim($sticky_threads[$i])."\n");
				}
			}

			$threadindex = $non_sticky_threads;

			if ($sticky == "" ) fwrite($fpTemp, "$thread_title|$original_membername|$login_member_name|$time_now|$numofposts|$thread|$sticky||\n");
			foreach ($threadindex as $i => $line)
			{
				if (rtrim($line) != rtrim($threadinfo))
				{
					fwrite($fpTemp, rtrim($line)."\n");
				}
			}
			fclose($fpTemp);

			copy($tempname, "messages/index_".$forum);
			unlink($tempname);

			$replytonum = $info_postid;

			// open existing threadfile
			$fp = fopen("messages/threadfiles/$thread", "a");
		}

		fwrite($fp, "$login_member_name|");
		fwrite($fp, "$to|");
		fwrite($fp, "$time_now|");
		fwrite($fp, "$message_text|");
		fwrite($fp, "$postid|");
		fwrite($fp, "$replytonum|"); // replytonum
		fwrite($fp, "$ip|");
		fwrite($fp, "$smilies|");
		fwrite($fp, "$signature|");
		fwrite($fp, "\n"); // deleted
		fclose($fp);

		if ($postit == "Post Message")
		{
			chmod("messages/threadfiles/$newthread", 0666);
		}

		// update total forum posts counter
		IncrementCounter("messages/counters/counter_totalmessages");
		IncrementCounter("messages/counters/counter_lastmessage");
		if ($postit ==  "Post Message")
		{
			IncrementCounter("messages/counters/counter_".$forum."_totalthreads");
			IncrementCounter("messages/counters/counter_totalthreads");
			IncrementCounter("messages/counters/counter_lastthread");
		}
		IncrementCounter("messages/counters/counter_".$forum."_totalposts");
		
		// save last post info
		$fp = fopen("messages/info_".$forum."_lastpost", "w");
		fwrite($fp, "$time_now|$login_member_name");
		fclose($fp);


		// update user's posts
		$userposts = file("profiles/$login_member_name/posts.dat", "r");
		$userposts[0]++;
		$fp4 = fopen("profiles/$login_member_name/posts.dat", "w");
		fwrite($fp4, $userposts[0]);
		fclose($fp4);

		if ($postit == "Post Reply" && $to != "ALL" && $to != $login_member_name)
		{
			// update replyto's posts
			$userposts = file("profiles/$to/replies.dat", "r");
			$userposts[0]++;
			$fp4 = fopen("profiles/$to/replies.dat", "w");
			fwrite($fp4, $userposts[0]);
			fclose($fp4);
		}

		// UPDATE SEARCH INDEXES
		//$fp = fopen("search/search_index_".$forum, "a");
		//fwrite($fp, "$newthread|$postid|$message_title|$login_member_name|$message_text\n");
		//fclose($fp);

		/*echo "Thread #: $forum2num[$forum].$newthread.1<br>";
		echo "Poster: $cookiemembername<br>";
		echo "To: ALL<br>";
		echo "Title: $message_title<br>";
		echo "Text: $message_text<br>";
		echo "HTML: $HTML<br>";
		echo "Sticky: $persistent<br>";
		echo "Smilies: $smilies<br>";
		echo "Signature: $signature<br>";*/

		// GOTO NEW THREAD
		if ($postit == "Post Message")
		{
			header("Cache-Control: must-revalidate");  
			header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$newthread&new=1#$postid");
		}
		else if ($postit == "Post Reply")
		{
			$threadfile = file("messages/threadfiles/$thread", "r");
			$threadsize = sizeof($threadfile)-1;

			$threadpages = (int)($threadsize / $postsperpage);
			$threadpages += ( ($threadsize % $postsperpage) != 0 ? 1 : 0);

			if ($threadpages > 1) $pagelinktext = "&page=$threadpages";
			else $pagelinktext = "";

			header("Cache-Control: must-revalidate");  
			header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."viewthread.php?thread=$newthread".$pagelinktext."&new=$threadsize#$postid");
		}
		exit();
	}
}

$subpagetitle = "Post a Message";
include("./headerinternal.php");

// DO PREVIEW
if ($preview == "Preview Message")
{
	if ( !(	$message_title_blank || $messagetext_blank || $passwordfailed || 
			$membernamefailed || $message_text_too_long || $message_title_too_long) )
	{
		$message_text_display = $message_text;

		if ($replyto != "")
		{
			// open current thread file and get info
			$threadfile = file("messages/threadfiles/$thread", "r");

			$lastpost = array_pop($threadfile); // get last post
			array_push($threadfile, $lastpost);

			list($original_membername, $original_to, $original_timeposted, $original_messagetext, $original_postid, $original_replytonum, $original_ip, $original_smilies, $original_signature, $original_deleted) = split('[|]', $threadfile[1]);

			list($info_membername, $info_to, $info_timeposted, $info_messagetext, $info_postid, $info_replytonum, $info_ip, $info_smilies, $info_signature, $info_deleted) = split('[|]', $lastpost);
		}

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
		$loaded_profile = LoadProfile($cookiemembername);

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
			$rank = DetermineRank($cookiemembername, $loaded_profile["posts"], $loaded_profile["specialrank"]);
		//}

		$time_now = time();
		
		?>

	<? TableHeader("$id_to_forum[$forum] >> $message_title [ PREVIEW ]") ?>
	
		<table border=0 cellspacing=0 cellpadding=0 width=100%>
		<td class=tableborder>
			<table border=0 cellspacing=1 cellpadding=4 width=100%>
			<tr><td width="150" class=titlecell valign="top" align="left" nowrap><font face="verdana" size=2><b>Member Details:</b></font></td>
				<td class=titlecell valign="top" align="left" colspan=4><font face="verdana" size=2><b>Messages:</b></font></td></tr>

			<tr><td width="150" class=tablecell1 valign="top" align="left" rowspan=2 nowrap><font face=verdana size=2><a name="6.50.1"></a>
				<a href="viewprofile.php?member=<? echo $cookiemembername ?>"><b><? echo $cookiemembername ?></b></a> <?

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
				<td class=tablecell2 nowrap><font face=verdana size=2><font size=1>To: </font><b>ALL</b></font></td>
				<td class=tablecell2 nowrap><font face="verdana" size="1"><? echo "#$postid" ?><?
					if ($replyto != "")
					{
						echo " in reply to #".$info_postid;
					}?></font></td>
				<td class=tablecell2 align="center" nowrap><font face="verdana" size="1"><? ShowDate($time_now) ?></font></td>
				<td class=tablecell2 align="center" nowrap><font face="verdana" size="1">[1 of 1]</font></td></tr>
				<tr><td class=tablecell1 valign="top" align="right" colspan=4>
					<table border="0" width="100%" cellspacing="0" cellpadding="10">
					<tr><td width="100%" class=tablecell1><font face="verdana" size=2><? echo $message_text_display ?></font></td></tr>
					</table><img src=spacer.gif height=25></td></tr></table>
		</td></table>
		<? TableFooter() ?><table cellspacing=0 cellpadding=4 border=0 width=100%><tr><td align=right><font face=verdana size=1 class=lightcolor>All times GMT 
<? if (isset($my_profile["timezone"])) echo $my_profile["timezone"];
else echo $servertimezone; ?> 
Hours</font></td></tr></table><p>
		<?
	} 
}

// DISPLAY FORM

$dialogue_title = "Post a New Message";
$thread_title = "";
if ($replyto > 0)
{
	$replyto_threadfile = file("messages/threadfiles/$thread", "r");

	list($thread_title, $forum, $sticky, $closed) = split('[|]', $replyto_threadfile[0]);
	$thread_title .= " >> ";
	$dialogue_title = "Post a Reply";

	if (rtrim($closed) == "closed")
	{
		ForumMessage("<b>This thread is closed.</b><p>No new replies can be added to this thread.");
		
		include("./skins/$forum_skin/forumfooter.php");
		exit();

	}
}

FixEditingText($message_text_for_editing);

?>
<? TableHeader($id_to_forum[$forum]." >> ".$thread_title.$dialogue_title) ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%><form action="postmessage.php" method="post">
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><? if ($membernamefailed) { echo "<font class=error>"; } ?><b>Member Name:</b></td>
			<td class=tablecell1 valign=top width=75%>
			<? if (!AuthCookie())
				  {
					 ?><input type=text size=40 name="login_member_name" value="" class=forminput tabindex=1>&nbsp&nbsp<font face=verdana size=1>You must be <a href="register.php">registered</a>.<?
				  }
				  else
				  {
					$member_name_display = $HTTP_COOKIE_VARS["mb4php_member_name"];
					RePipe($member_name_display);

					?><table cellspacing=0 cellpadding=2 border=0><td class=tablecell1><font face=verdana size=2><b><? echo $member_name_display?></td></table>
					<input type=hidden size=40 name="login_member_name" value="<? echo $HTTP_COOKIE_VARS["mb4php_member_name"]?>" tabindex=1><?
				  }
				  ?></b></td></tr>
		<? if (!AuthCookie())
		   { ?>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><? if ($passwordfailed) { echo "<font class=error>"; } ?><b>Password:</td>
			<td class=tablecell1 valign=top width=65%><input type=password size=40 name="login_password" value="" class=forminput tabindex=2>&nbsp&nbsp<font face=verdana size=1>Case-sensitive.</td></tr>
		<? } ?>
		
<? if ($replyto > 0)
{
	list($replyto_membername, $replyto_to, $replyto_timeposted, $replyto_messagetext, $replyto_postid, $replyto_replytonum, $replyto_ip, $replyto_smilies, $replyto_signature, $replyto_deleted) = split('[|]', $replyto_threadfile[$replyto]);

	// do quoted text
	if ($withquote == 1)
	{
		$quotetext = split('<br>', $replyto_messagetext);

		if ($message_text_for_editing == "")
		{
			$message_text_for_editing = "\n\n\n;On ".date("m-d-Y h:ia", $replyto_timeposted).", ".$replyto_membername." wrote:\n";

			for ($i=0; $i<sizeof($quotetext); $i++)
			{
				$message_text_for_editing .= ";".$quotetext[$i];
				if ($i != sizeof($quotetext)-1) { $message_text_for_editing .= "\n"; }
			}
		}
	}

				?>
				<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>To:</b></td>
				<td class=tablecell1 valign=top width=75%>
				<select name="to" class=forminput tabindex=3>
				<option selected value="<? echo $replyto_membername ?>"><? echo $replyto_membername ?></option>
				<option value="ALL">ALL</option>
				</select>
				</td></tr>
<?
}
else
{ ?>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><?
				  if ($message_title_blank)
				  {
					?><font class=error>You must enter a title!</font><?
				  }
				  else if ($message_title_too_long)
				  {
					?><font class=error>Maximum length is 60 characters!</font><?
				  }
				  else
				  {
					?><b>Message Title:</b></td><?
				  } ?>
			<td class=tablecell1 valign=top width=75%><input type=text size=65 name="message_title" value="<? echo $message_title ?>" class=forminput tabindex=4></td></tr>
<? } ?>
		<tr><td class=tablecell1 align=right valign=top><font face=verdana size=2><?
					if ($messagetext_blank)
					{
						?><font class=error>You must enter some text!</font><?
					}
					else
					{
						?><b>Message Text:</b><?
					}
			?><font size=1><p>Enter your message here.<p>If you wish to quote a line of text, simply put a semicolon ";" in front of the line.<p>For a list of smilies, <a href="faq.php">click here</a>.<?
			if ($message_text_too_long)
			{
				?><font class=error>Maximum length is 20000 characters!</font><?
			}
			?></td>
			<td class=tablecell1 valign=top width=75%><textarea cols=50 rows=15 wrap="VIRTUAL" name="message_text" class=forminput tabindex=5><? echo $message_text_for_editing ?></textarea></td></tr>
		<tr><td class=tablecell1 align=right valign=top nowrap><font face=verdana size=2><b>Post Options:</b></td>
			<td class=tablecell1 valign=top width=75%><font face="arial" size=2>
			<?
			if (AuthAdminCookie() && $replyto == "")
			{ ?>
			<input type="checkbox" name="sticky" value="ON" tabindex=6 <? if ($sticky == "ON") { ?>checked<? } ?>>[<font class=error>ADMIN</font>] Sticky Thread (stays on top always).<br>
			<? } ?>
			<input type="checkbox" name="smilies" value="ON" checked tabindex=7>Check here to enable smilie icons.<br>
			<input type="checkbox" name="signature" value="ON" checked tabindex=8>Check here to include your signature in this post.
			</td></tr>
			<input type="hidden" name="forum" value="<? echo $forum ?>">
			<? if ($replyto > 0) { ?>
			<input type="hidden" name="replyto" value="<? echo $replyto ?>">
			<input type="hidden" name="thread" value="<? echo $thread ?>">
			<? } ?>
		<tr><td class=tablecell1 align=center valign=top colspan=2 width=100%><input type=submit name=postit value="<? if ($replyto > 0) echo "Post Reply"; else echo "Post Message"; ?>" class=formbutton tabindex=9> <input type=submit name=preview value="Preview Message" class=formbutton tabindex=10> <input type=reset value="Clear Form" class=formbutton  tabindex=11></td></tr></form>
		</table>
		</td></table>
		<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>