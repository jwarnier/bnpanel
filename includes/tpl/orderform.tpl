<script type="text/javascript">
var step = 1;
var form = document.getElementById("order");
var wrong = '<img src="<URL>themes/icons/cross.png">';
var right = '<img src="<URL>themes/icons/accept.png">';
var loading = '<img src="<URL>themes/icons/ajax-loader.gif">';
var working = '<div align="center"><img src="<URL>themes/icons/working.gif"></div>';
var result;
var pid;

$(document).ready(function(){
   $("#username").change(function(event) {
	   this.value = this.value.toLowerCase();
	   check('user', this.value);
   });
});

function stopRKey(evt) { 
  var evt = (evt) ? evt : ((event) ? event : null); 
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
  if ((evt.keyCode == 13) && (node.type=="text"))  {return false;} 
} 

document.onkeypress = stopRKey;

function check(name, value) {
	$("#"+name+"check").html(loading);
	document.getElementById("next").disabled = true;
	window.setTimeout(function() {
		$.get("<AJAX>?function="+name+"check&THT=1&"+name+"="+value, function(data) {
			if(data == "1") {
				$("#"+name+"check").html(right);
			}
			else {
				$("#"+name+"check").html(wrong);
			}													
			document.getElementById("next").disabled = false;
		});
	},500);
}

function orderstepme(id, type) {
	pid = id;
	document.getElementById("package").value = id;
	document.getElementById("order"+id).disabled = true;
	
	if(document.getElementById("domain").value == "sub") {
		document.getElementById("dom").style.display = 'none';
		document.getElementById("sub").style.display = '';
		$.get("<AJAX>?function=sub&pack="+document.getElementById("package").value, function(data) {
			document.getElementById("dropdownboxsub").innerHTML = data;
		});
	} else if(document.getElementById("domain").value == "dom") {
		document.getElementById("sub").style.display = 'none';
		document.getElementById("dom").style.display = '';
	}	
	$.get('<AJAX>?function=orderForm&package='+ document.getElementById("package").value, function(stuff) {
		$("#custom").html('<table width="100%" border="0" cellspacing="2" cellpadding="0" id="custom">'+stuff+'</table>');		
	});
	var step_hide = step;
	if (type != 'paid') {		
		step = 3;
	}
	showhide(step_hide, step + 1)
	step = step + 1;
}

function showhide(hide, show) {
	document.getElementById("next").disabled = true;
	document.getElementById("back").disabled = true;
	document.getElementById("verify").innerHTML = "";
	
	$("#"+hide).fadeOut(1000, function() {
		$("#steps").fadeIn(1000);
		$("#"+show).fadeIn(1000, function() {
			document.getElementById("next").disabled = false;
			document.getElementById("back").disabled = false;
		});
     });
}

function nextstep() {
	alert(step);
	switch(step) {
		//addon info
		/*case 2:
			if(1) {			
				showhide(step, step + 1);
				step = step + 1;				
			}
			else {
				document.getElementById("verify").innerHTML = wrong
			}
		break;*/
		
		//Resume
		case 2:			
			if(document.getElementById("billing_id").value != 0) {
				var addon_list = '';
				// If no addons
				if (document.getElementById("addon_ids") != null) {
					//Only one addon present
					if (document.order.addon_ids.length ==  null) {					
						addon_list  = document.order.addon_ids.value;
					} else {				
						for (var i=0; i < document.order.addon_ids.length; i++) {	
							if (document.order.addon_ids[i].checked) {
						   		if (document.order.addon_ids[i].value != 'undefined' ) { 
						      		addon_list = addon_list + document.order.addon_ids[i].value + '-';
								}				      
							}
						}						
					}
				}				
				var billing_id  = document.getElementById("billing_id").value;
				showhide(step, step + 1);
				step = step + 1;		
				$.get("<AJAX>?function=getSummary&billing_id="+billing_id +"&addon_list="+addon_list +"&package_id="+document.getElementById("package").value, function(data) {
					document.getElementById("show_summary").innerHTML = data;
				});				
			} else  {
				$("#verify").html("<strong>You must select a Billing Cycle</strong> "+wrong);
			}
		break;		
		
		case 3:		
			//After selecting the payment mode
			showhide(step, step + 1);
			step = step + 1;
			/*	
			if(document.getElementById("agree").checked == true) {
				$.get("<AJAX>?function=orderIsUser", function(data) {
					if (data == "1") {
						showhide(step, step + 2)
						step = step + 2
					}
					else {
						showhide(step, step + 1)
						step = step + 1
					}
				});
			}
			else {
				document.getElementById("verify").innerHTML = wrong
			}*/
			
			break;
			
		case 4:
			//TOS				
			if(document.getElementById("agree").checked == true) {								
				$.get("<AJAX>?function=orderIsUser", function(data) {
					if (data == "1") {
						showhide(step, step + 2)
						step = step + 2
					} else {						
						showhide(step, step + 1)
						step = step + 1
					}
				});
			} else {
				$("#verify").html("<strong>You must agree the Terms of Service</strong> "+wrong);
			}			
			break;
			
		case 5:			
			//User form
			$.get("<AJAX>?function=clientcheck", function(data) {
				if(data == "1") {					
					if (document.getElementById("username").value != '' ) {
						document.getElementById("verify").innerHTML = right;
						showhide(step, step + 1)
						step = step + 1;
					} else {
						$("#verify").html("<strong>You must fill all the fields</strong> "+wrong);
					}	
				} else {
					$("#verify").html("<strong>You must fill all the fields</strong> "+wrong);
				}													
			});
			break;
			
		case 6:
			
			final(step, step + 1)
			step = step + 1
			var url = "?function=create";
			var i;
			
			for(i="0"; i < document.order.length; i++) {
				if(document.order.elements[i].type == "checkbox") {
					if (document.order.elements[i].id != null && document.order.elements[i].value != null) {
						//fix to work with addons					
						if (document.order.elements[i].name == 'addon_ids') {
							url = url+"&"+document.order.elements[i].id+"[]="+document.order.elements[i].value;
						} else {
							url = url+"&"+document.order.elements[i].id+"="+document.order.elements[i].checked;
						}
					}					
				} else {
					url = url+"&"+document.order.elements[i].id+"="+document.order.elements[i].value;
				}
				//alert(document.order.elements[i].id + ' - '. document.order.elements[i].value + ' - '. document.order.elements[i].checked);
			}
			
			//adding subdomain
			if (document.getElementById("domain").value == 'sub') {
				var subdomain = document.getElementById("csub2");			
				var subdomain_id = subdomain.options[subdomain.selectedIndex].value;
				url = url + "&csub2="+subdomain_id;
			}
			 
			document.getElementById("finished").innerHTML = working;
			document.getElementById("next").disabled = true;
			document.getElementById("back").disabled = true;
			//showing the signup code
			alert(url);
			$.get("<AJAX>"+url, function(data) {
				document.getElementById("finished").innerHTML = data;
				document.getElementById("back").disabled = false;
				document.getElementById("verify").innerHTML = "";				
				//Check if an invoice is generated
				$.get("<AJAX>?function=ispaid&pid="+ document.getElementById("package").value +"&uname="+ document.getElementById("username").value, function(invoice_id) {
					if(invoice_id != "") {
						window.location = "../client/?page=invoices&iid="+invoice_id;				
					}
				});
			});
			break;
	}
}

function final(hide, show) {
	document.getElementById("next").disabled = true;
	document.getElementById("back").disabled = true;
	document.getElementById("verify").innerHTML = ""
	$("#"+hide).fadeOut(1000, function() {
		document.getElementById("verify").innerHTML = "<strong>Don't close or browse away from this page!</strong>";
		$("#"+show).fadeIn(1000);
     });
}
function previousstep() {
	if(step != 1) {
		document.getElementById("next").disabled = true;
		document.getElementById("back").disabled = true;
		document.getElementById("verify").innerHTML = ""
		
		var newstep = step - 1;
		if (newstep == 3) {
			$.get("<AJAX>?function=orderIsUser", function(data) {
				if (data == "1") {
					newstep = 2
				}
			});
		}
		$("#"+step).fadeOut(1000, function() {
		step = newstep;
		$("#"+step).fadeIn(1000, function() {
			document.getElementById("next").disabled = false;
			if(step != "1") {
				document.getElementById("back").disabled = false;
			}
			if(step == "1") {
				document.getElementById("next").disabled = true;
				document.getElementById("order"+pid).disabled = false;
			}
										  });
		
     });
	}
}
function showAddons(obj) {	
//	step = step + 1;
	$("#verify").html('');
	var billing_id=obj.options[obj.selectedIndex].value;	
	$.get("<AJAX>?function=getAddons&billing_id="+billing_id +"&package_id="+document.getElementById("package").value, function(data) {
		document.getElementById("showaddons").innerHTML = data;
	});
															
}
</script>
<form action="" method="post" name="order" id="order">
<div>
	
	<div id="1">
    	<input name="package" id="package" type="hidden" value="" />
        <div class="table">
            <div class="cat">Step One - Choose Type/Package</div>
            <div class="text">
                <table width="100%" border="0" cellspacing="2" cellpadding="0">
                  <tr>
                    <td width="20%">Domain/Subdomain:</td>
                    <td>
                    	<select name="domain" id="domain">
                      		<option value="dom" selected="selected">Domain</option>
                      		%CANHASSUBDOMAIN%
                    	</select>
                    </td>
                    <td width="70%"><a title="Choose the type of hosting:<br /><strong>Domain:</strong> example.com<br /><strong>Subdomain:</strong> example.subdomain.com" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                  </tr>                  
                </table>
            </div>
        </div>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          %PACKAGES%          
        </table>
    </div>
    
	<!-- cambios por julio billing thing --> 
    <div class="table" id="2" style="display:none">
        <div class="cat">Select</div>
        <div class="text">
        	<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td colspan="2">
                	<div class="subborder">
                		<div class="sub" id="description">
                		Payment cycles                			
	              			<select name="billing_id" id="billing_id" onchange="showAddons(this)" >
	              			<option value="0" selected="selected">Select a billing cycle</option>         		
	                     		%BILLING_CYCLE%
	                    	</select>
              		  </div>
              		  <div id="showaddons"></div>
                    </div>
				</td>
              </tr>
            </table>
        </div>
    </div>
    
    <!-- cambios por julio  resumen --> 
    <div class="table" id="3" style="display:none">
        <div class="cat">Select</div>
        <div class="text">
        	<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td colspan="2">
                	<div class="subborder">
                		<div class="sub" id="description">
							<div id="show_summary"></div>					       
					   	</div>
              		</div>
				</td>
              </tr>
            </table>
        </div>
    </div>
    
    
    <div class="table" id="4" style="display:none">
        <div class="cat">Step Two - Terms of Service</div>
        <div class="text">
        	<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td colspan="2">
                	<div class="subborder">
                		<div class="sub" id="description">
                		%TOS%
                		</div>
                    </div>
				</td>
              </tr>
              <tr>
                <td width="330"><input name="agree" id="agree" type="checkbox" value="1" /> Do you agree to the <NAME> Terms of Service?</td>
                <td><a title="The Terms of Service is the set of rules you abide by. These must be agreed to." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
            </table>
        </div>
    </div>
    
	<div class="table" id="5" style="display:none">
        <div class="cat">Step Three - Client Account</div>
        <div class="text">
        	<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td>Username:</td>
                <td><input type="text" name="username" id="username" /></td>
                <td align="left"><a title="The username is your unique identity to your account. This is both your client account and control panel username. Please keep it under 8 characters." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td align="left" id="usercheck">&nbsp;</td>
              </tr>
              <tr>
                <td>Password:</td>
                <td><input type="password" name="password" id="password" onchange="check('pass', this.value+':'+document.getElementById('confirmp').value)"/></td>
                <td rowspan="2" align="left" valign="middle"><a title="Your password is your own personal key that allows only you to log you into your account." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td rowspan="2" align="left" valign="middle" id="passcheck">&nbsp;</td>
              </tr>
              <tr>
                <td>Confirm Password:</td>
                <td><input type="password" name="confirmp" id="confirmp" onchange="check('pass', this.value+':'+document.getElementById('password').value)"/></td>
              </tr>
              <tr>
                <td>Email:</td>
                <td><input type="text" name="email" id="email" onchange="check('email', this.value)" /></td>
                <td align="left"><a title="Your email is your own address where all <NAME> emails will be sent to. Make sure this is valid." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="emailcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>First Name:</td>
                <td><input type="text" name="firstname" id="firstname" onchange="check('firstname', this.value)" /></td>
                <td align="left"><a title="Your first name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="firstnamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lastname" id="lastname" onchange="check('lastname', this.value)" /></td>
                <td align="left"><a title="Your last name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="lastnamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Address:</td>
                <td><input type="text" name="address" id="address" onchange="check('address', this.value)" /></td>
                <td align="left"><a title="Your personal address." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="addresscheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>City:</td>
                <td><input type="text" name="city" id="city" onchange="check('city', this.value)" /></td>
                <td align="left"><a title="Your city. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="citycheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>State:</td>
                <td><input type="text" name="state" id="state" onchange="check('state', this.value)" /></td>
                <td align="left"><a title="Your state. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="statecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Zip Code:</td>
                <td><input type="text" name="zip" id="zip" onchange="check('zip', this.value)" /></td>
                <td align="left"><a title="Your zip/postal code. Numbers only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="zipcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Country:</td>
                <td>%COUNTRY_SELECT%</td>
                <td align="left"><a title="Your country." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="countrycheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Phone Number:</td>
                <td><input type="text" name="phone" id="phone" onchange="check('phone', this.value)" /></td>
                <td align="left"><a title="Your personal phone number. Numbers and dashes only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="phonecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td><img src="<URL>includes/captcha_image.php"></td>
                <td><input type="text" name="human" id="human" onchange="check('human', this.value)" /></td>
                <td align="left"><a title="Answer the question to prove you are not a bot." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="humancheck" align="left">&nbsp;</td>
              </tr>
            </table>
        </div>
    </div>
    <div class="table" id="6" style="display:none">
        <div class="cat">Step Four - Hosting Account</div>
        <div class="text">
        	<table width="100%" border="0" cellspacing="2" cellpadding="0">
              <tr id="dom">
                <td width="20%" id="domtitle">Domain:</td>
                <td width="78%" id="domcontent">%DOMAIN%</td>
                <td width="2%" align="left" id="domaincheck"><a title="Your domain, this must be in the format: <strong>example.com</strong>" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr id="sub">
                <td width="20%" id="domtitle">Subdomain:</td>
                
                <td id="domcontent"><input name="csub" id="csub" type="text" />.<span id="dropdownboxsub"></span></td>
                <td id="domaincheck" align="left">
                	<a title="Your subdomain, this must be in the format: <strong>subdomain.example.com</strong>" class="tooltip">
                		<img src="<URL>themes/icons/information.png" />
                	</a>
                </td>
              </tr>
            </table>
            <div id="custom">
            </div>
        </div>
    </div>
    <div class="table" id="7" style="display:none">
        <div class="cat">Step 5 - Create Account</div>
        <div class="text" id="creation">
        	<div id="finished">
            </div>
        </div>
    </div>
    <table width="100%" border="0" cellspacing="2" cellpadding="0" id="steps" style="display:none;">
      <tr>
        <td width="33%" align="center"><input type="button" name="back" id="back" value="Previous Step" onclick="previousstep()" disabled="disabled" /></td>
        <td width="33%" align="center" id="verify">&nbsp;</td>
        <td width="33%" align="center"><input type="button" name="next" id="next" value="Next Step" onclick="nextstep()" ondblclick="return false" /></td>
      </tr>
    </table>
</div>
</form>
