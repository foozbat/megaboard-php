<?php // support functions

/////// profile file format ////////
// email
// icq
// encrypted_pass
// moderator
// homepage
// location
// signature
// publicemail
// user_maxviewthreads
// user_postsperpage
// quickforum
////////////////////////////////////

// log start time
$time_start = getmicrotime();

//ob_start("ob_gzhandler");

$active_users = file("list_activeusers");

$logout              = 0;
$cookiemembername    = "";
$cookiepassencrypted = "";

$num_unread_pm = 0;

if (isset($_GET["logout"])) $logout = $_GET["logout"];
if (isset($HTTP_COOKIE_VARS["mb4php_member_name"]))    $cookiemembername    = $HTTP_COOKIE_VARS["mb4php_member_name"];
if (isset($HTTP_COOKIE_VARS["mb4php_pass_encrypted"])) $cookiepassencrypted = $HTTP_COOKIE_VARS["mb4php_pass_encrypted"];

if ( file_exists("admin/forumclosed") && !AuthAdminCookie() )
{
	ShowClosedForumMessage();
}

$maxviewthreads = 20;
$postsperpage = 20;

if ($logout == 1) // kill cookie
{
	setcookie ('mb4php_member_name',    ' ', time()-5184000, $forumpath, $forumdomain);
	setcookie ('mb4php_pass_encrypted', ' ', time()-5184000, $forumpath, $forumdomain);

	$no_support = 1;
	$no_settings = 1;
	$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."\">";
	include("./headerinternal.php"); 
		
	ForumMessage("<b>You have logged out.</b><p>If your browser does not automatically return to the main forum index, <a href=\"./\">click here</a>.");

	include("./skins/$forum_skin/forumfooter.php");
	exit();

}
else if ( AuthCookie() ) // refresh cookie
{
	setcookie ("mb4php_member_name",    $cookiemembername, time()+5184000, $forumpath, $forumdomain);
	setcookie ("mb4php_pass_encrypted", $cookiepassencrypted, time()+5184000, $forumpath, $forumdomain);
}

if ( AuthCookie() ) // load current user's profile
{	
	$temp_profile = file("profiles/$cookiemembername/profile.dat");
	$temp_posts   = file("profiles/$cookiemembername/posts.dat");
	$temp_replies = file("profiles/$cookiemembername/replies.dat");

	$my_profile["email"]          =  rtrim($temp_profile[0]);
	$my_profile["encrypted_pass"] =  rtrim($temp_profile[1]);
	$my_profile["specialrank"]      =  rtrim($temp_profile[2]);
	$my_profile["homepage"]       =  rtrim($temp_profile[3]);
	$my_profile["location"]       =  rtrim($temp_profile[4]);
	$my_profile["signature"]      =  rtrim($temp_profile[5]);
	$my_profile["publicemail"]    =  rtrim($temp_profile[6]);
	$my_profile["maxviewthreads"] =  rtrim($temp_profile[7]);
	$my_profile["postsperpage"]   =  rtrim($temp_profile[8]);
	$my_profile["icq"]            =  rtrim($temp_profile[9]);
	$my_profile["aim"]            =  rtrim($temp_profile[10]);
	$my_profile["yahoo"]          =  rtrim($temp_profile[11]);
	$my_profile["msn"]            =  rtrim($temp_profile[12]);
	$my_profile["timeregistered"] =  rtrim($temp_profile[13]);
	$my_profile["timezone"]       =  rtrim($temp_profile[14]);
	$my_profile["quickforum"]     =  rtrim($temp_profile[15]);
	$my_profile["posts"]          =  rtrim($temp_posts[0]);
	$my_profile["replies"]        =  rtrim($temp_replies[0]);

	if ($my_profile["maxviewthreads"] != 0 && $my_profile["postsperpage"] != 0)
	{
		$maxviewthreads = $my_profile["maxviewthreads"];
		$postsperpage   = $my_profile["postsperpage"];
	}
	
	// check for new private messages
	$unread_pm_file = file("profiles/$cookiemembername/counter_unread_pm");

	$num_unread_pm = $unread_pm_file[0];
}

// update active user list
UpdateActiveUsers();


// SUPPORT FUNCTIONS
//////////////////////////////////


function LoadProfile($membername)
{
	global $DOCUMENT_ROOT;

	if (file_exists("profiles/$membername/profile.dat"))
	{
		$temp_profile = file("profiles/$membername/profile.dat");
		$temp_posts   = file("profiles/$membername/posts.dat");
		$temp_replies = file("profiles/$membername/replies.dat");

		$loaded_profile["email"]          =  rtrim($temp_profile[0]);
		$loaded_profile["encrypted_pass"] =  rtrim($temp_profile[1]);
		$loaded_profile["specialrank"]    =  rtrim($temp_profile[2]);
		$loaded_profile["homepage"]       =  rtrim($temp_profile[3]);
		$loaded_profile["location"]       =  rtrim($temp_profile[4]);
		$loaded_profile["signature"]      =  rtrim($temp_profile[5]);
		$loaded_profile["publicemail"]    =  rtrim($temp_profile[6]);
		$loaded_profile["maxviewthreads"] =  rtrim($temp_profile[7]);
		$loaded_profile["postsperpage"]   =  rtrim($temp_profile[8]);
		$loaded_profile["icq"]            =  rtrim($temp_profile[9]);
		$loaded_profile["aim"]            =  rtrim($temp_profile[10]);
		$loaded_profile["yahoo"]          =  rtrim($temp_profile[11]);
		$loaded_profile["msn"]            =  rtrim($temp_profile[12]);
		$loaded_profile["timeregistered"] =  rtrim($temp_profile[13]);
		$loaded_profile["timezone"]       =  rtrim($temp_profile[14]);
		$loaded_profile["quickforum"]     =  rtrim($temp_profile[15]);
		$loaded_profile["posts"]          =  rtrim($temp_posts[0]);
		$loaded_profile["replies"]        =  rtrim($temp_replies[0]);

		return $loaded_profile;
	}
	else
	{
		return "error";
	}
}

function AccessLevel($name)
{
	global $administrators;
	global $moderators;

	for ($i=0; $i< count($administrators); $i++)
	{
		if ($administrators[$i] == $name)
		{
			return "administrator";
		}
	}

	for ($i=0; $i<count($moderators); $i++)
	{
		if ($moderators[$i] == $name)
		{
			return "moderator";
		}
	}
	
	return "user";
}

function AuthCookie()
{
	global $cookiemembername;
	global $cookiepassencrypted;

	if (file_exists("profiles/$cookiemembername/profile.dat"))
	{
		$profile = file("profiles/$cookiemembername/profile.dat");
		$stored_pass = chop($profile[1]);

		if ($cookiepassencrypted == $stored_pass)
		{
			return true;
		}
	}

	return false;
}

function AuthAdminCookie()
{
	global $administrators;

	global $cookiemembername;
	global $cookiepassencrypted;

	if (file_exists("profiles/$cookiemembername/profile.dat"))
	{
		$profile = file("profiles/$cookiemembername/profile.dat");
		$stored_pass = rtrim($profile[1]);

		for ($i=0; $i< count($administrators); $i++)
		{
			if ($administrators[$i] == $cookiemembername && $cookiepassencrypted == $stored_pass)
			{
				return true;
			}
		}
	}
	
	return false;
}

function AuthModeratorCookie()
{
	global $moderators;

	global $cookiemembername;
	global $cookiepassencrypted;

	if (file_exists("profiles/$cookiemembername/profile.dat"))
	{
		$profile = file("profiles/$cookiemembername/profile.dat");
		$stored_pass = rtrim($profile[1]);

		for ($i=0; $i< count($moderators); $i++)
		{
			if ($moderators[$i] == $cookiemembername && $cookiepassencrypted == $stored_pass)
			{
				return true;
			}
		}
	}
	
	if (AuthAdminCookie()) return true;

	return false;
}

function RePipe(&$text)
{
	$text = str_replace("&pipe;", "|", $text);
}

function FixParagraph(&$msgtext)
{
	// kill html
	$msgtext = str_replace("<", "&lt;", $msgtext);
	$msgtext = str_replace(">", "&gt;", $msgtext);

	// kill bad chars
	$msgtext = str_replace("\n","<br>",$msgtext);
	$msgtext = str_replace("\r","",$msgtext);
	$msgtext = str_replace("\'","'",$msgtext);
	$msgtext = str_replace("\\\"","&quot;",$msgtext);
	$msgtext = str_replace("|","&pipe;",$msgtext);
	$msgtext = str_replace("\\\\","\\",$msgtext);
}

function FixLine(&$msgtext)
{
	// kill html
	$msgtext = str_replace("<", "&lt;", $msgtext);
	$msgtext = str_replace(">", "&gt;", $msgtext);

	// kill bad chars
	$msgtext = str_replace("\n","",$msgtext);
	$msgtext = str_replace("\r","",$msgtext);
	$msgtext = str_replace("\'","'",$msgtext);
	$msgtext = str_replace("\\\"","&quot;",$msgtext);
	$msgtext = str_replace("|","&pipe;",$msgtext);
}

function FixEditingText(&$msgtext)
{
	$msgtext = str_replace("\'","'",$msgtext);
	$msgtext = str_replace("\\\"","&quot;",$msgtext);
	$msgtext = str_replace("\\\\","\\",$msgtext);
}

function MakeHTMLSpaces(&$msgtext)
{
	$msgtext = str_replace("  ", " &nbsp;", $msgtext);
	$msgtext = str_replace("\t", " &nbsp; &nbsp; ", $msgtext);
}

// PHP.net
// http://www.php.net/manual/en/function.wordwrap.php
function wordwrap2($str, $cols, $cut)
{
	$wordlen = 0;
	$result = "";

   $len = strlen($str);
   $tag = 0;
   for ($i = 0; $i < $len; $i++) {
       $chr = $str[$i];
       if ($chr == '<') {
          $tag++;
       } elseif ($chr == '>') {
          $tag--;
       } elseif ((!$tag) && (ctype_space($chr))) {
          $wordlen = 0;
       } elseif (!$tag) {
          $wordlen++;
       }

       if ((!$tag) && ($wordlen) && (!($wordlen % $cols))) {
           $chr .= $cut;
      }

       $result .= $chr;
   }

   return $result;
}

function MBCode2HTML(&$msgtext)
{
	// BOLD
	$msgtext = preg_replace("/(\[b\])/i", "<b>", $msgtext);
	$msgtext = preg_replace("/(\[\/b\])/i", "</b>", $msgtext);
	// ITALICS
	$msgtext = preg_replace("/(\[i\])/i", "<i>", $msgtext);
	$msgtext = preg_replace("/(\[\/i\])/i", "</i>", $msgtext);

	// UNDERLINE
	$msgtext = preg_replace("/(\[u\])/i", "<u>", $msgtext);
	$msgtext = preg_replace("/(\[\/u\])/i", "</u>", $msgtext);

	// LIST
	$msgtext = preg_replace("/(\[list\])/i", "<ul>", $msgtext);
	$msgtext = preg_replace("/(\[\/list\])/i", "</ul>", $msgtext);

	// LI
	$msgtext = preg_replace("/(\[\*\])/i", "<li>", $msgtext);

	// IMAGE SRC
    $msgtext = preg_replace("/\[img\](mailto:)?(\S+?)(\.jpe?g|\.gif|\.png)\[\/img\]/si", "<img src=\"\\2\\3\" border=0 alt=\"\\1\\2\\3\">", $msgtext);

	// FONT
	$msgtext = preg_replace("/\[font=(.*?)\](.*?)\[\/font\]/si", "<font face=\"\\1\">\\2</font>", $msgtext);

	// FONT SIZE
	$msgtext = preg_replace("/\[size=([1-7])\](.*?)\[\/size\]/si", "<font size=\"\\1\">\\2</font>", $msgtext);

	// FONT COLOR
	$msgtext = preg_replace("/\[color=(\S+?)\](.*?)\[\/color\]/si", "<font color=\"\\1\">\\2</font>", $msgtext);

	// HYPERLINK
	$msgtext = preg_replace("/\[url\](http|https|ftp)(:\/\/\S+?)\[\/url\]/si", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</A>", $msgtext);
    $msgtext = preg_replace("/\[url\](\S+?)\[\/url\]/si", "<a href=\"http://\\1\" target=\"_blank\">\\1</A>", $msgtext);
    $msgtext = preg_replace("/\[url=(http|https|ftp)(:\/\/\S+?)\](.*?)\[\/url\]/si", "<a href=\"\\1\\2\" target=\"_blank\">\\3</A>", $msgtext);
    $msgtext = preg_replace("/\[url=(\S+?)\](\S+?)\[\/url\]/si", "<a href=\"http://\\1\" target=\"_blank\">\\2</A>", $msgtext);

	// EMAIL LINK
    $msgtext = preg_replace("/\[email\](\S+?@\S+?\\.\S+?)\[\/email\]/si", "<a href=\"mailto:\\1\">\\1</A>", $msgtext);
    $msgtext = preg_replace("/\[email=(\S+?@\S+?\\.\S+?)\](.*?)\[\/email\]/si", "<a href=\"mailto:\\1\">\\2</A>", $msgtext);
}

function MakeSmilies(&$msgtext)
{
	global $forum_skin;

	$msgtext = str_replace("&gt;:(",	"<img src=skins/$forum_skin/images/emoticons/angry.gif border=0 height=15 width=15>",		$msgtext);
	$msgtext = str_replace(":(",		"<img src=skins/$forum_skin/images/emoticons/frown.gif border=0 height=15 width=15>",		$msgtext);
	$msgtext = str_replace("&gt;:O",	"<img src=skins/$forum_skin/images/emoticons/pissed.gif border=0 height=15 width=15>",		$msgtext);
	$msgtext = str_replace(":)",		"<img src=skins/$forum_skin/images/emoticons/smile.gif border=0 height=15 width=15>",		$msgtext);
	$msgtext = str_replace(":D",		"<img src=skins/$forum_skin/images/emoticons/bigsmile.gif border=0 height=15 width=15>",	$msgtext);
	$msgtext = str_replace(";)",		"<img src=skins/$forum_skin/images/emoticons/wink.gif border=0 height=15 width=15>",		$msgtext);
	$msgtext = str_replace("B)",		"<img src=skins/$forum_skin/images/emoticons/sunglasses.gif border=0 height=15 width=15>",	$msgtext);
	$msgtext = str_replace("8)",		"<img src=skins/$forum_skin/images/emoticons/glasses.gif border=0 height=15 width=15>",	$msgtext);
	$msgtext = str_replace("( 8'(|)",	"<img src=skins/$forum_skin/images/emoticons/homer.gif border=0 height=15 width=15>",		$msgtext);
}

function DetermineRank($name, $numofposts, $special)
{
	global $ranklevels;
	global $ranks;
	global $specialranks;

	$rank = $ranks[0];

	for ($i=1; $i<sizeof($ranklevels); $i++)
	{
		if ($numofposts >= $ranklevels[$i]) { $rank = $ranks[$i]; }
	}

	if ($special != "")
	{
		$rank = $special;
		if (AccessLevel($name) == "administrator") $rank = "<font class=specialrank>".$rank."</font>";
	}
	else if (AccessLevel($name) == "administrator")
	{
		$rank = "<font class=specialrank>".$specialranks[AccessLevel($name)]."</font>";
	}
	else if (AccessLevel($name) == "moderator")
	{
		$rank = $specialranks[AccessLevel($name)];
	}

	return $rank;
}

function UpdateActiveUsers()
{
	// username|time

	global $active_users;
	global $cookiemembername;
	
	$time_now = time();//date("m-d-Y h:ia");

	$tempname = tempnam("./forum_temp", "temp_list_");
	$fp = fopen($tempname, "w");
	
	if (AuthCookie()) fwrite($fp, "$cookiemembername|$time_now\n");

	for ($i=0; $i<sizeof($active_users); $i++)
	{
		list($cur_user, $cur_user_time) = split('[|]', $active_users[$i]);
		if ($time_now-$cur_user_time < 900 && $cur_user != $cookiemembername) // expiration time 15 minutes
		{
			fwrite($fp, rtrim($active_users[$i])."\n");
		}
	}

	fclose($fp);

	copy($tempname, "list_activeusers");
	unlink($tempname);

	$active_users = file("list_activeusers");
}

function IncrementCounter($file)
{
	$counter = file($file);
	$counter[0]++;

	$tempname = tempnam("./forum_temp", "temp_count_");
	$fp = fopen($tempname, "w");
	fwrite($fp, $counter[0]); // write to temp file to prevent counter destruction
	fclose($fp);

	copy($tempname, $file);
	unlink($tempname);
}

function DecrementCounter($file)
{
	$counter = file($file);
	$counter[0]--;

	$tempname = tempnam("./forum_temp", "temp_count_");
	$fp = fopen($tempname, "w");
	fwrite($fp, $counter[0]); // write to temp file to prevent counter destruction
	fclose($fp);

	copy($tempname, $file);
	unlink($tempname);
}

/*function AppendToFile($file)
{
	$fp = fopen($file, "a");
	flock*/

function getmicrotime()
{ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
}

function ShowProcessingTime()
{
	global $time_start;

	$time_end = getmicrotime();
	$processing_time = $time_end - $time_start;

	?>
	<font face=arial size=1 color=gray><br>
	<center>
	Page processed in <? echo $processing_time ?> seconds. <?
}

function array_insert(&$array, $value, $pos)
{
	if (!is_array($array)) 
		return FALSE;

	$last = array_splice($array, $pos);

	array_push($array, $value);
	$array = array_merge($array, $last);
}

// PHP.net
// http://www.php.net/manual/en/function.natcasesort.php
function ignorecasesort(&$array) {
$separator="|<>|";
for($i=0;$i<sizeof($array);$i++) { $array[$i]=strtolower($array[$i]).$separator.$array[$i]; }
sort($array);
for($i=0;$i<sizeof($array);$i++) { $temp=$array[$i]; $temp=explode($separator,$temp); $array[$i]=$temp[1]; }

 /* short before longer (e.g. 'abc' should come before 'abcd') */
 for($i=count($array)-1;$i>0;$i--) {
   $str_a = $array[$i  ];
   $str_b = $array[$i-1];
  $cmp_a = strtolower(substr($str_a,0,strlen($str_a)));
   $cmp_b = strtolower(substr($str_b,0,strlen($str_a)));
   if ($cmp_a==$cmp_b && strlen($str_a)<strlen($str_b)) {
     $array[$i]=$str_b; $array[$i-1]=$str_a; $i+=2;
   }
 }
}

function WaitForWriteToFinish()
{
	while(TRUE)
	{
		$time_lastpost = file("messages/time_lastwrite");
		if (time()-$time_lastpost[0] >= 1)
		{
			// START NEW WRITE
			$tempname = tempnam("./forum_temp", "temp_time_");
			$fp = fopen($tempname, "w");
			fwrite($fp, time()); // write to temp file to prevent counter destruction
			fclose($fp);

			copy($tempname,"messages/time_lastwrite");
			unlink($tempname);
			
			break;
		}
		sleep(1);
	}
}

function ShowClosedForumMessage()
{
	$no_support=1;
	include ("headerinternal.php");

	ForumMessage("<h2>The forum is closed temporarily</h2>Please try again later.  We are sorry for the inconvience.");

	include("skins/$forum_skin/forumfooter.php");

	exit();
}

function ForumMessage($message)
{
	TableHeader("Forum Message") ?>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
	<td class=tableborder>

		<table cellspacing=1 cellpadding=8 border=0 width=100%>
		<tr><td class=tablecell1 width=100% valign=top align=center>
		
		<? echo $message ?>
		
		</td></tr></table>
	</td></table><? TableFooter();
}

function ShowDate($time)
{
	global $servertimezone;
	global $my_profile;
	
	$time = TimeZone($time);

	$date = date("m-d-Y h:ia", $time);
	
	$date_array = split(' ', $date);
	if ($date_array[0] == date("m-d-Y", TimeZone(time())) )
	{
		echo "Today @ $date_array[1]";
	}
	else
	{
		echo $date;
	}
}

function TimeZone($time)
{
	global $my_profile;
	global $servertimezone;

	if (isset($my_profile["timezone"]))
	{
		$time -= $servertimezone*60*60;
		$time += $my_profile["timezone"]*60*60;
	}

	return $time;
}

function TimeStampNow()
{
	global $servertimezone;
	global $my_profile;

	?><table cellspacing=0 cellpadding=4 border=0 width=100%><tr><td align=right><font face=verdana size=1 class=lightcolor>All times GMT 
	<? if (isset($my_profile["timezone"])) echo $my_profile["timezone"];
	else echo $servertimezone; ?> 
	Hours. Time now: <? echo date("h:ia", TimeZone(time())) ?></font></td></tr></table><?
}

?>