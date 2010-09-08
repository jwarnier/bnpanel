<script type="text/javascript" src="<URL>includes/javascript/jquery.validate.js"></script>

<script type="text/javascript">
	tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "advanced",
		theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|formatselect,fontselect,fontsizeselect",			
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions",
		theme_advanced_toolbar_location : "top",		
		theme_advanced_toolbar_align : "left",		
		theme_advanced_statusbar_location : "bottom",		
		theme_advanced_resizing : true,
		height:"400px",
		width:"100%",
	});
	$(function() {
		$("#addpackage").validate(%json_encode%);
	});	
</script>
<ERRORS>
<form class="content" id="addpackage" name="addpackage" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Name:</td>
    <td>
      <input name="name" type="text" id="name" /><a title="The User-Friendly version of the package name. Type whatever you want to show to the users." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td width="20%">Backend:</td>
    <td>
      <input name="backend" type="text" id="backend" /><a title="The <b>backend</b> name of your package. This is the name of the package shown in your WebHost Manager. DO NOT USE SPACES!" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td valign="top">Description:</td>
    <td><textarea name="description" id="description" cols="45" rows="5"></textarea></td>
  </tr>
  <tr>
    <td valign="top">Type:</td>
    <td>
    <select name="type" id="type" onchange="ajaxSlide('customform','<AJAX>function=acpPadd&type='+ this.value)">
      <option value="free">Free</option>
      <option value="p2h">Post 2 Host</option>
      <option value="paid">Paid</option>
    </select><a title="The type of your package. You can choose between <em>free</em>, <em>post2host</em> and <em>paid</em>." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td valign="top">Server:</td>
    <td>
    %SERVER% <a title="The Server which the package is located at." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
   
  
  <tr>
    <td valign="top">Admin Validation:</td>
    <td>
      <input name="admin" type="checkbox" id="admin" value="1" /><a title="Does this package require Administrator Validation?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top">Reseller:</td>
    <td>
      <input name="reseller" type="checkbox" id="reseller" value="1" /><a title="Is this package a reseller package?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top">Hidden:</td>
    <td>
      <input name="hidden" type="checkbox" id="hidden" value="1" /><a title="Is this package hidden on the order form? (Direct orders allowed.)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  
  <tr>
    <td valign="top">Disabled:</td>
    <td>
      <input name="disabled" type="checkbox" id="disabled" value="1" /><a title="Are new orders disabled for this package?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  
   <tr>
    <td valign="top">Addons</td>
    <td>
    %ADDON%
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
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Add Package" /></td>
  </tr>
</table>
</form>