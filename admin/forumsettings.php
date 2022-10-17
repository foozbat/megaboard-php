<?
// This module allows easy manipulation of forum settings

include("admin_support.inc");

$submit = "";
$settings_saved = "";
$doesnt_exist = 0;

if (isset($_POST["submit"])) $submit = $_POST["submit"];
if (isset($_GET["settings_saved"])) $settings_saved = $_GET["settings_saved"];


if ($submit == "Submit")
{
	$mainforumtitle = $_POST["input_mainforumtitle"];
	//$forumdomain = $_SERVER["HTTP_HOST"];
	$forumpath = str_replace("admin/forumsettings.php", "", $_SERVER["PHP_SELF"]); //$_POST["input_forumpath"];
	$forum_skin = $_POST["input_forum_skin"];

	if ($_POST["input_addadmin"] != "")
	{
		if (file_exists("../profiles/".$_POST["input_addadmin"]))
		{
			array_push($administrators, $_POST["input_addadmin"]);
		}
		else
		{
			$doesnt_exist = 1;
		}
	}

	if ($_POST["input_addmoderator"] != "")
	{
		if (file_exists("../profiles/".$_POST["input_addmoderator"]))
		{
			array_push($moderators, $_POST["input_addmoderator"]);
		}
		else
		{
			$doesnt_exist = 1;
		}
	}


	WriteForumSettings();
	header("Location: forumsettings.php?settings_saved=1");
}

?>

<? include("admin_header.php") ?>

<? if ($doesnt_exist == 1) { ?><font face=verdana size=2><b>[ <font color=#DD0000>MEMBER DOESN'T EXIST!</font> ]</b></font><p><? }
if ($settings_saved == 1) { ?><font face=verdana size=2><b>[ <font color=#DD0000>SETTINGS SAVED</font> ]</b></font><p><? } ?>
<table border=0 cellspacing=0 cellpadding=1 width=100%>
<td class=tableborder width=100%>

<table border=0 cellspacing=0 cellpadding=6 width=100%>
<tr><td width=100% class=backtable><b>Forum Settings</b><br></td></tr>
<tr><td width=100% class=backtable>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder><form action="forumsettings.php" method=post>
		<table border=0 cellspacing=1 cellpadding=2 width=100%>
		<tr><td width=40% class=tablecell1 align=right><font face=verdana size=2><b>Forum Name:</b></td>
			<td width=60% class=tablecell1><input type=text size=40 name="input_mainforumtitle" value="<? echo $mainforumtitle ?>" class=forminput></td></tr>
		<tr><td width=40% class=tablecell1 align=right><font face=verdana size=2><b>Forum Skin:</b></td>
			<td width=60% class=tablecell1><select name="input_forum_skin" class=forminput>
			<? $handle = opendir("../skins/");
			while (false !== ($skin = readdir($handle)))
			{ 
				if ($skin != "." && $skin != ".." && $skin != '.htaccess' && $skin != '.htpasswd')
				{
					?><option <?php if ($skin == $forum_skin) echo "selected" ?> value="<?php echo $skin ?>"><?php echo $skin ?></option>
					<?
				}
			} ?></select>
			</td></tr>

			<tr><td width=40% class=tablecell1 align=right valign=top><font face=verdana size=2><b>Administrators:</b></td>
				<td width=60% class=tablecell1>
				<table cellspacing=0 cellpadding=0 border=0 width=100%>
				<td class=tableborder>
					<table border=0 cellspacing=1 cellpadding=4 width=100%>
					
				<? foreach ($administrators as $administrator)
				{
					echo "<tr><td class=tablecell1>", $administrator, "</td></tr>\n";
				} ?>
					</table>
				</td></table>

				Add Admin: <input type=text size=25 name="input_addadmin" value="" class=forminput>
				</td></tr>

			<tr><td width=40% class=tablecell1 align=right valign=top><font face=verdana size=2><b>Moderators:</b></td>
				<td width=60% class=tablecell1 nowrap>
				<table cellspacing=0 cellpadding=0 border=0 width=100%>
				<td class=tableborder>
					<table border=0 cellspacing=1 cellpadding=4 width=100%>
					
				<? foreach ($moderators as $moderator)
				{
					echo "<tr><td class=tablecell1>", $moderator, "</td></tr>\n";
				} ?>
					</table>
				</td></table>
				Add Moderator: <input type=text size=25 name="input_addmoderator" value="" class=forminput>

				</td></tr>

			<tr><td colspan=2 class=tablecell1 align=center><input type=submit name=submit value="Submit" class=formbutton> <input type=reset value="Reset" class=formbutton></td></tr>
			</form>
			</table>
	</td></table>
</td></tr></table>
</td></table>

<? include("admin_footer.php") ?>