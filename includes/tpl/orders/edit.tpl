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

		$("#show_preview").dialog({ autoOpen: false, width: '400px' });

	});

	function send(template, id) {		
		$.get("<AJAX>?function=sendtemplate&template="+template+"&order_id="+id,  function(data) {
			$("#show_preview").html(data);				
		});						
		$("#show_preview").dialog('open');
	}


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


        <tr>
    <td valign="top">Control Panel Username</td>
    <td>
  		%USERNAME% 
    </td>
  </tr>
  
      <tr>
    <td valign="top">Control Panel Password</td>
    <td>
  		%PASSWORD%
    </td>
  </tr>
  
    
  
  <tr>
    <td valign="top">Email center</td>
    
    <td>
    <div id="show_preview" ></div>  	
    	<ul>	
  		<li><a href="#" onclick="send('neworder', %ID%);" >Send Order Confirmation email</a> <a href="?page=email&sub=templates&do=19"><img src="<URL>themes/icons/pencil.png"></a></li>
  		<li><a href="#" onclick="send('orderactivation', %ID%);"  >Send Activation email </a><a href="?page=email&sub=templates&do=19"><img src="<URL>themes/icons/pencil.png"></a></li>
  		<li><a href="#" onclick="send('ordersuspension', %ID%);"  > Send Suspension email</a><a href="?page=email&sub=templates&do=19"><img src="<URL>themes/icons/pencil.png"></a></li>
  		</ul>
    </td>    
  </tr>  
</table>
<h2>Invoice List</h2>
 %INVOICE_LIST%
 
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit order" /></td>
  </tr>
</table>
</form>
