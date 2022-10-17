<center>
	<img src="skins/<? echo $forum_skin ?>/logo1.gif" width=700 height=100><br>
	<font face=verdana size=2><b>
	[	<a class=toplink href="<? echo $forumpath ?>">Main Forum</a> | 
		<? if (!AuthCookie()) { ?><a class=toplink href="register.php">Register</a> | <? } ?>
		<? if (AuthCookie()) { ?><a class=toplink href="modifyprofile.php">My Profile</a> | <? } ?>
		<? if (AuthCookie()) { ?><a class=toplink href="pm.php">Private Messages</a> | <? } ?>
		<? if (!AuthCookie()) { ?><a class=toplink href="login.php">Login</a> | <? }
		   else { ?><a class=toplink href="?logout=1">Logout</a> | <? } ?>
		<a class=toplink href="memberlist.php">Member List</a> | 
		<a class=toplink href="faq.php">F.A.Q.</a> | 
		<a class=toplink href="search.php">Forum Search</a>
	]</b></font>
</center>
<p>