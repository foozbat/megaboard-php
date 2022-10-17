<?
include("./settings.inc");
include("./support.inc");
$no_settings = 1;
$no_support = 1;

// form variables
$submit = "";
$desired_member_name = "";
$email = "";
$publicemail = "";
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
$timezoneoffset = "";

if (isset($_POST["submit"]))              $submit              = $_POST["submit"];
if (isset($_POST["desired_member_name"])) $desired_member_name = $_POST["desired_member_name"];
if (isset($_POST["email"]))               $email               = $_POST["email"];
if (isset($_POST["publicemail"]))         $publicemail         = $_POST["publicemail"];
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
if (isset($_POST["timezoneoffset"]))      $timezoneoffset      = $_POST["timezoneoffset"];

// error variables
$membernametaken = 0;
$membernameblank = 0;
$membernameinvalid = 0;
$emailtaken = 0;
$emailinvalid = 0;
$emailblank = 0;
$passwordinvalid = 0;
$passwordmismatch = 0;
$password1blank = 0;
$password2blank = 0;

$membername_too_long = 0;
$email_too_long = 0;
$password_too_long = 0;
$icq_too_long = 0;
$aim_too_long = 0;
$yahoo_too_long = 0;
$msn_too_long = 0;
$homepage_too_long = 0;
$location_too_long = 0;
$signature_too_long = 0;

if ($submit == 'Submit Registration')
{
	// ERROR CHECK THIS REGISTRATION
	////////////////////////////////

	FixLine($desired_member_name);

	// check if membername is taken and email taken
	$handle = opendir("profiles/");
	while (false !== ($file = readdir($handle)))
	{ 
		if ($file != "." && $file != ".." && $file != '.htaccess' && $file != '.htpasswd' && 
			$file != 'counter_totalmembers' && $file != 'info_newestmember')
		{
			$profile = file("profiles/$file/profile.dat", "r");

			if (strtolower($file) == strtolower($desired_member_name)) { $membernametaken = 1; }
			if (strtolower($profile[0]) == strtolower($email)) { $emailtaken = 1; }
		}
	}

	// check for blank membername
	if ($desired_member_name == '' || $desired_member_name == ' ') { $membernameblank = 1; }
	if (preg_match('/"/', $desired_member_name)) { $membernameinvalid = 1; } 
	if (preg_match('/\\\\/', $desired_member_name)) { $membernameinvalid = 1; }
	if (preg_match('/\//', $desired_member_name)) { $membernameinvalid = 1; }
	if (preg_match('/\:/', $desired_member_name)) { $membernameinvalid = 1; }
	if (preg_match('/\*/', $desired_member_name)) { $membernameinvalid = 1; }
	if (preg_match('/\?/', $desired_member_name)) { $membernameinvalid = 1; }

	// check email
	if ($email == '') {	$emailblank = 1; }
	else if (preg_match('/\s+/', $email)) { $emailinvalid = 1; }
	else if (!preg_match('/.+@.+\..+/', $email)) { $emailinvalid = 1;	}

	// make sure passwords match
	if ($password == '') { $password1blank = 1;	}
	if ($passwordcheck == '') {	$password2blank = 1; }
	if ($password != $passwordcheck) { $passwordmismatch = 1; }
	if (preg_match('/\s+/', $password)) { $passwordinvalid = 1; }

	// check lengths
	if (strlen($desired_member_name) > 20) $membername_too_long = 1;
	if (strlen($email) > 40) $email_too_long = 1;
	if (strlen($password) > 20) $password_too_long = 1;
	if (strlen($icq) > 20) $icq_too_long = 1;
	if (strlen($aim) > 20) $aim_too_long = 1;
	if (strlen($yahoo) > 20) $yahoo_too_long = 1;
	if (strlen($msn) > 20) $msn_too_long = 1;
	if (strlen($homepage) > 40) $homepage_too_long = 1;
	if (strlen($location) > 40) $location_too_long = 1;
	if (strlen($signature) > 255) $signature_too_long = 1;

	$registrationerror = $membernametaken||$membernameblank||$membernameinvalid||$emailtaken||$emailinvalid||$emailblank
						 ||$password1blank||$password2blank||$passwordmismatch||$passwordinvalid||$membername_too_long
						 ||$email_too_long||$password_too_long||$icq_too_long||$aim_too_long||$yahoo_too_long
						 ||$msn_too_long||$homepage_too_long||$location_too_long||$signature_too_long;

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

		// write new member profile
		mkdir("profiles/$desired_member_name", 0755);
		$fp = fopen("profiles/$desired_member_name/profile.dat",'w');

		fwrite($fp, "$email\n");
		fwrite($fp, "$encrypted_pass\n");
		fwrite($fp, "\n"); // moderator
		fwrite($fp, "$homepage\n");
		fwrite($fp, "$location\n");
		fwrite($fp, "$signature\n");
		fwrite($fp, "$publicemail\n");
		fwrite($fp, "$user_maxviewthreads\n");
		fwrite($fp, "$user_postsperpage\n");
		fwrite($fp, "$icq\n");
		fwrite($fp, "$aim\n");
		fwrite($fp, "$yahoo\n");
		fwrite($fp, "$msn\n");
		fwrite($fp, time()."\n");
		fwrite($fp, "$timezoneoffset\n");
		fwrite($fp, "\n");

		fclose($fp);
		chmod("profiles/$desired_member_name/profile.dat", 0666);

		$fp = fopen("profiles/$desired_member_name/posts.dat",'w');
		fwrite($fp, "0");
		fclose($fp);
		chmod("profiles/$desired_member_name/posts.dat", 0666);

		$fp = fopen("profiles/$desired_member_name/replies.dat",'w');
		fwrite($fp, "0");
		fclose($fp);
		chmod("profiles/$desired_member_name/replies.dat", 0666);

		$fp = fopen("profiles/$desired_member_name/inbox.dat",'w');
		fclose($fp);
		chmod("profiles/$desired_member_name/replies.dat", 0666);

		$fp = fopen("profiles/$desired_member_name/sentbox.dat",'w');
		fclose($fp);
		chmod("profiles/$desired_member_name/replies.dat", 0666);

		$fp = fopen("profiles/$desired_member_name/counter_unread_pm",'w');
		fwrite($fp, "0");
		fclose($fp);
		chmod("profiles/$desired_member_name/counter_unread_pm", 0666);

		IncrementCounter("profiles/counter_totalmembers");
		$fp = fopen("profiles/info_newestmember", "w");
		fwrite($fp, $desired_member_name);
		fclose($fp);

		// don't display form

		setcookie ("mb4php_member_name",    $desired_member_name, time()+5184000, $forumpath, $forumdomain);
		setcookie ("mb4php_pass_encrypted", $encrypted_pass, time()+5184000, $forumpath, $forumdomain);

		header("Cache-Control: must-revalidate");  
		header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath);
		exit();
	}
} 

$subpagetitle = "Register a New Account";
include("./headerinternal.php"); ?>

<? TableHeader("Register a New Account") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%><form action="register.php" method="post">

		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>Required Information</b></td></table></td></tr>

		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($membernametaken) { ?><font class=error>That member name already taken!</font><? } 
				 else if ($membernameblank) { ?><font class=error>You MUST enter a member name!</font><? } 
				 else if ($membernameinvalid) { ?><font class=error>Invalid member name!</font><? } 
				 else if ($membername_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				 else { ?><b>Desired Member Name:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="desired_member_name" value="<? if (!$membernametaken && !$membernameblank && !$membernameinvalid) { echo $desired_member_name; } ?>" class=forminput> <font face=verdana size=2><b>*</b></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2>
				<? if ($emailtaken) { ?><font class=error>That email address is already registered!</font><? } 
				 else if ($emailinvalid) { ?><font class=error>That is not a valid email address!</font><? } 
				 else if ($emailblank) { ?><font class=error>You MUST enter an email address!</font><? }
				 else if ($email_too_long) { ?><font class=error>Maximum length is 40 characters!</font><? }
				 else { ?><b>Your Email Address:</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="email" value="<? if (!$emailtaken && !$emailblank && !$emailinvalid) { echo $email; } ?>" class=forminput> <font face=verdana size=2><b>*</b></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Make Your Email Public?</b></td>
			<td class=tablecell1 valign=top width=65%><input type="checkbox" name="publicemail" value="ON" checked></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Desired Password:<font size=1><br><br></b>
				<? if ($passwordinvalid) { ?><font class=error>Password is invalid!</font><? } 
				 else if ($passwordmismatch) { ?><font class=error>Passwords do not match!</font><? } 
				 else if ($password1blank || $password2blank) { ?><font class=error>You must enter your password <b>TWICE</b>!</font><? }
				 else if ($password_too_long) { ?><font class=error>Maximum length is 20 characters!</font><? }
				 else { ?>Enter password <b>twice</b><? } ?></td>
			<td class=tablecell1 valign=top width=65%><input type=password size=40 name="password" value="" class=forminput> <font face=verdana size=2><b>*</b><br>
													 <input type=password size=40 name="passwordcheck" value="" class=forminput> <b>*</b></td></tr>

		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>Miscellaneous Information</b></td></table></td></tr>

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
		<tr><td class=tablecell1 align=right valign=top><font face=verdana size=2><b>Signature </b>(optional)<b>:</b><font size=1><p>Signatures can contain MBCode.<p><? if ($signature_too_long) { ?><font class=error><? } ?>Maximum length is 255 characters.</td>
			<td class=tablecell1 valign=top width=75%><textarea cols=40 rows=5 wrap="VIRTUAL" name="signature" class=forminput><? echo $signature ?></textarea></td></tr>
		<tr><td class=titlecell colspan=2><table cellpadding=2 cellspacing=0 border=0><td class=titlecell><font face=verdana size=2><b>Forum Preferences</b></td></table></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Forum Index Posts per Page:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="user_maxviewthreads" class=forminput>
													 <option value="10">&nbsp&nbsp&nbsp&nbsp&nbsp;10</option>
													 <option selected value="20">&nbsp&nbsp&nbsp&nbsp&nbsp;20</option>
													 <option value="30">&nbsp&nbsp&nbsp&nbsp&nbsp;30</option>
													 <option value="40">&nbsp&nbsp&nbsp&nbsp&nbsp;40</option>
													 <option value="50">&nbsp&nbsp&nbsp&nbsp&nbsp;50</option></select></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Thread View Messages per Page:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="user_postsperpage" class=forminput>
													 <option value="10">&nbsp&nbsp&nbsp&nbsp&nbsp;10</option>
													 <option selected value="20">&nbsp&nbsp&nbsp&nbsp&nbsp;20</option>
													 <option value="30">&nbsp&nbsp&nbsp&nbsp&nbsp;30</option>
													 <option value="40">&nbsp&nbsp&nbsp&nbsp&nbsp;40</option>
													 <option value="50">&nbsp&nbsp&nbsp&nbsp&nbsp;50</option></select></td></tr>
				<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Time Zone:</b></td>
			<td class=tablecell1 valign=top width=65%><select name="timezoneoffset" class=forminput style="font-size: 8pt">
				<option value="-12">(GMT -12:00) Eniwetok, Kwajalein</option>
				<option value="-11">(GMT -11:00) Midway Island, Samoa</option>
				<option value="-10">(GMT -10:00) Hawaii</option>
				<option value="-9">(GMT -9:00) Alaska</option>
				<option value="-8">(GMT -8:00) Pacific Time (US & Canada)</option>
				<option value="-7">(GMT -7:00) Mountain Time (US & Canada)</option>
				<option value="-6" selected>(GMT -6:00) Central Time (US & Canada), Mexico City</option>
				<option value="-5">(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima, Quito</option>
				<option value="-4">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
				<option value="-3.5">(GMT -3:30) Newfoundland</option>
				<option value="-3">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
				<option value="-2">(GMT -2:00) Mid-Atlantic</option>
				<option value="-1">(GMT -1:00) Azores, Cape Verde Islands</option>
				<option value="0">(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia</option>
				<option value="+1">(GMT +1:00) CET(Central Europe Time), Brussels, Copenhagen, Madrid</option>
				<option value="+2">(GMT +2:00) EET(Eastern Europe Time), Kaliningrad, South Africa</option>
				<option value="+3">(GMT +3:00) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Volgograd</option>
				<option value="+3.5">(GMT +3:30) Tehran</option>
				<option value="+4">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
				<option value="+4.5">(GMT +4:30) Kabul</option>
				<option value="+5">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
				<option value="+5.5">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
				<option value="+6">(GMT +6:00) Almaty, Dhaka, Colombo</option>
				<option value="+7">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
				<option value="+8">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi</option>
				<option value="+9">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
				<option value="+9.5">(GMT +9:30) Adelaide, Darwin</option>
				<option value="+10">(GMT +10:00) EAST(East Australian Standard), Guam, Papua New Guinea</option>
				<option value="+11">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
				<option value="+12">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka, Marshall Island</option>
			</select>
			</td></tr>
		<tr><td class=tablecell1 colspan=2 align=center><table cellpadding=2 cellspacing=0 border=0><td><font face=verdana size=2><b>&nbsp;*</b></font><font face=verdana size=1> - denotes required field</td></table></td></tr>
		<tr><td class=tablecell1 align=center valign=top colspan=2 width=100%><input type=submit name=submit value="Submit Registration" class=formbutton> <input type=reset value="Clear Form" class=formbutton></td></tr></form>
		</table>
		</td></table>
	<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>