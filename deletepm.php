<?
include("./settings.inc");
include("./support.inc");
$no_support = 1;
$no_settings = 1;

$folder = "Inbox";

if (isset($_POST["folder"])) $folder = $_POST["folder"];

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

if (!isset($_POST["deletemessage"]))
{
	$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."pm.php?folder=$folder\">";
	include("./headerinternal.php");

	ForumMessage("<b>No messages marked for deletion!</b><p>Returning to $folder. If your browser does not automatically return to your $folder, <a href=pm.php?folder=$folder>click here</a>.");
	
	include("./skins/$forum_skin/forumfooter.php");
	exit();
}

// prevent accessing files at same time
WaitForWriteToFinish();

$boxlist = file("profiles/$cookiemembername/$folderfile", "r");

$delete_message = 0;

// create tempfile
$tempname = tempnam("./forum_temp", "temp_pmbox_");
$fpTemp = fopen($tempname, "w");

for ($i=0; $i<sizeof($boxlist); $i++)
{
	foreach ($_POST["deletemessage"] as $key => $value)
	{
		if ($key == $i && $value == "delete")
		{
			$delete_message = 1;
		}
	}

	if (!$delete_message)
	{
		fwrite($fpTemp, rtrim($boxlist[$i])."\n");
	}
	
	$delete_message = 0;
}

fclose($fpTemp);

copy($tempname, "profiles/$cookiemembername/$folderfile");
unlink($tempname);

$meta_tag = "<meta http-equiv=\"Refresh\" content=\"1; URL=http://".$_SERVER['HTTP_HOST'].$forumpath."pm.php?folder=$folder\">";
include("./headerinternal.php");

ForumMessage("<b>Private message(s) deleted.</b><p>If your browser does not automatically return to your $folder, <a href=pm.php?folder=$folder>click here</a>.");

include("./skins/$forum_skin/forumfooter.php");