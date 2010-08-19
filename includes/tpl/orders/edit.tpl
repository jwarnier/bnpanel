<script type="text/javascript" src="<URL>includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
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
		buttonImage: '<URL>themes/icons/calendar_add.png'			 
	});*/

	$("#show_preview").dialog({ autoOpen: false, width: '400px' });
});

/*
function send(template, id) {		
	$.get("<AJAX>function=sendtemplate&template="+template+"&order_id="+id,  function(data) {
		$("#show_preview").html(data);				
	});						
	$("#show_preview").dialog('open');
}*/

function changeAddons(obj) {	
	var id=obj.options[obj.selectedIndex].value;
	$.get("<AJAX>function=changeAddons&package_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showdata").innerHTML = data;
	});
}

function loadPackages(obj) {
	var id=obj.options[obj.selectedIndex].value;
	$.get("<AJAX>function=loadPackages&billing_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showpackages").innerHTML = data;
	});
	
	var packages = document.getElementById("package_id");
	loadAddons(packages);
}

function loadAddons(obj) {
	if (obj !=  null) {
		var id=obj.options[obj.selectedIndex].value;
		var billing_obj = document.getElementById("billing_cycle_id");
		var billing_id=billing_obj.options[billing_obj.selectedIndex].value;
	
		$.get("<AJAX>function=loadaddons&package_id="+id+"&billing_id="+billing_id+"&order_id="+document.getElementById("order_id").value, function(data) {
			document.getElementById("showaddons").innerHTML = data;
		});
	}
}
</script>




<ERRORS>
<h2>Order #%ID%</h2>
<form id="addpackage" name="addpackage" method="post" action="">

<input name="order_id" type="hidden" id="order_id" value="%ID%" />

<table width="100%" border="0" cellspacing="2" cellpadding="0">
     <tr>
    <td width="20%">User</td>
    <td>
    %USER%
    </td>
  </tr> 
     <tr>
    <td valign="top">Domain</td>
    <td>
    %DOMAIN%
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
    <a class="tooltip" title="Will operate on the Control Panel server"><img src="<ICONDIR>information.png"></a>
    </td>
  </tr>
  


        <tr>
    <td valign="top">
    Control Panel Username    
    </td>
    <td>
  		%USERNAME% 
  		<a class="tooltip" title="The username to login in the Control Panel System"><img src="<ICONDIR>information.png"></a>
    </td>
  </tr>
  
      <tr>
    <td valign="top">Control Panel Password</td>
    <td>
  		%PASSWORD%
  		<a class="tooltip" title="The password to login in the Control Panel System"><img src="<ICONDIR>information.png"></a>
    </td>
  </tr>
  
  <tr>
    <td valign="top">Creation date</td>
    <td>  		
  		<!--  <input name="created_at" type="text" id="created_at" value="%CREATED_AT%" />  -->
  		%CREATED_AT%
    </td>
</tr>
  
  
     
  
  <tr>
    <td valign="top">Emails sent when editing this order</td>
    
    <td>
    <div id="show_preview" ></div>  	
    	<ul>	
    	<!-- onclick="send('neworder', %ID%);" -->
  		<li><a target="_blank" href="?page=email&sub=templates&do=20">Edit Order Activation email</a> 		<a href="?page=email&sub=templates&do=20"><img src="<URL>themes/icons/pencil.png"></a></li>
  		<li><a target="_blank" href="?page=email&sub=templates&do=21">Edit Order Waiting for Admin email</a><a href="?page=email&sub=templates&do=21"><img src="<URL>themes/icons/pencil.png"></a></li>
  		<li><a target="_blank" href="?page=email&sub=templates&do=22">Edit Order Cancelled email </a>		<a href="?page=email&sub=templates&do=22"><img src="<URL>themes/icons/pencil.png"></a></li>  		
  		</ul>
    </td>    
  </tr>  
   
</table>
<br />
%INVOICE_LIST%


<div class="%SITE_STATUS_CLASS%">
  %SITE_STATUS%<br />
  %SITE_STATUS_INFO%
</div>

<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit order" /></td>
  </tr>
</table>
</form>
