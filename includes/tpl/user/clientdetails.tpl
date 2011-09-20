<div class="page-header">
<h2>{t}User information{/t}</h2>
</div>

<div class="subborder">
	<div class="sub">
	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
	    <tr>
	      <td width="50%">Username:</td>
	      <td align="right">{$USER}
	      <a title="Their username." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
	    <tr>
	      <td width="50%">Email Address:</td>
	      <td align="right">{$EMAIL}
	      <a title="Their email address." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
	    <tr>
	      <td width="50%">Signup Date:</td>
	      <td align="right">{$DATE} <a title="Their signup date for <NAME>" class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
	      <td width="50%">User Status:</td>
	      <td align="right">{$STATUS}
	      <a title="The status of the user." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
          <td width="50%">Client IP:</td>
          <td align="right"><a href="http://whois.domaintools.com/{$CLIENTIP}" target="_blank">{$CLIENTIP}</a>
          <a title="The IP used during registration." class="tooltip">
          <img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
          <td width="50%">First Name:</td>
          <td align="right">{$FIRSTNAME}</a>
          <a title="User's first name." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
          <td width="50%">Last Name:</td>
          <td align="right">{$LASTNAME}</a>
          <a title="User's last name." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>    
        <tr>
          <td width="50%">Address:</td>
          <td align="right">{$ADDRESS}</a>
          <a title="User's address." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
     
        <tr>
          <td width="50%">City:</td>
          <td align="right">{$CITY}</a>
          <a title="User's city." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
     
        <tr>
          <td width="50%">State:</td>
          <td align="right">{$STATE}</a>
          <a title="User's state." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
    
        <tr>
          <td width="50%">Zip Code:</td>
          <td align="right">{$ZIP}</a>
          <a title="User's zip code." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
    
        <tr>
          <td width="50%">Country:</td>
          <td align="right"><img src="{$url}themes/flags/{$COUNTRY}.gif" /></td>
        </tr>  
        <tr>
          <td width="50%">Phone:</td>
          <td align="right">{$PHONE}</a>
          <a title="User's phone number." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
      </table>
    </div>
</div>

<div class="page-header">
	<h2>{t}Company information (Optional){/t}</h2>
</div>		
	
<div class="subborder">
    <div class="sub">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="50%">Company:</td>
          <td align="right">{$COMPANY}</a>
          <a title="Company name" class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
          <td width="50%">Vat ID:</td>
          <td align="right">{$VATID}</a>
          <a title="Company VAT ID" class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
        <tr>
          <td width="50%">Fiscal ID</td>
          <td align="right">{$FISCALID}</a>
          <a title="User's SSN/Fiscal ID" class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
        </tr>
      </table>
    </div>
</div>
</fieldset>	