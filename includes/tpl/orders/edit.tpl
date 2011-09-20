<script type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "simple"
});

$(function() {
/*	$( "#created_at" ).datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: 'button',
		buttonImage: '{$url}themes/icons/calendar_add.png'			 
	});*/

	/* $("#show_preview").dialog({ autoOpen: false, width: '400px' }); */
});

/*
function send(template, id) {		
	$.get("{$ajax}function=sendtemplate&template="+template+"&order_id="+id,  function(data) {
		$("#show_preview").html(data);				
	});						
	$("#show_preview").dialog('open');
}*/

function changeAddons(obj) {	
	var id=obj.options[obj.selectedIndex].value;
	$.get("{$ajax}function=changeAddons&package_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showdata").innerHTML = data;
	});
}

function loadPackages(obj) {
	var id=obj.options[obj.selectedIndex].value;
	$.get("{$ajax}function=loadPackages&action=edit&billing_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showpackages").innerHTML = data;
	});	
	//var packages = document.getElementById("package_id");
	var packages_id = document.getElementById("package_id").value;	
	loadAddons(packages_id);
}

function loadAddons(obj) {
	if (obj !=  null) {
		//var id=obj.options[obj.selectedIndex].value;
		var id=obj;
		var billing_obj = document.getElementById("billing_cycle_id");
		var billing_id=billing_obj.options[billing_obj.selectedIndex].value;
	
		$.get("{$ajax}function=loadaddons&action=edit&package_id="+id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
			document.getElementById("showaddons").innerHTML = data;
		});
	}
}
</script>
<div class="contextual">
	<a href="?page=orders&sub=view&do={$ID}"> <img src="{$url}themes/icons/order.png"> View </a>
	<a href="?page=orders&sub=add_invoice&do={$ID}"> <img src="{$url}themes/icons/note_add.png"> Add Invoice</a>
	<a href="?page=orders&sub=change_pass&do={$ID}"> <img src="{$url}themes/icons/key.png"> Change CP Password</a>	  
</div>

<h2>Order #{$ID}</h2>

<form class="content" id="addpackage" name="addpackage" method="post" action="">

<input name="order_id" type="hidden" id="order_id" value="{$ID}" />
<input name="package_id" type="hidden" id="package_id" value="{$PACKAGE_ID}" />

<table width="100%" border="0" cellspacing="2" cellpadding="0">
     <tr>
    <td width="20%">User</td>
    <td>
    <a href="?page=users&sub=search&do={$USER_ID}">{$USER}</a>
    </td>
  </tr> 
     <tr>
    <td valign="top">Domain</td>
    <td>    
       <a target="_blank" href="http://{$REAL_DOMAIN}">{$REAL_DOMAIN}</a>
    </td>
  </tr>  
  <tr>
    <td valign="top">Billing cycles</td>
    <td>
    {$BILLING_CYCLES}
    <div id = "showdata"></div>
    </td>
  </tr>
     <tr>
    <td valign="top">Packages</td>
    <td>   
    	<div id = "showpackages"> {$PACKAGES} </div>
    </td>
  </tr>
  
    <tr>
    <td valign="top">Addons</td>
    <td>
   		<div id = "showaddons"> {$ADDON} </div>
    </td>
  </tr>
   
   <tr>
    <td valign="top">Order status</td>
    <td>
    {$STATUS}
    <a class="tooltip" title="Will operate on the Control Panel server"><img src="{$icon_dir}information.png"></a>
    </td>
  </tr>
  


        <tr>
    <td valign="top">
    Control Panel Username    
    </td>
    <td>
  		{$USERNAME} 
  		<a class="tooltip" title="The username to login in the Control Panel System"><img src="{$icon_dir}information.png"></a>
    </td>
  </tr>
  
      <tr>
    <td valign="top">Control Panel Password</td>
    <td>
  		{$PASSWORD}
  		<a class="tooltip" title="The password to login in the Control Panel System"><img src="{$icon_dir}information.png"></a>
    </td>
  </tr>
  
  <tr>
    <td valign="top">Creation date</td>
    <td>  		
  		<!--  <input name="created_at" type="text" id="created_at" value="{$CREATED_AT}" />  -->
  		{$CREATED_AT}
    </td>
</tr>
  
  
     
  
  <tr>
    <td valign="top">Emails sent when editing this order</td>
    
    <td>
    <div id="show_preview" ></div>  	
    	<ul>	
    	<!-- onclick="send('neworder', {$ID});" -->
  		<li><a target="_blank" href="?page=email&sub=templates&do=20">Edit Order Activation email</a> 		<a href="?page=email&sub=templates&do=20"><img src="{$url}themes/icons/pencil.png"></a></li>
  		<li><a target="_blank" href="?page=email&sub=templates&do=21">Edit Order Waiting for Admin email</a><a href="?page=email&sub=templates&do=21"><img src="{$url}themes/icons/pencil.png"></a></li>
  		<li><a target="_blank" href="?page=email&sub=templates&do=22">Edit Order Cancelled email </a>		<a href="?page=email&sub=templates&do=22"><img src="{$url}themes/icons/pencil.png"></a></li>  		
  		</ul>
    </td>    
  </tr>  
   
</table>
<br />
{$INVOICE_LIST}


<div class="{$SITE_STATUS_CLASS}">
  {$SITE_STATUS}<br />
  {$SITE_STATUS_INFO}
</div>

<div class="actions">
    <input type="submit" name="add" id="add" value="Edit order" class="btn primary"/>
</div>
</form>
