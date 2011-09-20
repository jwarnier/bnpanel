<script type="text/javascript">
var step 	= 1;
var speed 	= 250; //default 1000
var form 	= document.getElementById("order");
var wrong 	= '<img src="{$url}themes/icons/cross.png">';
var right 	= '<img src="{$url}themes/icons/accept.png">';
var loading = '<img src="{$url}themes/icons/ajax-loader.gif">';
var working = '<div align="center"><img src="{$url}themes/icons/working.gif"></div>';
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
	
	$.get("{$ajax}function="+name+"check&"+name+"="+value, function(data) {
		if(data == "1") {
			$("#"+name+"check").html(right);
		}
		else {
			$("#"+name+"check").html(wrong);
		}													
		document.getElementById("next").disabled = false;
	});
	
}


function orderstepme(id, type) {
	pid = id;
	document.getElementById("package").value = id;
	document.getElementById("order"+id).disabled = true;
	if (document.getElementById("domain")) 
	if (document.getElementById("domain").value == "sub") {
		document.getElementById("dom").style.display = 'none';
		document.getElementById("sub").style.display = '';		
		$.get("{$ajax}function=sub&pack="+document.getElementById("package").value, function(data) {
			document.getElementById("dropdownboxsub").innerHTML = data;
		});
	} else if(document.getElementById("domain").value == "dom") {
		document.getElementById("sub").style.display = 'none';
		document.getElementById("dom").style.display = '';		
		document.getElementById("sub3").style.display = 'none';
	}
	
	$.get('{$ajax}function=orderForm&package='+ document.getElementById("package").value, function(stuff) {
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
	
	$("#"+hide).fadeOut(speed, function() {
		$("#steps").fadeIn(speed);	
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
				$.get("{$ajax}function=getSummary&billing_id="+billing_id +"&addon_list="+addon_list +"&package_id="+document.getElementById("package").value, function(data) {
					document.getElementById("show_summary").innerHTML = data;
				});				
			} else  {
				$("#verify").html("<div class='alert-message info'>You must select a Billing Cycle</div>");
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
				$.get("{$ajax}function=userIsLogged", function(data) {
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
				$("#verify").html("<div class='alert-message info'>You must agree the Terms of Service</div>");
			}			
			break;			
		case 5:					
			//User form
			$.get("{$ajax}function=clientcheck", function(data) {
				if(data == "1") {	
					if (document.getElementById("username").value != '' ) {
						document.getElementById("verify").innerHTML = right;
						$("#next").val('Pay now');						
						showhide(step, step + 1)
						step = step + 1;						
					} else {
						$("#verify").html("<div class='alert-message info'>You must fill all the fields</div>");
					}	
				} else {
					$("#verify").html("<div class='alert-message info'>You must fill all the fields</div>");
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
					$("#verify").html("<strong>You must fill all the fields</strong>");
					break;
				}
				if (subdomain_id == '' ) {
					$("#verify").html("<strong>You must select a domain</strong>");
					break;
				}
				final_domain = subdomain;
				// var subdomain_id = subdomain.options[subdomain.selectedIndex].value;
			} else {
				var subdomain_id 	= '';
				var subdomain       = '';
				if (final_domain == '') {
					$("#verify").html("<strong>You must fill a domain name</strong>");
					break;
				}
			}
			
			$.get("{$ajax}function=checkSubDomainExists&domain="+domain_id+"&package_id="+package_id +"&final_domain="+final_domain+"&subdomain_id="+subdomain_id,  function(data) {							
				if (data == '1') {
					$("#verify").html("<strong>Domain already exists</strong>");					
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
					$.get("{$ajax}"+url, function(data) {
						document.getElementById("finished").innerHTML = data;
						
						document.getElementById("verify").innerHTML = "";				
						//Check if an invoice is generated
						$.get("{$ajax}function=ispaid", function(invoice_id) {
							if(invoice_id != "") {
								window.location = "../client/?page=invoices&iid="+invoice_id;				
							} else {
								window.location = "../client/?page=invoices";
							}
							
						});
					});
				} else {
					$("#verify").html("<strong>Seems that you took a lot of time to decide...</strong>");		
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
			$.get("{$ajax}function=userIsLogged", function(data) {
				if (data == "1") {					
					newstep = 2
				}
			});
		} else if (newstep == 5) {
			
			$.get("{$ajax}function=userIsLogged", function(data) {
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
	$.get("{$ajax}function=getAddons&billing_id="+billing_id +"&package_id="+document.getElementById("package").value, function(data) {
		document.getElementById("showaddons").innerHTML = data;
	});															
}

function checkDomain() {
	var domain = $("#cdom").val();
	$.get("{$ajax}function=validateDomain&domain="+domain,  function(data) {
		if (data == '1') {
			$("#domain_result").html("<strong>Wrong domain format </strong> ");	
		} else {	
			$.get("{$ajax}function=checkSubDomainExistsSimple&domain="+domain+"&subdomain_id=0",  function(data2) {							
				if (data2 == '1') {
					$("#domain_result").html("<strong>Domain already exists</strong> ");	
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
			$.get("{$ajax}function=checkSubDomainExists&domain="+domain_id+"&package_id="+package_id +"&final_domain="+final_domain+"&subdomain_id="+subdomain_id,  function(data) {							
				if (data == '1') {
					$("#subdomain_result").html("<strong>Subdomain already exists</strong> ");	
				} else {
					$("#subdomain_result").html("<strong>Subdomain available</strong> "+right);
				}
			});
		}
	} else {
		$("#subdomain_result").html("<strong>Select a domain first</strong>");
	}			
}
</script>