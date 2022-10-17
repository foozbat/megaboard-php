<?
$subpagetitle = "Member List";
include("./headerinternal.php"); 

/*class Member
{
	var $member_name;
	var $posts;
	var $replies;
	var $date_joined;*/

$profile_dir = opendir("profiles/");

$list_admins = array();
$list_moderators = array();
$list_members = array();

while (false !== ($member = readdir($profile_dir)))
{ 
	if ($member != "." && $member != ".." && $member != '.htaccess' && $member != '.htpasswd' &&
		$member != "counter_totalmembers" && $member != "info_newestmember")
	{
		$loaded_profile = LoadProfile($member);
		
		if (AccessLevel($member) == "administrator")
		{
			$list_admins[str_replace("&pipe;", "|", $member)] = array($loaded_profile["posts"], $loaded_profile["replies"], $loaded_profile["timeregistered"]);
		}
		else if (AccessLevel($member) == "moderator")
		{
			$list_moderators[str_replace("&pipe;", "|", $member)] = array($loaded_profile["posts"], $loaded_profile["replies"], $loaded_profile["timeregistered"]);
		}
		else
		{
			$list_members[str_replace("&pipe;", "|", $member)] = array($loaded_profile["posts"], $loaded_profile["replies"], $loaded_profile["timeregistered"]);
		}
	}
}

closedir($profile_dir);

function user_sort($a, $b)
{
	return strcasecmp($a, $b);
}

uksort($list_admins, "user_sort");
uksort($list_moderators, "user_sort");
uksort($list_members, "user_sort");


?>

<? TableHeader("Member List") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%><form action="<?php echo $PHP_SELF ?>" method="post">
		<tr><td class=titlecell2 width=100%><b>Member Name:</b></td>
			<td class=titlecell2 align=center nowrap><b>Posts:</b></td>
			<td class=titlecell2 align=center nowrap><b>Times Replied To:</b></td>
			<td class=titlecell2 align=center nowrap><b>Join Date:</b></td></tr>
		<tr><td class=separatorcell width=100% colspan=4><b>Administrators</b></td></tr>
		<?
		foreach ($list_admins as $member_name => $properties)
		{
			?><tr><td class=tablecell1><a href="viewprofile.php?member=<? echo $member_name ?>"><? echo $member_name ?></a></td>
				<td class=tablecell1 align=center><? echo $properties[0] ?></td>
				<td class=tablecell1 align=center><? echo $properties[1] ?></td>
				<td class=tablecell1 nowrap><? ShowDate($properties[2]) ?></td></tr><?
		}
		?>
		<tr><td class=separatorcell width=100% colspan=4><b>Moderators</b></td></tr>
		<?
		foreach ($list_moderators as $member_name => $properties)
		{
			?><tr><td class=tablecell1><a href="viewprofile.php?member=<? echo $member_name ?>"><? echo $member_name ?></a></td>
				<td class=tablecell1 align=center><? echo $properties[0] ?></td>
				<td class=tablecell1 align=center><? echo $properties[1] ?></td>
				<td class=tablecell1 nowrap><? ShowDate($properties[2]) ?></td></tr><?
		}
		?>
		<tr><td class=separatorcell width=100% colspan=4><b>Regular Users</b></td></tr>
		<?
		foreach ($list_members as $member_name => $properties)
		{
			?><tr><td class=tablecell1><a href="viewprofile.php?member=<? echo $member_name ?>"><? echo $member_name ?></a></td>
				<td class=tablecell1 align=center><? echo $properties[0] ?></td>
				<td class=tablecell1 align=center><? echo $properties[1] ?></td>
				<td class=tablecell1 nowrap><? ShowDate($properties[2]) ?></td></tr><?
		}
		?>
		

		</table>
		</td></table>
<? TableFooter() ?><?php include("./skins/$forum_skin/forumfooter.php"); ?>