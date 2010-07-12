<script type="text/javascript" src="<URL>includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "simple"
	});
	
	$(function() {
		$( "#due" ).datepicker({ 
			dateFormat: 'yy-mm-dd',
			showOn: 'button',
			buttonImage: '<URL>themes/icons/calendar_add.png'			 
			});
	});

	
	
	function loadAddons(obj) {
		var id=obj.options[obj.selectedIndex].value;
		/*
		var billing_obj = document.getElementById("billing_cycle_id");
		var billing_id=billing_obj.options[billing_obj.selectedIndex].value;*/
		var billing_id = document.getElementById("billing_id").value;
		$.get("<AJAX>?function=loadaddons&package_id="+id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
			document.getElementById("showaddons").innerHTML = data;
		});
	}
	

</script>
<ERRORS>
<form id="addpackage" name="addpackage" method="post" action="">
<input name="package_id" type="hidden" id="package_id" value="%PACKAGE_ID%" />
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Id:</td>
    <td><input name="name" type="text" id="name" value="%ID%" readonly /></td>
  </tr>
  
    <tr>
    <td width="20%">Order id:</td>
    <td><input name="order_id" type="text" id="order_id" value="%ORDER_ID%" readonly /></td>
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
    <input name="name" type="text" id="name" value="%DOMAIN%" readonly />
    </td>
  </tr>
  
    <tr>
    <td valign="top">Description:</td>
    <td><textarea name="notes" id="notes" cols="45" rows="5">%NOTES%</textarea></td>
  </tr>  
  
      <tr>
    <td valign="top">Billing cycles</td>
    <td>
    %BILLING_CYCLES%
    </td>
  </tr>
    
	<tr>
    <td valign="top">Package</td>
    <td>
    %PACKAGE_NAME%
    </td>
  </tr>
  
  	<tr>
    <td valign="top">Package amount</td>
    <td>
    
    <input name="amount" type="text" id="amount" value="%PACKAGE_AMOUNT%" />
    </td>
  </tr>
 
       <tr>
    <td valign="top">Addons</td>
    <td>
 	 %ADDON% 
    </td>
  </tr>
  
    	<tr>
    <td valign="top">Status</td>
    <td>
    %STATUS%
    </td>
  </tr> 
    
  <!-- <tr>
    	<td valign="top">Package amount:</td>
    	<td><input name="amount" type="text" id="amount" value="%AMOUNT%" /></td>
	</tr>  
  
	<tr>
    <td valign="top">Total</td>
    <td>
    %TOTAL%
    </td>
  </tr>
  -->  

  	<tr>
    <td valign="top">Due date</td>
    <td>  		
  		<input name="due" type="text" id="due" value="%DUE%"/>
    </td>
  </tr>  
 
 
  <tr>
    <td valign="top">Email center</td>
    <td>  	
    	<ul>	
  		<li><a href="">Send Invoice Notice</a></li>  		
  		</ul>
    </td>    
  </tr>  
  
</table>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit invoice" /></td>
  </tr>
</table>
</form>
