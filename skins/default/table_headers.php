<?

function TableHeader($title)
{
?><table border=0 cellspacing=0 cellpadding=1 width=100%>
<td class=tableborder>

<table border=0 cellspacing=0 cellpadding=6 width=100%>
<tr><td width=100% class=backtable><b><? echo $title ?></b></td></tr>
<tr><td width=100% class=backtable colspan=2>
<?
}

function TableFooter()
{
?>	</td></tr></table>
	</td></table><?
}

?>