<script type="text/javascript" src="{$url}includes/javascript/jquery.validate.js"></script>
<script type="text/javascript">
	$(function() {
		$("#addstaff").validate({$json_encode});		
	});	
</script>
<ERRORS>
<form class="content" id="addstaff" name="addstaff" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Username:</td>
    <td>
      <input type="text" name="user" id="user" value="{$USER}" size="30"/>
      <a title="The staff member's username." class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Email:</td>
    <td><input type="text" name="email" id="email" value="{$EMAIL}" size="30"/>
    <a title="The staff member's Email address." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Full Name:</td>
    <td><input type="text" name="name" id="name" value="{$NAME}" size="30"/>
    <a title="The staff member's full name." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
  </tr>
  <tr id="staffperms">
    <td valign="top">ACP Pages: <br />(Check the ones you don't want them to see)</td>
    <td align="center">{$PAGES}</td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Staff Account" /></td>
    </tr>
    </table>
</form>
