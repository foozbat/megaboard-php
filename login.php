<?php
include("settings.inc");
$no_settings = 1;

$membernamefailed = 0;
$passwordfailed = 0;

$login_member_name = "";
$login_password    = "";
$submit            = "";

if (isset($_POST["login_member_name"])) $login_member_name = $_POST["login_member_name"];
if (isset($_POST["login_password"]))    $login_password    = $_POST["login_password"];
if (isset($_POST["submit"]))            $submit            = $_POST["submit"];

header("Cache-Control: must-revalidate");  

if ($submit == 'Login')
{
	if (file_exists("profiles/$login_member_name/profile.dat"))
	{
		$profile = file("profiles/$login_member_name/profile.dat", "r");
		$stored_pass = chop($profile[1]);

		$encrypted_pass = crypt($login_password,'limabean');

		if ($encrypted_pass == $stored_pass)
		{
			setcookie ("mb4php_member_name",    $login_member_name, time()+5184000, $forumpath, $forumdomain);
			setcookie ("mb4php_pass_encrypted", $encrypted_pass, time()+5184000, $forumpath, $forumdomain);

			header("Location: http://".$_SERVER['HTTP_HOST'].$forumpath);
			exit();
		}
		else
		{
			$passwordfailed = 1;
			$HTTP_COOKIE_VARS["mb4php_member_name"] = "";

			setcookie ("mb4php_member_name",    " ", time()-5184000, $forumpath, $forumdomain);
			setcookie ("mb4php_pass_encrypted", " ", time()-5184000, $forumpath, $forumdomain);

		}
	}
	else
	{
		$membernamefailed = 1;
		$HTTP_COOKIE_VARS["mb4php_member_name"] = "";

		setcookie ("mb4php_member_name",    " ", time()-5184000, $forumpath, $forumdomain);
		setcookie ("mb4php_pass_encrypted", " ", time()-5184000, $forumpath, $forumdomain);
	}
}

/*
echo "$login_member_name<br>";
echo "$login_password<br>";

echo "$encrypted_pass, $stored_pass<br>";
echo "passwordfailed: $passwordfailed<br>";
echo "membernamefailed: $membernamefailed";*/

$subpagetitle = "Login";
include("./headerinternal.php");

$somethingfailed = "";
if ($passwordfailed || $membernamefailed)
{
	$somethingfailed = " - [ LOGIN ERROR ]";
}

if ($membernamefailed)
{
	$login_member_name = "";
}

?>

<? TableHeader("Enter Your Info to Login".$somethingfailed) ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=2 width=100%><form action="login.php" method=post>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><?php if ($membernamefailed) { echo "<font class=error>"; } ?><b>Member Name:</b></td>
			<td class=tablecell1 valign=top width=65%><input type=text size=40 name="login_member_name" value="<? echo $login_member_name ?>" class=forminput>&nbsp&nbsp<font face=verdana size=1>You must be <a href="register.php">registered</a>.</td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><?php if ($passwordfailed) { echo "<font class=error>"; } ?><b>Password:</td>
			<td class=tablecell1 valign=top width=65%><input type=password size=40 name="login_password" value="" class=forminput>&nbsp&nbsp<font face=verdana size=1>Case-sensitive.</td></tr>
		<tr><td class=tablecell1 align=right nowrap></td><td class=tablecell1>
			<table cellspacing=2 cellpadding=2><td><font face=verdana size=1 valign=top width=65%>
			In order to login, you muse be registered for this board.<br>
			After you login, you will be able to make posts and edit/delete your own messages</td></table>
			</td></tr>
		<tr><td class=tablecell1 align=center valign=top colspan=2 width=100%><input type=submit name=submit value="Login" class=formbutton> <input type=reset value="Clear Form" class=formbutton></td></tr></form>
		</table>
		</td></table>
	<? TableFooter() ?><?php include("./skins/$forum_skin/forumfooter.php"); ?>