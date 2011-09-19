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
</script>
<ERRORS>
<form class="content"  id="addpackage" name="addpackage" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Name:</td>
    <td>
      <input name="name" type="text" id="name" value="{$NAME}" />
      <a title="The package name" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>  
  <tr>
    <td valign="top">Description:</td>
    <td><textarea name="description" id="description" cols="45" rows="5">{$DESCRIPTION}</textarea></td>
  </tr>


  
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
    <td valign="top">Mandatory 
    </td>
    <td>
	 {$MANDATORY} <a title="This addon will be set to mandatory for a related package" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
    
           <tr>
    <td valign="top">Install Chamilo</td>
    <td>
	 {$INSTALL_PACKAGE}	
    </td>
  </tr>
  
  
 
    
</table>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Addon" /></td>
  </tr>
</table>
</form>
