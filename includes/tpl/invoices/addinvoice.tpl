<script type="text/javascript" src="<URL>includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="<URL>includes/javascript/jquery.validate.js"></script>
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
		$("#addinvoice").validate(%json_encode%);
	});


	function changeAddons(obj, order_id) {	
		var id=obj.options[obj.selectedIndex].value;
		$.get("<AJAX>?function=changeAddons&package_id="+id+"&order_id="+order_id, function(data) {
			document.getElementById("showdata").innerHTML = data;
		});
	}

	function loadAddons(obj) {
		var package_id=obj.options[obj.selectedIndex].value;				
		var billing_id=document.getElementById("billing_id").value;
				
		$.get("<AJAX>?function=loadaddons&package_id="+package_id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
			document.getElementById("showaddons").innerHTML = data;
		});
	}
</script>
<ERRORS>
<form id="addinvoice" name="addinvoice" method="post" action="">
<input name="billing_id" type="hidden" id="billing_id" value="%BILLING_ID%"/>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Order id:</td>
    <td><input name="order_id" type="text" id="order_id" value="%ID%" readonly /></td>
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
    <td><textarea name="notes" id="notes" cols="45" rows="5"></textarea></td>
  </tr>  

  
      <tr>
    <td valign="top">Billing cycles</td>
    <td>
    %BILLING_CYCLES%   
    </td>
  </tr>
    
     <tr>
    <td valign="top">Packages</td>
    <td>
    %PACKAGES%
    </td>
  </tr>
  

  
       <tr>
    <td valign="top">Addons</td>
    <td>
     <div id = "showaddons"> %ADDON% </div>
    </td>
  </tr>
  
       <tr>
    <td valign="top">Status</td>
    <td>
      %STATUS%
    </td>
  </tr>


    <!--
    <tr>
    	<td valign="top">Package amount:</td>
    	<td><input name="amount" type="text" id="amount" value="%AMOUNT%" /></td>
	</tr>-->
	
     <!--
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
  		<input name="due" type="text" id="due" value="%DUE%" class="required"/>
    </td>
  </tr>
  
    <tr>
    <td valign="top">Email center</td>
    <td>  	
    	<ul>    		
  		<li><a target="_blank" href="?page=email&sub=templates&do=24">Edit New Invoice email</a>		<a href="?page=email&sub=templates&do=24"><img src="<URL>themes/icons/pencil.png"></a></li>
  		</ul>
    </td>    
  </tr>  
  
</table>


%INVOICE_LIST%
 
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Add invoice" /></td>
  </tr>
</table>
</form>
