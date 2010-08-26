<script type="text/javascript" src="<URL>includes/javascript/jquery.validate.js"></script>
<script type="text/javascript">


jQuery.validator.addMethod("UsernameExists", 
		function(value, element) {
			var username_value = $("#user").val();	
			$.ajax({
				url:"<AJAX>function=usernameExists&user="+username_value,
				async:false,
				type: "GET",
				success:  function(data) {
					result = (data=='0') ? true : false;				
				}
			});
			return result;			
	});

jQuery.validator.addMethod("validateUsername", 
	function(value, element) {
		var username_value = $("#user").val();	
		$.ajax({
			url:"<AJAX>function=validateUserName&user="+username_value,
			async:false,
			type: "GET",
			success:  function(data) {
				result = (data=='0') ? true : false;				
			}
		});
		return result;			
});


$(function() {
	$("#edituser").validate(%json_encode%);
});



function check(name, value) {
	$("#"+name+"check").html(loading);
	/* document.getElementById("next").disabled = true; */
	window.setTimeout(function() {
		$.get("<AJAX>function="+name+"check&THT=1&"+name+"="+value, function(data) {
			if(data == "1") {
				$("#"+name+"check").html(right);
			}
			else {
				$("#"+name+"check").html(wrong);
			}													
			/* document.getElementById("next").disabled = false; */
		});
	},500);
}
var wrong = '<img src="<URL>themes/icons/cross.png">';
var right = '<img src="<URL>themes/icons/accept.png">';
var loading = '<img src="<URL>themes/icons/ajax-loader.gif">';
var working = '<div align="center"><img src="<URL>themes/icons/working.gif"></div>';



</script>

<ERRORS>
<form id="edituser" name="edituser" method="post" action="">
<table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td>Username:</td>
                <td><input type="text" name="user" id="user" value="%user%"/></td>
                <td align="left"><a title="The username is your unique identity to your account. This is both your client account and control panel username. Please keep it under 8 characters." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td align="left" id="usercheck">&nbsp;</td>
              </tr>
              <tr>
              <!--   <td>Password:</td>
                <td><input type="password" name="password" id="password" /></td>
                <td rowspan="2" align="left" valign="middle"><a title="Your password is your own personal key that allows only you to log you into your account." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td rowspan="2" align="left" valign="middle" id="passcheck">&nbsp;</td>
              </tr>
              <tr>
                <td>Confirm Password:</td>
                <td><input type="password" name="confirmp" id="confirmp" onchange="check('pass', this.value+':'+document.getElementById('password').value)"/></td>
              </tr> -->
              <tr>
                <td>Email:</td>
                <td><input type="text" name="email" id="email" value="%email%" onchange="check('email', this.value)" /></td>
                <td align="left"><a title="Your email is your own address where all <NAME> emails will be sent to. Make sure this is valid." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="emailcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>First Name:</td>
                <td><input type="text" name="firstname" id="firstname"  value="%firstname%" onchange="check('firstname', this.value)" /></td>
                <td align="left"><a title="Your first name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="firstnamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lastname" id="lastname" value="%lastname%" onchange="check('lastname', this.value)" /></td>
                <td align="left"><a title="Your last name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="lastnamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Company:</td>
                <td><input type="text" name="company" id="company" value="%company%" onchange="check('company', this.value)" /></td>
                <td align="left"><a title="Company name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="companynamecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>VAT ID:</td>
                <td><input type="text" name="vatid" id="vatid" value="%vatid%" onchange="check('vatid', this.value)" /></td>
                <td align="left"><a title="Company VAT id." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="vatid" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>SSN/Fiscal ID:</td>
                <td><input type="text" name="fiscalid" id="fiscalid" value="%fiscalid%" onchange="check('lastname', this.value)" /></td>
                <td align="left"><a title="Fiscal ID or SSN." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="fiscalidcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Address:</td>
                <td><input type="text" name="address" id="address" value="%address%"  onchange="check('address', this.value)" /></td>
                <td align="left"><a title="Your personal address." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="addresscheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>City:</td>
                <td><input type="text" name="city" id="city" value="%city%" onchange="check('city', this.value)" /></td>
                <td align="left"><a title="Your city. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="citycheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>State:</td>
                <td><input type="text" name="state" id="state" value="%state%" onchange="check('state', this.value)" /></td>
                <td align="left"><a title="Your state. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="statecheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Zip Code:</td>
                <td><input type="text" name="zip" id="zip" value="%zip%" onchange="check('zip', this.value)" /></td>
                <td align="left"><a title="Your zip/postal code. Numbers only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="zipcheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Country:</td>
                <td>%country%</td>
                <td align="left"><a title="Your country." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="countrycheck" align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>Phone Number:</td>
                <td><input type="text" name="phone" id="phone" value="%phone%" onchange="check('phone', this.value)" /></td>
                <td align="left"><a title="Your personal phone number. Numbers and dashes only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td id="phonecheck" align="left">&nbsp;</td>
              </tr>
              
              <tr>
                <td>Status:</td>
                <td>%STATUS%</td>
                <td align="left"><a title="User status" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>                
              </tr>
              
              
              <tr>
    			<td colspan="2" align="center"><input type="submit" name="edit" id="edit" value="Edit client" /></td>
    			</tr>
      
            </table>
</form>            
            
