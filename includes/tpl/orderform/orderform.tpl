<script type="text/javascript">
var step 	= 1;
var speed 	= 250; //default 1000
var form 	= document.getElementById("order");
var wrong 	= '<img src="<URL>themes/icons/cross.png">';
var right 	= '<img src="<URL>themes/icons/accept.png">';
var loading = '<img src="<URL>themes/icons/ajax-loader.gif">';
var working = '<div align="center"><img src="<URL>themes/icons/working.gif"></div>';
var result;
var pid;

$(document).ready(function(){	
   $("#username").change(function(event) {
	   this.value = this.value.toLowerCase();
	   check('user', this.value);
   });   
   
   //Modal login options see http://jqueryui.com/demos/dialog/#animated for more information
   $('#login_form').dialog({
		autoOpen: 	false, //Avoid dialog problem
		modal: 		true,
		draggable: 	false,
		resizable:	false,
		height:		'auto',
		position: 	'center'
			
	});   
});

function stopRKey(evt) { 
  var evt = (evt) ? evt : ((event) ? event : null); 
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
  if ((evt.keyCode == 13) && (node.type=="text")) {
	  return false;
	} 
} 

document.onkeypress = stopRKey;

function check(name, value) {
	$("#"+name+"check").html(loading);
	document.getElementById("next").disabled = true;
	window.setTimeout(function() {
		$.get("<AJAX>function="+name+"check&THT=1&"+name+"="+value, function(data) {
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
		$.get("<AJAX>function=sub&pack="+document.getElementById("package").value, function(data) {
			document.getElementById("dropdownboxsub").innerHTML = data;
		});
	} else if(document.getElementById("domain").value == "dom") {
		document.getElementById("sub").style.display = 'none';
		document.getElementById("dom").style.display = '';		
		document.getElementById("sub3").style.display = 'none';
	}	
	$.get('<AJAX>function=orderForm&package='+ document.getElementById("package").value, function(stuff) {
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
	/*$("#next").hide();
	$("#back").hide();*/
	document.getElementById("next").disabled = true;
	document.getElementById("back").disabled = true;
	document.getElementById("verify").innerHTML = "";
	
	$("#"+hide).fadeOut(speed, function() {
		$("#steps").fadeIn(speed);
		//$("#next").fadeIn(speed);
		//$("#back").fadeIn(speed);
		$("#"+show).fadeIn(speed, function() {
			document.getElementById("next").disabled = false;
			document.getElementById("back").disabled = false;
			
		});
     });
		
}

function login() {
	var user = $("#user_login").val();
	var pass = $("#pass_login").val();	
	$.get("<AJAX>function=clientLogin&user="+user+"&pass="+pass, function(data) {
		if (data != '') {
			if (data == 1) {
				if (step == '5') {					
					showhide(step, step + 1)
					step = step + 1;						
				}
				$.get("<AJAX>function=getNavigation", function(data2) {
					$("#welcome").html(data2);	
				});			
				$("#login_form").dialog('close');			
			} else {
				alert('Please, try again');
			}
		}		
	});		
}

function showLogin() {	
	$("#login_form").dialog('open');
}

function nextstep() {
	//alert(step);
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
						if(document.order.addon_ids.checked) {			
							addon_list  = document.order.addon_ids.value;
						}											
					} else {				
						for (var i=0; i < document.order.addon_ids.length; i++) {	
							//alert(document.order.addon_ids[i].checked);
							if (document.order.addon_ids[i].checked) {
						   		if (document.order.addon_ids[i].value != 'undefined' ) { 
						      		addon_list = addon_list + document.order.addon_ids[i].value + '-';
								}				      
							}
						}	
						//alert(addon_list);					
					}
				}				
				var billing_id  = document.getElementById("billing_id").value;
				showhide(step, step + 1);
				step = step + 1;		
				$.get("<AJAX>function=getSummary&billing_id="+billing_id +"&addon_list="+addon_list +"&package_id="+document.getElementById("package").value, function(data) {
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
			break;			
		case 4:
			//TOS
			if(document.getElementById("agree").checked == true) {							
				$.get("<AJAX>function=userIsLogged", function(data) {
					if (data == "1") {
						showhide(step, step + 2);
						step = step + 2;
						$("#next").val('Pay now');						
					} else {	
						//alert('here');
						$("#login_form").show();					
						showhide(step, step + 1);
						step = step + 1;
					}
				});
			} else {
				$("#verify").html("<strong>You must agree the Terms of Service</strong> "+wrong);
			}			
			break;			
		case 5:					
			//User form
			$.get("<AJAX>function=clientcheck", function(data) {
				if(data == "1") {	
					if (document.getElementById("username").value != '' ) {
						document.getElementById("verify").innerHTML = right;
						$("#next").val('Pay now');						
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
			//adding subdomain			
			var domain_id 	= document.getElementById("domain").value;
			var package_id 	= document.getElementById("package").value;
			var final_domain= document.getElementById("cdom").value;

					
			if (domain_id == 'sub') { //this is a subdomain
				var subdomain_id 	= document.getElementById("csub2").value;
				var subdomain		= document.getElementById("csub").value;
				
				if (subdomain == '') {
					$("#verify").html("<strong>You must fill all the fields</strong> "+wrong);
					break;
				}
				if (subdomain_id == '' ) {
					$("#verify").html("<strong>You must select a domain</strong> "+wrong);
					break;
				}
				final_domain = subdomain;
				// var subdomain_id = subdomain.options[subdomain.selectedIndex].value;
			} else {
				var subdomain_id 	= '';
				var subdomain       = '';
				if (final_domain == '') {
					$("#verify").html("<strong>You must fill a domain name</strong> "+wrong);
					break;
				}
			}
			
			$.get("<AJAX>function=checkSubDomainExists&domain="+domain_id+"&package_id="+package_id +"&final_domain="+final_domain+"&subdomain_id="+subdomain_id,  function(data) {							
				if (data == '1') {
					$("#verify").html("<strong>Domain already exists</strong> "+wrong);					
				} else if(data == '0') {
					final(step, step + 1);
					step = step + 1
					var url = "function=create";
					var i;					
					for(i="0"; i < document.order.length; i++) {
						if(document.order.elements[i].type == "checkbox") {
							if (document.order.elements[i].id != null && document.order.elements[i].value != null) {
								//alert(document.order.elements[i].name);
								//fix to work with addons					
								if (document.order.elements[i].name == 'addon_ids') {
									if (document.order.elements[i].checked) {								
										url = url+"&"+document.order.elements[i].id+"[]="+document.order.elements[i].value;
									}	
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

					//Remove both buttons
					$("#next").hide();
					$("#back").hide();
					
					//showing the signup code
					//alert(url);
					$.get("<AJAX>"+url, function(data) {
						document.getElementById("finished").innerHTML = data;
						
						document.getElementById("verify").innerHTML = "";				
						//Check if an invoice is generated
						$.get("<AJAX>function=ispaid", function(invoice_id) {
							if(invoice_id != "") {
								//window.location = "../client/?page=invoices&iid="+invoice_id;				
							} else {
								//window.location = "../client/?page=invoices";
							}
							
						});
					});
				} else {
					$("#verify").html("<strong>Seems that you took a lot of time to decide...</strong> "+wrong);		
				}			
			});			
			break;
	}
}

function final(hide, show) {
	document.getElementById("next").disabled = true;
	document.getElementById("back").disabled = true;
	document.getElementById("verify").innerHTML = ""
	$("#"+hide).fadeOut(speed, function() {
		document.getElementById("verify").innerHTML = "<strong>Don't close or browse away from this page!</strong>";
		$("#"+show).fadeIn(speed);
     });
}
function previousstep() {
	//alert(step);
	
	$("#next").val('Next Step');
	
	if (step == 2 ) {
		$("#steps").hide();
	}	
	if(step != 1) {
		document.getElementById("next").disabled = true;
		document.getElementById("back").disabled = true;
		
		document.getElementById("verify").innerHTML = ""
		
		var newstep = step - 1;
		
		if (newstep == 3) {
			$.get("<AJAX>function=userIsLogged", function(data) {
				if (data == "1") {					
					newstep = 2
				}
			});
		} else if (newstep == 5) {
			
			$.get("<AJAX>function=userIsLogged", function(data) {
				if (data == "1") {					
					newstep = 4;
				}
			});
		}
		$("#"+step).fadeOut(speed, function() {
			step = newstep;
			$("#"+step).fadeIn(speed, function() {
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
	$("#verify").html('');
	var billing_id=obj.options[obj.selectedIndex].value;	
	$.get("<AJAX>function=getAddons&billing_id="+billing_id +"&package_id="+document.getElementById("package").value, function(data) {
		document.getElementById("showaddons").innerHTML = data;
	});															
}

function checkDomain() {
	var domain = $("#cdom").val();
	$.get("<AJAX>function=validateDomain&domain="+domain,  function(data) {
		if (data == '1') {
			$("#domain_result").html("<strong>Wrong domain format </strong> "+wrong);	
		} else {	
			$.get("<AJAX>function=checkSubDomainExistsSimple&domain="+domain+"&subdomain_id=0",  function(data2) {							
				if (data2 == '1') {
					$("#domain_result").html("<strong>Domain already exists</strong> "+wrong);	
				} else {
					$("#domain_result").html("<strong>Domain available</strong> "+right);
				}
			});
			
		}		
	});
}

function checkSubdomain() {
	//adding subdomain			
	var domain_id 	= document.getElementById("domain").value;
	var package_id 	= document.getElementById("package").value;
	var final_domain= document.getElementById("cdom").value;
			
	if (domain_id == 'sub') { //this is a subdomain
		var subdomain_id 	= document.getElementById("csub2").value;
		var subdomain		= document.getElementById("csub").value;	
		final_domain = subdomain;

	} else {
		var subdomain_id 	= '';
		var subdomain       = '';
	} 
	
	$("#verify").html(''); //Cleaning the verify
	 
	if (subdomain_id != '') {
		if (final_domain != '') {
			$.get("<AJAX>function=checkSubDomainExists&domain="+domain_id+"&package_id="+package_id +"&final_domain="+final_domain+"&subdomain_id="+subdomain_id,  function(data) {							
				if (data == '1') {
					$("#subdomain_result").html("<strong>Subdomain already exists</strong> "+wrong);	
				} else {
					$("#subdomain_result").html("<strong>Subdomain available</strong> "+right);
				}
			});
		}
	} else {
		$("#subdomain_result").html("<strong>Select a domain first</strong> "+wrong);
	}
			
}

</script>
<div class="box">

	<span id="welcome" class="welcome">
	%WELCOME_MESSAGE%
	</span>
	<div style="clear:both"></div>
	
<form action="" method="post" name="order" id="order">	
	<div id="1">
    	<input name="package" id="package" type="hidden" value="" /> 
		%DOMAIN_CONFIGURATION%              
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          %PACKAGES%          
        </table>
    </div>    
    <div class="table" id="2" style="display:none">
        <div class="cat"><span class="cat_title">Select a billing cycle</span></div>
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
        <div class="cat"><span class="cat_title">Summary</span></div>
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
        <div class="cat"><span class="cat_title">Terms of Service</span></div>
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
                <td width="330">
                	<label for="agree">
               			<input name="agree" id="agree" type="checkbox" value="1" /> Do you agree to the <NAME> Terms of Service?
                	</label>
                </td>
                <td><a title="The Terms of Service is the set of rules you abide by. These must be agreed to." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
            </table>
        </div>
    </div>    
	<div class="table" id="5" style="display:none">
        <div class="cat"><span class="cat_title">Client Account</span></div>
        <div class="text">        
        	<table  class="data_table">
        	<tr>
        	<td>
        	
        	<fieldset>
        	<legend>
        		User information
        	</legend>
        	<table class="data_table" border="0" cellspacing="2" cellpadding="0" align="center" style="width: 400px;">
              <tr>
                <td>Username:</td>
                <td><input type="text" name="username" id="username" maxlength="20" /></td>
                <td align="left"><a title="The username is your unique identity to your account. Please keep it between 8 and 20." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td align="left" id="usercheck">&nbsp;</td>
              </tr>
              <tr>
                <td>Password:</td>
                <td><input type="password" name="password" id="password" maxlength="40" onchange="check('pass', this.value+':'+document.getElementById('confirmp').value)"/></td>
                <td rowspan="2" align="left" valign="middle"><a title="Your password is your own personal key that allows only you to log you into your account." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td rowspan="2" align="left" valign="middle" id="passcheck">&nbsp;</td>
              </tr>
              <tr>
                <td>Confirm Password:</td>
                <td><input type="password" name="confirmp" id="confirmp" maxlength="40" onchange="check('pass', this.value+':'+document.getElementById('password').value)"/></td>
              </tr>
              <tr>
                <td>Email:</td>
                <td><input type="text" name="email" id="email" onchange="check('email', this.value)" /></td>
                <td align="left"><a title="Your email is your own address where all <NAME> emails will be sent to. Make sure this is valid." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="emailcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>First Name:</td>
                <td><input type="text" name="firstname" id="firstname" maxlength="40" onchange="check('firstname', this.value)" /></td>
                <td align="left"><a title="Your first name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="firstnamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lastname" id="lastname" maxlength="40" onchange="check('lastname', this.value)" /></td>
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
			</fieldset>
			<br />
            <fieldset>
            <legend>
        		Company information (Optional)
        	</legend>
            <table class="data_table" border="0" cellspacing="2" cellpadding="0" align="center" style="width: 400px;">
              <tr>
                <td>Company:</td>
                <td><input type="text" name="company" id="company" onchange="check('company', this.value)" /></td>
                <td align="left"><a title="Your company name" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="companynamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Tax ID (VAT):</td>
                <td><input type="text" name="vatid" id="vatid" onchange="check('vatid', this.value)" /></td>
                <td align="left"><a title="Company Tax registration number (VAT ID)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="vatid" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>SSN/Fiscal ID</td>
                <td><input type="text" name="fiscalid" id="fiscalid" onchange="check('fiscalid', this.value)" /></td>
                <td align="left"><a title="Your SSN or Fiscal ID" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="fiscalid" align="left">&nbsp;</td>
               </tr>
            </table>
           </fieldset>   
           
           </td>
           <td width="50px" align="center">
           Or
           </td>
           <td>
           <div class="big_title">
				<a onclick="showLogin();" href="#">Log in to your account</a>
			</div>				
           %LOGIN_TPL%           
           </td>
           </tr> 
           </table>            
        </div>
    </div>
    <div class="table" id="6" style="display:none">
        <div class="cat"><span class="cat_title">Hosting Account</span></div>
        <div class="text">
        	<table width="100%" border="0" cellspacing="2" cellpadding="0">
              <tr id="dom">
                <td width="20%" id="domtitle">Domain:</td>
                <td width="78%" id="domcontent">%DOMAIN% <span id="domain_result"></span></td>
                <td width="2%" align="left" id="domaincheck">
                	<a title="Your domain, this must be in the format: <strong>example.com</strong>" class="tooltip">
                	<img src="<URL>themes/icons/information.png" /></a>
                </td>
              </tr>
              
              <tr id="sub">              
                <td width="20%" id="domtitle">Domain:</td>                
                <td id="domcontent">
                	<span id="dropdownboxsub"></span>                	
                </td>
                <td id="domaincheck" align="left">
                	<a title="Your domain name" class="tooltip">
                		<img src="<URL>themes/icons/information.png" />
                	</a>
                </td>                
              </tr>
 
			<tr id="sub3">              
                <td width="20%" id="domtitle">Subdomain:</td>                
                <td id="domcontent">
                	<input name="csub" id="csub" type="text" maxlength="40" onkeyup="checkSubdomain();" />
                	<span id="subdomain_result"></span>
                </td>
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
        <div class="cat"><span class="cat_title">Setting your account</span></div>
        <div class="text" id="creation">
        	<div id="finished">
            </div>
        </div>
    </div>
    <table width="100%" border="0" cellspacing="2" cellpadding="0" id="steps" style="display:none;">
      <tr>
        <td width="33%" align="center">
        	<input type="button" name="back" id="back" value="Previous Step" onclick="previousstep()" disabled="disabled" /></td>
        <td width="33%" align="center" id="verify">&nbsp;</td>
        <td width="33%" align="center"><input type="button" name="next" id="next" value="Next Step" onclick="nextstep()" ondblclick="return false" /></td>
      </tr>
    </table>
</form>
</div>