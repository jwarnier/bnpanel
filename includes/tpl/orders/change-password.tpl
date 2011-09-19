<div class="contextual">
	<a href="?page=orders&sub=view&do=%ID%"> <img src="{$url}themes/icons/arrow_rotate_clockwise.png"> Return to Order</a> 
</div>
<h2>Change Password in Order #%ID%</h2>
<ERRORS>
<form class="content"  name="change_pass" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Username</td>
    <td>%USERNAME%</td>
  </tr>
  <tr>
    <td>New Password:</td>
    <td><input type="password" name="password" id="password" />
    <a title="The new password that you are going to use." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Confirm New Password:</td>
    <td><input type="password" name="confirm" id="confirm" />
    <a title="Please confirm your new password." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="change" id="change" value="Change Password" />
    </td>
  </tr>
</table>
</form>
