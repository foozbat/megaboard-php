<?php if (!isset($no_settings)) { include("./settings.inc"); } 
	  if (!isset($no_support)) { include("./support.inc"); } 
	  
	  // include user defined table headers
include("./skins/$forum_skin/table_headers.php");


$page_title = $mainforumtitle;

if (isset($subpagetitle)) $page_title .= " - ".$subpagetitle;

?>

<html>
<head>
<title><? echo $page_title ?></title>
<? if (isset($meta_tag)) { echo $meta_tag; } ?>
<link rel="stylesheet" href="skins/<? echo $forum_skin ?>/megaboard.css" type="text/css">
</head>

<? include("./skins/$forum_skin/forumheader.php"); 

