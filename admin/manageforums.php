<?
// This module allows easy creation and deletion of forums

include("admin_support.inc");

$totalmessages = 0;				// total posts in entire forum
$totalforumthreads = array();	// total threads in subforum
$totalforumposts = array();		// total posts in subforum
$opcompleted = 0;

// form variables
$op = "";
$newcategory = "";
$newforum = "";
$newforumdescription = "";
$moveup_cat = "";
$moveup_forumid = "";
$movedown_cat = "";
$movedown_forumid = "";
$delete_forumid = "";
$parentcategory = "";

if (isset($_GET["op"])) $op = $_GET["op"];
if (isset($_GET["op"]))  $op = $_GET["op"];
if (isset($_GET["newcategory"]))  $newcategory = $_GET["newcategory"];
if (isset($_GET["newforum"]))  $newforum = $_GET["newforum"];
if (isset($_GET["newforumdescription"]))  $newforumdescription = $_GET["newforumdescription"];
if (isset($_GET["parentcategory"]))  $parentcategory = $_GET["parentcategory"];
if (isset($_GET["moveup_cat"]))  $moveup_cat = $_GET["moveup_cat"];
if (isset($_GET["moveup_forumid"]))  $moveup_forumid = $_GET["moveup_forumid"];
if (isset($_GET["movedown_cat"]))  $movedown_cat = $_GET["movedown_cat"];
if (isset($_GET["movedown_forumid"]))  $movedown_forumid = $_GET["movedown_forumid"];
if (isset($_GET["delete_forumid"]))  $delete_forumid = $_GET["delete_forumid"];


// ADD A NEW CATEGORY
if ($newcategory != "")
{
	$newcategory = preg_replace('/\s+/', ' ', $newcategory);

	if ($newcategory != ' ' && $newcategory != '') $forumindex[$newcategory] = array();

	$opcompleted = true;
}

// ADD A NEW FORUM
if ($newforum != "")
{
	$newforum = preg_replace('/\s+/', ' ', $newforum);

	$last_forumid = file("last_forumid", "r");
	$newforumid = $last_forumid[0] + 1;
	$fp = fopen("last_forumid", "w");
	fwrite($fp, $newforumid);
	fclose($fp);

	$id_to_forum[$newforumid] = $newforum;

	array_push($forumindex[$parentcategory], $newforumid);

	$newforumdescription = preg_replace('/\s+/', ' ', $newforumdescription);
	$newforumdescription = str_replace("\\'", "'", $newforumdescription);
	$forumdescription[$newforumid] = $newforumdescription;
	$forum_flags[$newforumid] = array("visible" => "yes", "closed" => "no", "posting" => "all");

	// create blank counters and indexes
	$fp = fopen("../messages/index_".$newforumid, "w");
	fclose($fp);
	chmod("../messages/index_".$newforumid, 0666);

	$fp = fopen("../messages/counters/counter_".$newforumid."_totalthreads", "w");
	fwrite($fp, "0");
	fclose($fp);
	chmod("../messages/counters/counter_".$newforumid."_totalthreads", 0666);

	$fp = fopen("../messages/counters/counter_".$newforumid."_totalposts", "w");
	fwrite($fp, "0");
	fclose($fp);
	chmod("../messages/counters/counter_".$newforumid."_totalposts", 0666);

	$fp = fopen("../messages/info_".$newforumid."_lastpost", "w");
	fwrite($fp, "never|posted");
	fclose($fp);
	chmod("../messages/info_".$newforumid."_lastpost", 0666);

	$opcompleted = true;
}

// DELETE A FORUM
if ($op == "delete")
{
	if (file_exists("../messages/$delete_forumid"))
	{
		$handle = opendir("../messages/$delete_forumid");

		while (false !== ($file = readdir($handle)))
		{ 
			if ($file != "." && $file != "..")
			{
				unlink("../messages/$delete_forumid/$file");
			}
		}

		closedir($handle);
	
		rmdir("../messages/$delete_forumid");
	}

	//$threadindex =  file("messages/index_".$forum, "r");
	//for ($i = 0; $i < sizeof($threadindex); $i++)
	//{
	//	list ($threadtitle, $starter, $lastposter, $timeposted, $numofposts, $threadid, $sticky) = split('[|]', $threadindex[$i]);
	//}

	unlink("../messages/index_$delete_forumid");
	unlink("../messages/counters/counter_".$delete_forumid."_totalposts");
	unlink("../messages/counters/counter_".$delete_forumid."_totalthreads");
	unlink("../messages/info_".$delete_forumid."_lastpost");

	$index = array_search($delete_forumid, $forumindex[$parentcategory]);

	unset($forumindex[$parentcategory][$index]);
	unset($forumdescription[$delete_forumid]);
	unset($id_to_forum[$delete_forumid]);
	unset($forum_flags[$delete_forumid]);

	$opcompleted = true;
}
else if ($op == "moveupforum")
{
	/*$temp_forumid_array = array_keys($forumindex[$moveup_cat]);
	$temp_forumtitle_array = array_values($forumindex[$moveup_cat]);

	$i = array_search($moveup_forumid, $temp_forumid_array);

	if ($i > 0)
	{
		$temp = $temp_forumid_array[$i-1];
		$temp_forumid_array[$i-1] = $temp_forumid_array[$i];
		$temp_forumid_array[$i] = $temp;

		$temp = $temp_forumtitle_array[$i-1];
		$temp_forumtitle_array[$i-1] = $temp_forumtitle_array[$i];
		$temp_forumtitle_array[$i] = $temp;

		$temp_cat_array = array();

		for ($i=0; $i<sizeof($temp_forumid_array); $i++)
		{
			$temp_cat_array[$temp_forumid_array[$i]] = $temp_forumtitle_array[$i];
		}

		$forumindex[$moveup_cat] = $temp_cat_array;
	}*/

	$i = array_search($moveup_forumid, $forumindex[$moveup_cat]);
	
	if ($i > 0)
	{
		$temp = $forumindex[$moveup_cat][$i-1];
		$forumindex[$moveup_cat][$i-1] = $forumindex[$moveup_cat][$i];
		$forumindex[$moveup_cat][$i] = $temp;
	}

	$opcompleted = true;
}
else if ($op == "movedownforum")
{
	/*$temp_forumid_array = array_keys($forumindex[$movedown_cat]);
	$temp_forumtitle_array = array_values($forumindex[$movedown_cat]);

	$i = array_search($movedown_forumid, $temp_forumid_array);

	if ($i < sizeof($temp_forumid_array)-1)
	{
		$temp = $temp_forumid_array[$i+1];
		$temp_forumid_array[$i+1] = $temp_forumid_array[$i];
		$temp_forumid_array[$i] = $temp;

		$temp = $temp_forumtitle_array[$i+1];
		$temp_forumtitle_array[$i+1] = $temp_forumtitle_array[$i];
		$temp_forumtitle_array[$i] = $temp;

		$temp_cat_array = array();

		for ($i=0; $i<sizeof($temp_forumid_array); $i++)
		{
			$temp_cat_array[$temp_forumid_array[$i]] = $temp_forumtitle_array[$i];
		}

		$forumindex[$movedown_cat] = $temp_cat_array;
	}*/

	$i = array_search($movedown_forumid, $forumindex[$movedown_cat]);
	
	if ($i < sizeof($forumindex[$movedown_cat])-1)
	{
		$temp = $forumindex[$movedown_cat][$i+1];
		$forumindex[$movedown_cat][$i+1] = $forumindex[$movedown_cat][$i];
		$forumindex[$movedown_cat][$i] = $temp;
	}

	$opcompleted = true;
}
else if ($op == "moveupcat")
{
	$temp_cat_titles_array = array_keys($forumindex);
	$temp_cat_forums_array = array_values($forumindex);

	$i = array_search($moveup_cat, $temp_cat_titles_array);

	if ($i > 0)
	{
		$temp = $temp_cat_titles_array[$i-1];
		$temp_cat_titles_array[$i-1] = $temp_cat_titles_array[$i];
		$temp_cat_titles_array[$i] = $temp;

		$temp = $temp_cat_forums_array[$i-1];
		$temp_cat_forums_array[$i-1] = $temp_cat_forums_array[$i];
		$temp_cat_forums_array[$i] = $temp;

		$temp_index_array = array();

		for ($i=0; $i<sizeof($temp_cat_titles_array); $i++)
		{
			$temp_index_array[$temp_cat_titles_array[$i]] = $temp_cat_forums_array[$i];
		}

		$forumindex = $temp_index_array;
	}

	$opcompleted = true;
}
else if ($op == "movedowncat")
{
	$temp_cat_titles_array = array_keys($forumindex);
	$temp_cat_forums_array = array_values($forumindex);

	$i = array_search($movedown_cat, $temp_cat_titles_array);

	if ($i < sizeof($temp_cat_titles_array)-1)
	{
		$temp = $temp_cat_titles_array[$i+1];
		$temp_cat_titles_array[$i+1] = $temp_cat_titles_array[$i];
		$temp_cat_titles_array[$i] = $temp;

		$temp = $temp_cat_forums_array[$i+1];
		$temp_cat_forums_array[$i+1] = $temp_cat_forums_array[$i];
		$temp_cat_forums_array[$i] = $temp;

		$temp_index_array = array();

		for ($i=0; $i<sizeof($temp_cat_titles_array); $i++)
		{
			$temp_index_array[$temp_cat_titles_array[$i]] = $temp_cat_forums_array[$i];
		}

		$forumindex = $temp_index_array;
	}

	$opcompleted = true;
}

if ($opcompleted == true)
{
	// Commit settings to file
	WriteForumSettings();

	header("Location: manageforums.php");
}

?>

<html>
<head>
<title>Manage Forums</title>
<link rel="stylesheet" href="megaboard-admin.css" type="text/css">
</head>

<center>
<font face=arial size=5><b>Manage Forums</b></font>
<p>

<table border=0 cellspacing=0 cellpadding=1 width=100%>
<td class=tableborder>

<table border=0 cellspacing=0 cellpadding=6 width=100%>
<tr><td width=100% class=backtable><b>Forum Index</b><br></td></tr>
<tr><td width=100% class=backtable>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=4 width=100%>
		<tr><td width=100% class=titlecell2><font face=verdana size=2><b>Forums:</b></td>
			<td nowrap class=titlecell2><font face=verdana size=2><b>Threads</b></td>
			<td nowrap class=titlecell2><font face=verdana size=2><b>Posts</b></td>
			<td nowrap class=titlecell2 colspan=3 align=center><font face=verdana size=2><b>Options</b></td></tr>
<?

foreach ($forumindex as $categorytitle => $categoryforums)
{
		// show separator
		?>
		<tr><td colspan=3 class=separatorcell><b><? echo $categorytitle ?></b></td>
			<td class=separatorcell align=center><a href="">Edit</a></td>
			<td class=separatorcell align=center><a href="">Delete</a></td>
			<td nowrap class=separatorcell align=center><a href="manageforums.php?op=moveupcat&moveup_cat=<? echo $categorytitle ?>">Move UP</a> | <a href="manageforums.php?op=movedowncat&movedown_cat=<? echo $categorytitle ?>">Move DOWN</a></td>
		</tr><?
	foreach ($categoryforums as $forumid)
	{
		$forumthreads = file("../messages/counters/counter_".$forumid."_totalthreads"," r");
		$forumposts = file("../messages/counters/counter_".$forumid."_totalposts"," r");

		// display info

		?>
		<tr><td width=100% class=tablecell1>
			<table border=0 cellspacing=0 cellpadding=0>
			<tr><td valign=middle><img src="../skins/default/images/<?
			if ($forumid == "TRASH")
			{
				echo "trashcan.gif";
			}
			else if ($forum_flags[$forumid]["closed"] == "yes")
			{
				echo "folder-closed.gif";
			} 
			else
			{
				echo "folder.gif";
			}?>" width=24 height=22>&nbsp;</td><td width=100%><font face=verdana size=2><b><a href="../viewforum.php?forum=<?php echo $forumid ?>"><?php echo $id_to_forum[$forumid] ?></b></a><br><font size=1>» <?php echo $forumdescription[ $forumid ] ?></td></tr></table></td>

			<td nowrap align=center class=tablecell1><font face=arial size=2><?php echo $forumthreads[0] ?></td>
			<td nowrap align=center class=tablecell1><font face=arial size=2><?php echo $forumposts[0] ?></td>
			<td align=center class=tablecell1><a href="">Edit</a></td>
			<td align=center class=tablecell1><a href="manageforums.php?op=delete&delete_forumid=<? echo $forumid ?>&parentcategory=<? echo $categorytitle ?>">Delete</a></td>
			<td nowrap align=center class=tablecell1><a href=""><a href="manageforums.php?op=moveupforum&moveup_cat=<? echo $categorytitle ?>&moveup_forumid=<? echo $forumid ?>">Move UP</a> | <a href="manageforums.php?op=movedownforum&movedown_cat=<? echo $categorytitle ?>&movedown_forumid=<? echo $forumid ?>">Move DOWN</a></td>
			</tr><?
	}

	?>		<tr><td nowrap class=tablecell1 colspan=6>
			<table border=0 cellspacing=0 cellpadding=0>
			<tr><td width=100%><font face=verdana size=2><form action="manageforums.php" method=get>
				<table border=0 cellspacing=0 cellpadding=0>
				<tr><td class=tablecell1 colspan=2><b>Add a new forum: &nbsp</b></td></tr>
				<tr><td class=tablecell1 align=right>
					Name: &nbsp<br>&nbsp Description: &nbsp</td>
					<td class=tablecell1>					
					<input type=text size=30 class=forminput name=newforum><br>
					<input type=text size=30 class=forminput name=newforumdescription>
					<input type=hidden name=parentcategory value="<? echo $categorytitle ?>"></td>
					<td class=tablecell1>&nbsp <input type=submit value="ADD" class=formbutton></td></tr></table>
				</td></tr></table>
			</td></tr></form><?
}

?>		<tr><td class=tablecell1 colspan=6><img src="../spacer.gif" height=2 width=2></td></tr>
		<tr><td nowrap class=separatorcell colspan=6><form><b>Add a new category: </b><input type=text size=30 class=forminput name=newcategory> &nbsp<input type=submit value="ADD" class=formbutton></td></tr></form>

		</table>
		</td></table>
	</td></tr></table>
	</td></table>
</center>
<?
