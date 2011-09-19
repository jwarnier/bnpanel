<script type="text/javascript" src="{$url}includes/javascript/jquery.validate.js"></script>
<script type="text/javascript">
	/*tinyMCE.init({
		mode : "textareas",
		skin : "o2k7",
		theme : "simple",
		width : "100%"
	});*/

	$(function() {		
		$("#add_ticket").validate(%json_encode%);		
	});
	
</script>

<form id="add_ticket" action="" method="post">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td >Subject:</td>
		<td><input name="title" type="text" id="title" value="%TITLE%" size="60" /></td>
	</tr>
	<tr>
		<td colspan="2">
		<textarea name="content" id="content" cols="65" rows="6"></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="reply" id="reply" value="Add Reply" />
		</td>
	</tr>
</table>
</form>
