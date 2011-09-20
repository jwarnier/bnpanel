<script type="text/javascript" src="{$url}includes/javascript/jquery.validate.js"></script>
<script type="text/javascript">
	tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "advanced",

	theme_advanced_toolbar_location : "top",		
	theme_advanced_toolbar_align : "left",		
	theme_advanced_statusbar_location : "bottom",		
	theme_advanced_resizing : true,
	height:"400px",
	width:"100%",
	
	});

	$(function() {		
		$("#addaddon").validate({$json_encode});		
		/* $("button, input:submit", "#addaddon").button(); */
		
	});
</script>
<form class="content" id="addaddon" name="addaddon" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Name:</td>
    <td>
      <input name="name" type="text" id="name" /><a title="The User-Friendly version of the package name. Type whatever you want to show to the users." class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  
  <tr>
    <td valign="top">Description:</td>
    <td><textarea name="description" id="description" cols="45" rows="5"></textarea></td>
  </tr>  
    
  <!--
	<tr>
    	<td valign="top">Setup fee:</td>
    	<td><textarea name="setup_fee" id="description" cols="45" rows="5"></textarea></td>
  	</tr>  
  -->
  
  <tr>
    <td valign="top">Billing cycle:</td>
    <td>
    {$BILLING_CYCLE}
    </td>
  </tr>
   
   
         <tr>
    <td valign="top">Active</td>
    <td>
	 {$STATUS}	
    </td>
  </tr>
  
  	<tr>
    <td valign="top">Mandatory</td>
    <td>
	 {$MANDATORY} <a title="This addon will be set to mandatory for a related package" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
    
	<tr>
    <td valign="top">Install Chamilo</td>
    <td>
	 {$INSTALL_PACKAGE}	<a title="Install chamilo using the Control Panel" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>  

</table>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" id="customform"></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Add Addon" /></td>
  </tr>
</table>
</form>