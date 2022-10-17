<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$member = "";
if (isset($_GET["member"])) $member = $_GET["member"];

FixLine($member);

$loaded_profile = LoadProfile($member);

if ($loaded_profile == "error")
{
	include("./headerinternal.php");
	
	ForumMessage("<b>Member does not exist!</b><p>Make sure you typed in the URL correctly.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}

include("./headerinternal.php"); 

$postsawardname = file("awards/posts", "r");
$popularawardname = file("awards/replies", "r");

$postsaward = 0;
$popularaward = 0;

if ($member == $postsawardname[0])
{
	$postsaward = 1;
}
if ($member == $popularawardname[0])
{
	$popularaward = 1;
}	

// status names
RePipe($member);

$rank = DetermineRank($member, $loaded_profile["posts"], $loaded_profile["specialrank"]);

?>

<? TableHeader($member."'s Profile") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Member since:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? ShowDate($loaded_profile["timeregistered"]) ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Forum Rank:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $rank ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Forum Awards:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><?
			
			if ($postsaward == 1)
			{
				echo "<img src=/megaboard/images/trophy.gif width=19 height=15> Posts Award &nbsp ";
			}
			if ($popularaward == 1)
			{
				echo "<img src=/megaboard/images/thumbsup.gif width=19 height=15> Popularity Award ";
			}
			if ($postsaward != 1 && $popularaward !== 1)
			{
				echo "-none-";
			}
			?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Number of Posts:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["posts"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Times Replied to:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["replies"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap valign=top><font face=verdana size=2><b>Contact Member:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? if ($loaded_profile["publicemail"] == "ON" ) { ?><a href="mailto: <? echo $loaded_profile["email"] ?>">Send Email</a><br><? } ?><a href="sendpm.php?to=<? echo $member ?>">Send Private Message</a></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>ICQ Number:</b></td>
			<td class=tablecell1 valign=top width=75% style="padding: 2px"><? if ($loaded_profile["icq"]) { ?><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="http://wwp.icq.com/scripts/online.dll?icq=<? echo $loaded_profile["icq"] ?>&img=5" border=0 width=18 height=18></td><td class=tablecell1><font face=verdana size=2>&nbsp<? echo $loaded_profile["icq"] ?></td></tr></table><? } ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>AOL Instant Messenger Handle:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["aim"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Yahoo! Messenger Handle:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["yahoo"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>MSN Messenger Handle:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["msn"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Homepage:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><a href="<? echo $loaded_profile["homepage"] ?>"><? echo $loaded_profile["homepage"] ?></td></tr>
		<tr><td class=tablecell1 align=right nowrap><font face=verdana size=2><b>Location:</b></td>
			<td class=tablecell1 valign=top width=75%><font face=verdana size=2><? echo $loaded_profile["location"] ?><font face=verdana size=2></td></tr>

		</table>
	</td></table>
	<? TableFooter() ?><? include("./skins/$forum_skin/forumfooter.php"); ?>