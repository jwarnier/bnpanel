{include file="orderform/order_js.tpl"}
<div id="ajaxwrapper" class="row">
    <div class="span16">
    
	<form action="" method="post" name="order" id="order">	
		<div id="1" class="row">
	    	<input name="package" id="package" type="hidden" value="" />    	 
			{$DOMAIN_CONFIGURATION}              
	        <div class="span16 show-grid">
	           {$PACKAGES}
	        </div>
	    </div>
	    
	    <div id="2" class="row" style="display:none">
	        <div class="page-header">
	            <h2>{t}Select a billing cycle{/t}</h2>
	        </div>        
	        <div class="span16">
	            <div class="sub" id="description">
	                {t}Payment cycles{/t}                			
	      			<select name="billing_id" id="billing_id" onchange="showAddons(this)" >
	          			<option value="0" selected="selected">{t}Select a billing cycle{/t}</option>         		
	                 		{$BILLING_CYCLE}
	                </select>
	            </div>              		
	        </div>
	        
	        <div class="span16">
	            <div id="showaddons"></div>
	        </div>	        
	    </div>
	
	    <div id="3" class="row" style="display:none">
	        <div class="page-header">
	            <h2>Summary</h2>
	        </div>
	        
	        <div class="span16">
	            <div class="sub" id="description">
	                <div id="show_summary"></div>
	            </div>			
	        </div>
	    </div>   
	    
	    <div id="4" class="row" style="display:none">        
	        <div class="page-header">
	            <h2>Terms of Service</h2>
	        </div>
	        
	        <div class="container">
	            {$TOS}
	        </div>
	         <div class="container">
	            <ul class="inputs-list">
	                <li>
				       	<label for="agree">
				       	    <input name="agree" id="agree" type="checkbox" value="1" />
				       	    <span>Do you agree to the <APP_NAME> Terms of Service?</span>            
				        </label>
				    </li>
	            </ul>
	        </div>
	        <a title="The Terms of Service is the set of rules you abide by. These must be agreed to." class="tooltip">
	    </div>    
		<div id="5" class="row" style="display:none">
	        <div class="page-header">
	            <h2>Account Information</h2>
	        </div>
	        
	        <div class="span16">
	            
	            <div class="page-header">        
		        	<h3>Already a registered?</h3>
		        	</div>
		            <a onclick="showLogin();" href="#">{t}Log in to your account{/t}</a>
		            <br /> <br />
		               
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
	                   {$COUNTRY_SELECT}
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
	               
	                <div class="input">
	                    <img src="{$url}includes/captcha_image.php"><br /><br />
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
	    
	    <div id="6" class="row" style="display:none">        
	        <div class="page-header">
	            <h2>Hosting Account</h2>
	        </div>        
	        <div class="span16">
	        	<table width="100%" border="0" cellspacing="2" cellpadding="0">
	              <tr id="dom">
	                <td width="20%" id="domtitle">Domain:</td>
	                <td width="78%" id="domcontent">{$DOMAIN} <span id="domain_result"></span></td>
	                <td width="2%" align="left" id="domaincheck">
	                	<a title="Your domain, this must be in the format: <strong>example.com</strong>" class="tooltip">
	                	<img src="{$url}themes/icons/information.png" /></a>
	                </td>
	              </tr>
	              
	              <tr id="sub">              
	                <td width="20%" id="domtitle">Domain:</td>                
	                <td id="domcontent">
	                	<span id="dropdownboxsub"></span>                	
	                </td>
	                <td id="domaincheck" align="left">
	                	<a title="Your domain name" class="tooltip">
	                		<img src="{$url}themes/icons/information.png" />
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
	                		<img src="{$url}themes/icons/information.png" />
	                	</a>
	                </td>                
	              </tr>       
	            </table>
	            
	            <div id="custom"></div>
	        </div>
	    </div>
	    <div class="row" id="7" style="display:none">        
	        <div class="page-header">
	            <h2>Setting your account</h2>
	        </div>          
	        <div class="row" id="creation">
	        	<div id="finished"></div>
	        </div>
	    </div>
	    
	    
	    <div id="verify" class="container" ></div>
	    
	    <div id="steps" class="row show-grid" style="display:none;" >
	        <div class="actions">            
	            <input type="button" name="next" id="next" value="{t}Continue{/t}" onclick="nextstep()" ondblclick="return false;" class="btn large primary "  />                    
	            <input type="button" name="back" id="back" value="{t}Previous Step{/t}" onclick="previousstep()" disabled="disabled" class="btn small" />                          
	        </div>
	    </div>     
	</form>
	</div>
</div>