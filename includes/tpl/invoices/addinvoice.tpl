<script type="text/javascript" src="{$url}includes/javascript/jquery.validate.js"></script>
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
			buttonImage: '{$url}themes/icons/calendar_add.png'			 
			});
		$("#addinvoice").validate({$json_encode});
	});


	function changeAddons(obj, order_id) {	
		var id=obj.options[obj.selectedIndex].value;
		$.get("{$ajax}function=changeAddons&package_id="+id+"&order_id="+order_id, function(data) {
			document.getElementById("showdata").innerHTML = data;
		});
	}

	function loadAddons(obj) {
		var package_id=obj.options[obj.selectedIndex].value;				
		var billing_id=document.getElementById("billing_id").value;
				
		$.get("{$ajax}function=loadaddons&package_id="+package_id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
			document.getElementById("showaddons").innerHTML = data;
		});
	}
</script>
<div class="tabs sub_tabs">
	<li><a href="?page=orders&sub=view&do={$ID}"> <img src="{$url}themes/icons/arrow_rotate_clockwise.png"> Return to Order</a></li> 
</div>
<div class="page-header">
	<h2>Add Invoice to Order #{$ID}</h2>
</div>
<form class="content"  id="addinvoice" name="addinvoice" method="post" action="">
<input name="billing_id" type="hidden" id="billing_id" value="{$BILLING_ID}"/>
<input name="package_id" type="hidden" id="package_id" value="{$PACKAGE_ID}"/>

<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Order id:</td>
    <td><a href="?page=orders&sub=view&do={$ID}">#{$ID}</a></td>
  </tr>
     <tr>
    <td valign="top">User</td>
    <td>
    {$USER}
    </td>
	</tr>
	  
     <tr>
    <td valign="top">Domain</td>
    <td>    	
    	 <a target="_blank" href="http://{$REAL_DOMAIN}">{$REAL_DOMAIN}</a>
    </td>
  </tr>
  
    <tr>
    <td valign="top">Description:</td>
    <td><textarea name="notes" id="notes" cols="45" rows="5"></textarea></td>
  </tr>  

  
      <tr>
    <td valign="top">Billing cycles</td>
    <td>
    {$BILLING_CYCLES}   
    </td>
  </tr>
    
     <tr>
    <td valign="top">Packages</td>
    <td>
    {$PACKAGES}
    </td>
  </tr>
  

  
       <tr>
    <td valign="top">Addons</td>
    <td>
     <div id = "showaddons"> {$ADDON} </div>
    </td>
  </tr>
  
       <tr>
    <td valign="top">Status</td>
    <td>
      {$STATUS}
    </td>
  </tr>


    <!--
    <tr>
    	<td valign="top">Package amount:</td>
    	<td><input name="amount" type="text" id="amount" value="{$AMOUNT}" /></td>
	</tr>-->
	
     <!--
	<tr>
    <td valign="top">Total</td>
    <td>
    {$TOTAL}
    </td>
  </tr>
  -->

  	<tr>
    <td valign="top">Due date</td>
    <td>  		
  		<input name="due" type="text" id="due" value="{$DUE}" class="required"/>
    </td>
  </tr>
  
    <tr>
    <td valign="top">Email center</td>
    <td>  	
    	<ul>    		
  		<li><a target="_blank" href="?page=email&sub=templates&do=24">Edit New Invoice email</a>		<a target="_blank" href="?page=email&sub=templates&do=24"><img src="{$url}themes/icons/pencil.png"></a></li>
  		</ul>
    </td>    
  </tr>  
  
</table>



 
<div class="actions">
<input type="submit" name="add" id="add" value="Add invoice" class="btn primary"/>
</div>

{$INVOICE_LIST}

</form>
