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
	if (document.getElementById("domain")) 
	if (document.getElementById("domain").value == "sub") {
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
								window.location = "../client/?page=invoices&iid="+invoice_id;				
							} else {
								window.location = "../client/?page=invoices";
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




	
<form action="" method="post" name="order" id="order">	
	<div id="1">
    	<input name="package" id="package" type="hidden" value="" />    	 
		%DOMAIN_CONFIGURATION%              
        <div class="row show-grid">
        %PACKAGES%
        </div>
    </div>
    
    <div id="2" class="table" style="display:none">
        <div class="page-header">
            <h2>_{Select a billing cycle}</h2>
        </div>
        
        <div class="text">
        	<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td colspan="2">
                	<div class="subborder">
                		<div class="sub" id="description">
                		_{Payment cycles}                			
	              			<select name="billing_id" id="billing_id" onchange="showAddons(this)" >
    	              			<option value="0" selected="selected">_{Select a billing cycle}</option>         		
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
        <div class="page-header">
            <h2>Summary</h2>
        </div>
        
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
        <div class="page-header">
            <h2>Terms of Service</h2>
        </div>
        
        <div class="container">
            %TOS%
        </div>
         <div class="container">
       	<label for="agree">
       	    <input name="agree" id="agree" type="checkbox" value="1" />
       	    <span>Do you agree to the <APP_NAME> Terms of Service?</span>            
        </label>
        </div>
        <a title="The Terms of Service is the set of rules you abide by. These must be agreed to." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
        
    </div>    
	<div class="table" id="5" style="display:none">
        <div class="page-header">
            <h2>Account Information</h2>
        </div>
        
        <div class="text">
            
            <div class="page-header">        
        	<h3>Already a registered?</h3>
        	</div>
            <a onclick="showLogin();" href="#">_{Log in to your account}</a>
               
            <div class="page-header">        
            <h3>New users</h3>
            </div>
            
        	<fieldset>
        	
            
        	<legend>Contact Info</legend>
        	
        	<div class="clearfix">
        	   <label>Username</label>
        	   <div class="input">
        	       <input type="text" name="username" id="username" maxlength="20" />
        	       <span id="usercheck"> </span>
        	       <span class="help-block">The username is your unique identity to your account. Please keep it between 8 and 20.</span>
        	   </div>        	   
        	</div>
        	
        	<div class="clearfix">
               <label>Password</label>
               <div class="input">
                   <input type="password" name="password" id="password" maxlength="40" onchange="check('pass', this.value+':'+document.getElementById('confirmp').value)"/>
                   <span id="passcheck"> </span>
                   <span class="help-block">Your password is your own personal key that allows only you to log you into your account.</span>
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Confirm Password</label>
               <div class="input">
                   <input type="password" name="confirmp" id="confirmp" maxlength="40" onchange="check('pass', this.value+':'+document.getElementById('password').value)"/>
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Email</label>
               <div class="input">
                   <input type="text" name="email" id="email" onchange="check('email', this.value)" />
                   <span id="emailcheck"> </span>
                   <span class="help-block">Your email is your own address where all <NAME> emails will be sent to. Make sure this is valid.</span>
               </div>              
            </div>
            
             <div class="clearfix">
               <label>First Name</label>
               <div class="input">
                   <input type="text" name="firstname" id="firstname" maxlength="40" onchange="check('firstname', this.value)" />
                   <span id="firstnamecheck"> </span>                   
               </div>              
            </div>
            
            
            <div class="clearfix">
               <label>Last Name</label>
               <div class="input">
                   <input type="text" name="lastname" id="lastname" maxlength="40" onchange="check('lastname', this.value)" />
                   <span id="lastnamecheck"> </span>                   
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Address</label>
               <div class="input">
                   <input type="text" name="address" id="address" onchange="check('address', this.value)" />
                   <span id="addresscheck"> </span>
               </div>              
            </div>
            
            
             <div class="clearfix">
               <label>City</label>
               <div class="input">
                   <input type="text" name="city" id="city" onchange="check('city', this.value)" />
                   <span id="citycheck"> </span>
                   <span class="help-block">Letters only.</span>
               </div>              
            </div>
            
             <div class="clearfix">
               <label>State</label>
               <div class="input">
                   <input type="text" name="state" id="state" onchange="check('state', this.value)" />
                   <span id="statecheck"> </span>
                   <span class="help-block">Letters only.</span>
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Zip Code</label>
               <div class="input">
                   <input type="text" name="zip" id="zip" onchange="check('zip', this.value)" />
                   <span id="zipcheck"> </span>
                   <span class="help-block">Numbers only.</span>
               </div>              
            </div>
            
             <div class="clearfix">
               <label>Country</label>
               <div class="input">
                   %COUNTRY_SELECT%
                   <span id="countrycheck"> </span>             
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Phone Number</label>
               <div class="input">
                   <input type="text" name="phone" id="phone" onchange="check('phone', this.value)" />
                   <span id="phonecheck"> </span>
                   <span class="help-block">Your personal phone number. Numbers and dashes only.</span>
               </div>              
            </div>
            
            <div class="clearfix">
               <label>Are you a human?</label>
                <img src="<URL>includes/captcha_image.php">
               <div class="input">
                  <input type="text" name="human" id="human" onchange="check('human', this.value)" />
                   <span id="humancheck"> </span>
                   <span class="help-block">Answer the question to prove you are not a bot.</span>
               </div>              
            </div>
 
			</fieldset>
			
            <fieldset>
            <legend>Company information (Optional)</legend>
            
                <div class="clearfix">
                   <label>Company</label>                    
                   <div class="input">
                      <input type="text" name="company" id="company" onchange="check('company', this.value)" />
                      <span id="companynamecheck"> </span>
                      <span class="help-block">Answer the question to prove you are not a bot.</span>
                   </div>              
                </div>
                
                 <div class="clearfix">
                   <label>Tax ID (VAT)</label>                    
                   <div class="input">
                      <input type="text" name="vatid" id="vatid" onchange="check('vatid', this.value)" />
                      <span id="vatid"> </span>
                      <span class="help-block">Company Tax registration number (VAT ID)</span>
                   </div>              
                </div>
                
                 <div class="clearfix">
                   <label>SSN/Fiscal ID</label>                    
                   <div class="input">
                      <input type="text" name="fiscalid" id="fiscalid" onchange="check('fiscalid', this.value)" />
                      <span id="fiscalid"> </span>
                      <span class="help-block">Your SSN or Fiscal ID</span>
                   </div>              
                </div>       
           </fieldset>
        </div>
    </div>
    <div class="table" id="6" style="display:none">        
        <div class="page-header">
            <h2>Hosting Account</h2>
        </div>        
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
        <div class="page-header">
            <h2>Setting your account</h2>
        </div>  
        
        <div class="text" id="creation">
        	<div id="finished">
            </div>
        </div>
    </div>
    
    
    <div id="verify" class="container" ></div>
    
    <div id="steps" class="row show-grid" style="display:none;" >
        <div class="actions">            
            <input type="button" name="next" id="next" value="_{Continue}" onclick="nextstep()" ondblclick="return false;" class="btn large primary "  />                    
            <input type="button" name="back" id="back" value="_{Previous Step}" onclick="previousstep()" disabled="disabled" class="btn small" />                          
        </div>
    </div>     
</form>