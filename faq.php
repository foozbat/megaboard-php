<?
$subpagetitle = "Frequently Asked Questions";
include("./headerinternal.php"); ?>


<? TableHeader("Frequently Asked Questions") ?>

	<table border=0 cellspacing=0 cellpadding=0 width=100%>
	<td class=tableborder>
		<table border=0 cellspacing=1 cellpadding=8 width=100%><form action="<?php echo $PHP_SELF ?>" method="post">
		<tr><td class=tablecell1><font face=verdana size=2>

		<b>What is the name that is underneath my username?</b>
		<p>
		That is your rank on the forum.  Rank is determined by the number of posts you make.  Beware, if you spam the board the moderators may decide to remove your rank and denote you a Lamer.  Here are the post levels required to earn rank:
		<p>
		<center>
		<table width=85%>
		<? for ($i=0; $i<sizeof($ranks); $i++)
		{ ?>
		<tr><td width=50% class=tablecell1><b><? echo $ranks[$i] ?></b></td><td width=50% class=tablecell1><? echo $ranklevels[$i];
		if (isset($ranklevels[$i+1])) { echo "-"; echo $ranklevels[$i+1]-1; } else { echo "+"; } ?></td></tr>
		<? } ?>

		<tr><td width=50% class=tablecell1><b>Lamer!</b></td><td width=50% class=tablecell1>Spammers, Flamers, other annoying pests</td></tr>
		</table>
		</center>
		<p>

		<b>Smilies</b><br>
		<center>
		<table width=75%>
		<tr><td class=tablecell1>:)</td><td class=tablecell1>Smile</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/smile.gif"></td></tr>
		<tr><td class=tablecell1>:D</td><td class=tablecell1>Big Smile</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/bigsmile.gif"></td></tr>
		<tr><td class=tablecell1>;)</td><td class=tablecell1>Wink</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/wink.gif"></td></tr>
		<tr><td class=tablecell1>8)</td><td class=tablecell1>Glasses</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/glasses.gif"></td></tr>
		<tr><td class=tablecell1>B)</td><td class=tablecell1>Sun Glasses</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/sunglasses.gif"></td></tr>
		<tr><td class=tablecell1>:(</td><td class=tablecell1>Frown</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/frown.gif"></td></tr>
		<tr><td class=tablecell1>&gt;:(</td><td class=tablecell1>Angry</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/angry.gif"></td></tr>
		<tr><td class=tablecell1>&gt;:O</td><td class=tablecell1>REALLY Angry!</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/pissed.gif"></td></tr>
		<tr><td class=tablecell1>( 8'(|)</td><td class=tablecell1>Homer, D'OH!</td><td class=tablecell1><img src="skins/<? echo $forum_skin ?>/images/emoticons/homer.gif"></td></tr>
		</table>
		</center>
		<p>
		<b>What is MBCode?</b>
		<p>
		MBCode allows you to customize the text of your posts.  It is very similar to HTML, but limits you to basic text functions.  Here are examples of its usage:
		<p>
		<?
		$tags = array("[b]bold text[/b]",
					  "[u]underline text[/u]",
					  "[i]italicized text[/i]",
					  "[list]<br>[*]list item 1<br>[*]list item 2<br>[/list]",
					  "[url]http://www.microsoft.com[/url]",
					  "[url=http://www.microsoft.com]Microsoft Website[/url]",
					  "[email]joeyjoejoe@blahmonkey.com[/email]",
					  "[email=joeyjoejoe@blahmonkey.com]Joey Joe Joe[/email]",
					  "[font=comic sans ms]nifty fonts[/font]",
					  "[size=6]big text[/size]",
					  "[color=red]red text[/color]",
					  "[img]an_image.jpg[/img]");

		?><center>
		<table border=0 cellspacing=0 cellpadding=0 width=100%>
		<td class=tableborder>
			<table border=0 cellspacing=1 cellpadding=10 width=100%><form action="<?php echo $PHP_SELF ?>" method="post">
		
		<? for ($i=0; $i < sizeof($tags); $i++)
		   {
				?><tr><td class=tablecell1><font face=verdana size=2><? echo $tags[$i] ?></td><td class=tablecell1><font face=verdana size=2><font face=verdana size=2><? MBCode2HTML($tags[$i]); echo $tags[$i]; ?></td></tr><?
			} ?>
		</table>
		</td></table></center>


		</td></tr></table>
		</td></table>
	<? TableFooter() ?><?php include("./skins/$forum_skin/forumfooter.php"); ?>