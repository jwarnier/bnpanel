<script type="text/javascript" src="<URL>includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "simple"
	});

	$(function() {
		$( "#created_at" ).datepicker({ 
			dateFormat: 'yy-mm-dd',
			showOn: 'button',
			buttonImage: '<URL>themes/icons/calendar_add.png'			 
			});
	});
	
</script>

<script type="text/javascript">
function changeAddons(obj) {	
	var id=obj.options[obj.selectedIndex].value;
	$.get("<AJAX>?function=changeAddons&package_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showdata").innerHTML = data;
	});
}

function loadPackages(obj) {
	var id=obj.options[obj.selectedIndex].value;
	$.get("<AJAX>?function=loadPackages&billing_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showpackages").innerHTML = data;
	});
	
	var packages = document.getElementById("package_id");
	loadAddons(packages);
}

function loadAddons(obj) {
	var id=obj.options[obj.selectedIndex].value;
	var billing_obj = document.getElementById("billing_cycle_id");
	var billing_id=billing_obj.options[billing_obj.selectedIndex].value;
	
	$.get("<AJAX>?function=loadaddons&package_id="+id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showaddons").innerHTML = data;
	});
}

</script>

<ERRORS>
<form id="addpackage" name="addpackage" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Id:</td>
    <td><input name="order_id" type="text" id="order_id" value="%ID%" readonly /></a></td>
  </tr>
  
     <tr>
    <td valign="top">User</td>
    <td>
    %USER%
    </td>
  </tr>
  
     <tr>
    <td valign="top">Domain</td>
    <td>
    <input name="domain" type="text" id="domain" value="%DOMAIN%" />
    </td>
  </tr>
  
    

  
  <tr>
    <td valign="top">Billing cycles</td>
    <td>
    %BILLING_CYCLES%
    <div id = "showdata"></div>
    </td>
  </tr>
  
    
     <tr>
    <td valign="top">Packages</td>
    <td>
   
    <div id = "showpackages"> %PACKAGES% </div>
    </td>
  </tr>
  
       <tr>
    <td valign="top">Addons</td>
    <td>
   <div id = "showaddons"> %ADDON% </div>
    </td>
  </tr>
   
   <tr>
    <td valign="top">Order status</td>
    <td>
    %STATUS%
    </td>
  </tr>
  
<tr>
    <td valign="top">Creation date</td>
    <td>  		
  		<input name="created_at" type="text" id="created_at" value="%CREATED_AT%"/>
    </td>
  </tr>  
 
  
</table>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit order" /></td>
  </tr>
</table>
</form>
