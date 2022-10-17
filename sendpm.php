<? 
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$message_text_for_editing = "";

$folder = "Inbox";
$to = "";
$replyto = "";
$withquote = "";
$subject = "";
$submit = "";
$message_text = "";
$smilies = "";
$signature = "";

if (isset($_GET["folder"])) $folder = $_GET["folder"];
if (isset($_GET["to"]))  $to = $_GET["to"];
if (isset($_GET["replyto"]))  $replyto = $_GET["replyto"];
if (isset($_GET["withquote"]))  $withquote = $_GET["withquote"];
if (isset($_POST["to"])) $to = $_POST["to"];
if (isset($_POST["subject"])) $subject = $_POST["subject"];
if (isset($_POST["message_text"]))  $message_text  = $_POST["message_text"];
if (isset($_POST["submit"]))  $submit  = $_POST["submit"];
if (isset($_POST["smilies"]))  $smilies  = $_POST["smilies"];
if (isset($_POST["signature"]))  $signature  = $_POST["signature"];

$to_blank = 0;
$to_found = 0;
$subject_blank = 0;
$messagetext_blank   = 0;
$message_text_too_long = 0;
$subject_too_long = 0;


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

if ($submit == "Send Message")
{
	if ($to == '') $to_blank = 1;
	else
	{
		// check if membername is taken and email taken
		$handle = opendir("profiles/");
		while (false !== ($file = readdir($handle)))
		{ 
			if ($file != "." && $file != ".." && $file != '.htaccess' && $file != '.htpasswd' && 
				$file != 'counter_totalmembers' && $file != 'info_newestmember')
			{
				if (strtolower($file) == strtolower($to)) { $to_found = 1; }
			}
		}
	}

	if (strlen($message_text) > 20000)
	{
		$message_text_too_long = 1;
	}

	if (strlen($subject) > 60)
	{
		$subject_too_long = 1;
	}
	else
	{
		// parse baddies
		$subject = preg_replace('/\s+/', ' ', $subject);

		if ($subject == '' || $subject == ' ') { $subject_blank = 1; }

		if ($message_text == '') { $messagetext_blank = 1; }
	}
}

if ($submit == "Send Message")
{
	if (!($subject_blank || $messagetext_blank || !$to_found || $message_text_too_long || $subject_too_long))
	{
		// prevent accessing files at same time
		WaitForWriteToFinish();
		
		// get rid of html baddies
		FixLine($subject);

		FixParagraph($message_text);
		MakeHTMLSpaces($message_text);
		
		//echo $to, "<br>\n";
		//echo $subject, "<br>\n";
		//echo $message_text, "<br>\n";

		if ($smilies != "ON") { $smilies = 'OFF'; }
		if ($signature != "ON") { $signature = 'OFF'; }

		$to = str_replace("|","&pipe;",$to);

		$time_now = time();

		$last_pm = file("counter_lastpm", "r");

		$new_pm_id = $last_pm[0] + 1;

		$fp = fopen("profiles/$to/inbox.dat", "a");
		fwrite($fp, "$cookiemembername|$subject|$time_now|$message_text|$new_pm_id|$smilies|$signature|unread\n");
		fclose($fp);

		$fp = fopen("profiles/$cookiemembername/sentbox.dat", "a");
		fwrite($fp, "$to|$subject|$time_now|$message_text|$new_pm_id|$smilies|$signature|read\n");
		fclose($fp);

		IncrementCounter("counter_lastpm");
		IncrementCounter("profiles/$to/counter_unread_pm");

		$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."pm.php\">";
		include("./headerinternal.php"); 
		
		
		ForumMessage("<b>Private message sent.</b><p>If your browser does not automatically return to your $folder, <a href=pm.php>click here</a>.");

		exit();
	}
}

$subpagetitle = "Send Private Message";
include("./headerinternal.php"); ?>

<? TableHeader("Send Private Message");

$message_text_for_editing = $message_text;

if ($replyto != "")
{
	$boxlist = file("profiles/$cookiemembername/$folderfile", "r");

	list($replyto_sender, $replyto_subject, $replyto_received, $replyto_message, $replyto_pm_id, $replyto_smilies, $replyto_signature) = split('[|]', $boxlist[$replyto]);

	$to = $replyto_sender;

	if (substr($replyto_subject, 0, 4) != "RE: ") $subject = "RE: $replyto_subject";
	else $subject = $replyto_subject;
}
?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%>
		<form action="sendpm.php" method=post>
		<tr><td class=tablecell1 align=right nowrap><?
					  if ($to_blank)
					  {
						?><font class=error>You must enter a recipient!</font><?
					  }
					  else if (!$to_found && $submit == "Send Message")
					  {
						?><font class=error>Member not found!</font><?
					  }
					  else
					  {
						?><b>Send To:</b></td><?
					  } ?></td>
			<td class=tablecell1 width=75%><input type=text size=40 name="to" value="<? echo $to ?>" class=forminput tabindex=1></td></tr>
		<tr><td class=tablecell1 align=right nowrap><?
					  if ($subject_blank)
					  {
						?><font class=error>You must enter a subject!</font><?
					  }
					  else if ($subject_too_long)
					  {
						?><font class=error>Maximum length is 60 characters!</font><?
					  }
					  else
					  {
						?><b>Subject:</b></td><?
					  } ?></td>
			<td class=tablecell1 width=75%><input type=text size=40 name="subject" value="<? echo $subject ?>" class=forminput tabindex=2></td></tr>
<?
	// do quoted text
	if ($withquote == 1)
	{
		$quotetext = split('<br>', $replyto_message);

		if ($message_text_for_editing == "")
		{
			$message_text_for_editing = "\n\n\n;On ".$replyto_received.", ".$replyto_sender." wrote:\n";

			for ($i=0; $i<sizeof($quotetext); $i++)
			{
				$message_text_for_editing .= ";".$quotetext[$i]."\n";
			}
		}
	}
?>
		<tr><td class=tablecell1 align=right valign=top><?php
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
			<td class=tablecell1 valign=top width=75%><textarea cols=50 rows=15 wrap="VIRTUAL" name="message_text" class=forminput tabindex=3><?php echo $message_text_for_editing ?></textarea></td></tr>
		<tr><td class=tablecell1 align=right valign=top nowrap><font face=verdana size=2><b>Message Options:</b></td>
			<td class=tablecell1 valign=top width=75%><font face="arial" size=2>
			<input type="checkbox" name="smilies" value="ON" checked tabindex=4>Check here to enable smilie icons.<br>
			<input type="checkbox" name="signature" value="ON" checked tabindex=5>Check here to include your signature in this post.
		<tr><td class=tablecell1 align=center colspan=2><input type=submit name=submit value="Send Message" class=formbutton> <input type=reset name=reset class=formbutton></td></tr>
		</form>
		</table>
	</td></table>
<? TableFooter() ?><?php include("./skins/$forum_skin/forumfooter.php"); ?>