<?
include("./settings.inc");
include("./support.inc");
$no_settings = 1;
$no_support = 1;

if (!AuthCookie())
{
	header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath."login.php");
	exit();
}

// form variables
$submit = "";
$email = "";
$publicemail = "";
$current_password = "";
$password = "";
$passwordcheck = "";
$icq = "";
$aim = "";
$yahoo = "";
$msn = "";
$homepage = "";
$location = "";
$signature = "";
$user_maxviewthreads = "";
$user_postsperpage = "";
$custom_title = "";
$timezoneoffset = "";

if (isset($_POST["submit"]))              $submit              = $_POST["submit"];
if (isset($_POST["email"]))               $email               = $_POST["email"];
if (isset($_POST["publicemail"]))         $publicemail         = $_POST["publicemail"];
if (isset($_POST["current_password"]))    $current_password    = $_POST["current_password"];
if (isset($_POST["password"]))            $password            = $_POST["password"];
if (isset($_POST["passwordcheck"]))       $passwordcheck       = $_POST["passwordcheck"];
if (isset($_POST["icq"]))                 $icq                 = $_POST["icq"];
if (isset($_POST["aim"]))                 $aim                 = $_POST["aim"];
if (isset($_POST["yahoo"]))               $yahoo               = $_POST["yahoo"];
if (isset($_POST["msn"]))                 $msn                 = $_POST["msn"];
if (isset($_POST["homepage"]))            $homepage            = $_POST["homepage"];
if (isset($_POST["location"]))            $location            = $_POST["location"];
if (isset($_POST["signature"]))           $signature           = $_POST["signature"];
if (isset($_POST["user_maxviewthreads"])) $user_maxviewthreads = $_POST["user_maxviewthreads"];
if (isset($_POST["user_postsperpage"]))   $user_postsperpage   = $_POST["user_postsperpage"];
if (isset($_POST["custom_title"]) && AuthAdminCookie()) $custom_title = $_POST["custom_title"];
if (isset($_POST["timezoneoffset"]))      $timezoneoffset      = $_POST["timezoneoffset"];

// error variables
$emailtaken = 0;
$emailinvalid = 0;
$emailblank = 0;
$passwordinvalid = 0;
$passwordmismatch = 0;
$password1blank = 0;
$password2blank = 0;
$currentpasswrong = 0;

$custom_title_too_long = 0;
$email_too_long = 0;
$password_too_long = 0;
$icq_too_long = 0;
$aim_too_long = 0;
$yahoo_too_long = 0;
$msn_too_long = 0;
$homepage_too_long = 0;
$location_too_long = 0;
$signature_too_long = 0;

if ($submit == 'Modify Profile')
{
	// ERROR CHECK THIS REGISTRATION
	////////////////////////////////

	// check if membername is taken and email taken
	$handle = opendir("profiles/");
	while (false !== ($file = readdir($handle)))
	{ 
		if ($file != "." && $file != ".." && $file != '.htaccess' && $file != '.htpasswd' && 
			$file != 'counter_totalmembers' && $file != 'info_newestmember')
		{
			$profile = file("profiles/$file/profile.dat", "r");

			if (strtolower($profile[0]) == strtolower($email) && strtolower($email) != strtolower($my_profile["email"]))
			{
				$emailtaken = 1;
			}
		}
	}

	// check email
	if ($email == '') {	$emailblank = 1; }
	else if (preg_match('/\s+/', $email)) { $emailinvalid = 1; }
	else if (!preg_match('/.+@.+\..+/', $email)) { $emailinvalid = 1;	}

	$new_password = $my_profile["encrypted_pass"];

	if ($current_password != '' && $password != '' && $passwordcheck != '') // dont check password if they dun wanna change
	{
		$current_encrypted_pass = crypt($current_password,'limabean');

		// make sure passwords match
		if ($current_encrypted_pass != $cookiepassencrypted) { $currentpasswrong = 1; }
		if ($password == '') { $password1blank = 1;	}
		if ($passwordcheck == '') {	$password2blank = 1; }
		if ($password != $passwordcheck) { $passwordmismatch = 1; }
		if (preg_match('/\s+/', $password)) { $passwordinvalid = 1; }
	
		$new_password = crypt($password, 'limabean');
	}

	// check lengths
	if (strlen($email) > 40) $email_too_long = 1;
	if (strlen($custom_title) > 25) $custom_title_too_long = 1;
	if (strlen($password) > 20) $password_too_long = 1;
	if (strlen($icq) > 20) $icq_too_long = 1;
	if (strlen($aim) > 20) $aim_too_long = 1;
	if (strlen($yahoo) > 20) $yahoo_too_long = 1;
	if (strlen($msn) > 20) $msn_too_long = 1;
	if (strlen($homepage) > 40) $homepage_too_long = 1;
	if (strlen($location) > 40) $location_too_long = 1;
	if (strlen($signature) > 255) $signature_too_long = 1;

	$registrationerror = $emailtaken||$emailinvalid||$emailblank||$password1blank||$password2blank||$passwordmismatch||$passwordinvalid
	||$email_too_long||$password_too_long||$icq_too_long||$aim_too_long||$yahoo_too_long
	||$msn_too_long||$homepage_too_long||$location_too_long||$signature_too_long||$custom_title_too_long;

	if (!$registrationerror)
	{
		// debug
		FixParagraph($signature);
		//MBCode2HTML($signature);

		/*echo "membernametaken: ", $membernametaken, "<br>";
		echo "membernameblank: ", $membernameblank, "<br>";
		echo "emailtaken: ", $emailtaken, "<br>";
		echo "emailinvalid: ", $emailinvalid, "<br>";
		echo "emailblank: ", $emailblank, "<br>";
		echo "password1blank: ", $password1blank, "<br>";
		echo "password2blank: ", $password2blank, "<br>";
		echo "passwordmismatch: ", $passwordmismatch, "<br>";
		echo "passwordinvalid: ", $passwordinvalid, "<br>";

		echo "<br>"; ////////////////////

		echo "MEMBERNAME: ", $desired_member_name, "<br>";
		echo "EMAIL: ", $email, "<br>";
		echo "EMAILPUBLIC? ", $publicemail, "<br>";
		echo "PASSWORD1: ", $password, "<br>";
		echo "PASSWORD2: ", $passwordcheck, "<br>";
		echo "ICQ: ", $icq, "<br>";
		echo "HOMEPAGE: ", $homepage, "<br>";
		echo "LOCATION: ", $location, "<br>";
		echo "SIGNATURE:<br>__________<br>", $signature, "<p>";
		echo "THREADS PER PAGE: ", $user_maxviewthreads, "<br>";
		echo "POSTS PER FORUM: ", $user_postsperpage, "<br>";
		echo "ENABLE QUICKFORUM? ", $quickforum, "<br>";*/

		$encrypted_pass = crypt($password,'limabean');

		// modify member profile
		$tempname = tempnam("./forum_temp", "temp_profile_");
		$fpTemp = fopen($tempname, "w");

		fwrite($fpTemp, "$email\n");
		fwrite($fpTemp, "$new_password\n");
		fwrite($fpTemp, "$custom_title\n"); // moderator
		fwrite($fpTemp, "$homepage\n");
		fwrite($fpTemp, "$location\n");
		fwrite($fpTemp, "$signature\n");
		fwrite($fpTemp, "$publicemail\n");
		fwrite($fpTemp, "$user_maxviewthreads\n");
		fwrite($fpTemp, "$user_postsperpage\n");
		fwrite($fpTemp, "$icq\n");
		fwrite($fpTemp, "$aim\n");
		fwrite($fpTemp, "$yahoo\n");
		fwrite($fpTemp, "$msn\n");
		fwrite($fpTemp, $my_profile["timeregistered"]."\n");
		fwrite($fpTemp, "$timezoneoffset\n");
		fwrite($fpTemp, "\n");

		fclose($fpTemp);

		copy($tempname, "profiles/$cookiemembername/profile.dat");
		unlink($tempname);


		// don't display form

		setcookie ("mb4php_member_name",    $cookiemembername, time()+5184000, $forumpath, $forumdomain);
		setcookie ("mb4php_pass_encrypted", $new_password, time()+5184000, $forumpath, $forumdomain);

		$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."\">";
		include("./headerinternal.php"); 
		
		ForumMessage("<b>Profile has been sucessfully modified</b><p>If your browser does not automatically return to the main forum, <a href=\"\">click here</a>.");

		exit();
	}
} 
else
{
	$custom_title = $my_profile["specialrank"];
	$email = $my_profile["email"];
	$icq = $my_profile["icq"];
	$aim = $my_profile["aim"];
	$yahoo = $my_profile["yahoo"];
	$msn = $my_profile["msn"];
	$homepage = $my_profile["homepage"];
	$location = $my_profile["location"];
	$signature = $my_profile["signature"];
	//$my_profile[publicemail]
	//$my_profile[maxviewthreads]
	//$my_profile[postsperpage]

	$signature = str_replace("<br>","\n",$signature);
	$signature = str_replace("&pipe;","|",$signature);

}

$member_name_display = str_replace("&pipe;", "|", $cookiemembername);

$subpagetitle = "Modify Your Profile";
include("./headerinternal.php"); ?>

<? TableHeader("Modify Your Profile") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%><form action="modifyprofile.php" method="post">

		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>Required Information</b></td></table></td></tr>

		<tr><td class=tablecell1 align=right nowrap style="padding: 4px"><font face=verdana size=2><b>Member Name:</b></td>
			<td class=tablecell1 valign=top width=65% style="padding: 4px"><b><? echo $member_name_display ?></b></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($emailtaken) { ?><font class=error>That email address is already registered!</font><? } 
				 else if ($emailinvalid) { ?><font class=error>That is not a valid email address!</font><? } 
				 else if ($emailblank) { ?><font class=error>You MUST enter an email address!</font><? }
				 else if ($email_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				 else { ?><b>Your Email Address:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="email" value="<? if (!$emailtaken && !$emailblank && !$emailinvalid) { echo $email; } ?>" class=forminput> <font face=verdana size=2><b>*</b></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Make Your Email Public?</b></td>
			<td class=tablecell1 valign=top width=65%><input type="checkbox" name="publicemail" value="ON"<? if ($my_profile["publicemail"] == "ON" ) { ?> checked<? } ?>></td></tr>
		
		<? if (AuthAdminCookie()) { ?>
		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>&nbsp;Custom Title</b></td></table></td></tr>
		
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($custom_title_too_long) { ?><font class=error>Maximum length is 25 characters!</font><? }
				else { ?><b>Your Custom Title </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="custom_title" value="<? echo $custom_title ?>" class=forminput></td></tr>
		<? } ?>

		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>&nbsp;Change Password</b></td></table></td></tr>

		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b><? if ($currentpasswrong) { ?><font class=error><? } ?>Current Password:</b></td>
			<td class=tablecell1 valign=top width=65%><input type=password size=40 name="current_password" value="" class=forminput></td></tr>
		
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>New Password:<font size=1><br><br></b>
				<? if ($passwordinvalid) { ?><font class=error>Password is invalid!</font><? } 
				 else if ($passwordmismatch) { ?><font class=error>Passwords do not match!</font><? } 
				 else if ($password1blank || $password2blank) { ?><font class=error>You must enter your password <b>TWICE</b>!</font><? }
				 else if ($password_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				 else { ?>Enter password <b>twice</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=password size=40 name="password" value="" class=forminput> <font face=verdana size=2><br>
													 <input type=password size=40 name="passwordcheck" value="" class=forminput> </td></tr>

		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>&nbsp;Miscellaneous Info</b></td></table></td></tr>
		
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($icq_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				else { ?><b>Your ICQ UIN </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="icq" value="<? echo $icq ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($aim_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				else { ?><b>Your AIM Handle </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="aim" value="<? echo $aim ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($yahoo_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				else { ?><b>&nbsp;Your Yahoo Messenger Handle </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="yahoo" value="<? echo $yahoo ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($msn_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				else { ?><b>Your MSN Messenger Handle </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="msn" value="<? echo $msn ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($homepage_too_long) { ?><font class=error>Maximum length is 40 characters!</font><? }
				else { ?><b>Your Homepage </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="homepage" value="<? if ($homepage != "http://" && $homepage != '') { echo $homepage; } else { echo "http://"; } ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($location_too_long) { ?><font class=error>Maximum length is 40 characters!</font><? }
				else { ?><b>Your Location </b>(optional)<b>:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="location" value="<? echo $location ?>" class=forminput></td></tr>
		<tr><td class=tablecell1 align=right valign=top><font face=verdana size=2><b>Signature </b>(optional)<b>:</b><font size=1><p>Signatures can contain MBCode.<p><? if ($signature_too_long) { ?><font color=red><? } ?>Maximum length is 255 characters.</td>
			<td class=tablecell1 valign=top width=75%><textarea cols=40 rows=5 wrap="VIRTUAL" name="signature" class=forminput><? echo $signature ?></textarea></td></tr>
		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>&nbsp;Forum Preferences</b></td></table></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Forum Index Posts per Page:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="user_maxviewthreads" class=forminput>
													 <option <? if ($my_profile["maxviewthreads"] == 10) { ?>selected <? } ?>value="10">&nbsp&nbsp&nbsp&nbsp&nbsp;10</option>
													 <option <? if ($my_profile["maxviewthreads"] == 20) { ?>selected <? } ?>value="20">&nbsp&nbsp&nbsp&nbsp&nbsp;20</option>
													 <option <? if ($my_profile["maxviewthreads"] == 30) { ?>selected <? } ?>value="30">&nbsp&nbsp&nbsp&nbsp&nbsp;30</option>
													 <option <? if ($my_profile["maxviewthreads"] == 40) { ?>selected <? } ?>value="40">&nbsp&nbsp&nbsp&nbsp&nbsp;40</option>
													 <option <? if ($my_profile["maxviewthreads"] == 50) { ?>selected <? } ?>value="50">&nbsp&nbsp&nbsp&nbsp&nbsp;50</option></select></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Thread View Messages per Page:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="user_postsperpage" class=forminput>
													 <option <? if ($my_profile["postsperpage"] == 10) { ?>selected <? } ?>value="10">&nbsp&nbsp&nbsp&nbsp&nbsp;10</option>
													 <option <? if ($my_profile["postsperpage"] == 20) { ?>selected <? } ?>value="20">&nbsp&nbsp&nbsp&nbsp&nbsp;20</option>
													 <option <? if ($my_profile["postsperpage"] == 30) { ?>selected <? } ?>value="30">&nbsp&nbsp&nbsp&nbsp&nbsp;30</option>
													 <option <? if ($my_profile["postsperpage"] == 40) { ?>selected <? } ?>value="40">&nbsp&nbsp&nbsp&nbsp&nbsp;40</option>
													 <option <? if ($my_profile["postsperpage"] == 50) { ?>selected <? } ?>value="50">&nbsp&nbsp&nbsp&nbsp&nbsp;50</option></select></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Time Zone:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="timezoneoffset" class=forminput style="font-size: 8pt">
<option value="-12" <? $off = "-12"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -12:00) Eniwetok, Kwajalein</option>
<option value="-11" <? $off = "-11"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -11:00) Midway Island, Samoa</option>
<option value="-10" <? $off = "-10"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -10:00) Hawaii</option>
<option value="-9" <? $off = "-9"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -9:00) Alaska</option>
<option value="-8" <? $off = "-8"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -8:00) Pacific Time (US & Canada)</option>
<option value="-7" <? $off = "-7"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -7:00) Mountain Time (US & Canada)</option>
<option value="-6" <? $off = "-6"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -6:00) Central Time (US & Canada), Mexico City</option>
<option value="-5" <? $off = "-5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima, Quito</option>
<option value="-4" <? $off = "-4"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
<option value="-3.5" <? $off = "-3.5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -3:30) Newfoundland</option>
<option value="-3" <? $off = "-3"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
<option value="-2" <? $off = "-2"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -2:00) Mid-Atlantic</option>
<option value="-1" <? $off = "-1"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT -1:00) Azores, Cape Verde Islands</option>
<option value="0" <? $off = "0"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia</option>
<option value="+1" <? $off = "+1"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +1:00) CET(Central Europe Time), Brussels, Copenhagen, Madrid</option>
<option value="+2" <? $off = "+1"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +2:00) EET(Eastern Europe Time), Kaliningrad, South Africa</option>
<option value="+3" <? $off = "+3"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +3:00) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Volgograd</option>
<option value="+3.5" <? $off = "+3.5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +3:30) Tehran</option>
<option value="+4" <? $off = "+4"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
<option value="+4.5" <? $off = "+4.5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +4:30) Kabul</option>
<option value="+5" <? $off = "+5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
<option value="+5.5" <? $off = "+5.5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
<option value="+6" <? $off = "+6"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +6:00) Almaty, Dhaka, Colombo</option>
<option value="+7" <? $off = "+7"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
<option value="+8" <? $off = "+8"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +8:00) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi</option>
<option value="+9" <? $off = "+9"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
<option value="+9.5" <? $off = "+9.5"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +9:30) Adelaide, Darwin</option>
<option value="+10" <? $off = "+10"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +10:00) EAST(East Australian Standard), Guam, Papua New Guinea</option>
<option value="+11" <? $off = "+11"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
<option value="+12" <? $off = "+12"; if ($my_profile["timezone"] == $off) echo "selected"; ?>>(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka, Marshall Island</option>
			</select>
			</td></tr>
		
		<tr><td class=tablecell1 colspan=2 align=center><table cellpadding=2 cellspacing=0 border=0><td><font face=verdana size=2><b>&nbsp;*</b></font><font face=verdana size=1> - denotes required field</td></table></td></tr>
		<tr><td class=tablecell1 align=center valign=top colspan=2 width=100%><input type=submit name=submit value="Modify Profile" class=formbutton> <input type=reset value="Clear Form" class=formbutton></td></tr></form>
		</table>
		</td></table>
	<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>