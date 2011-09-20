<div class="page-header">
	<h2>Order #{$ID}</h2>
</div>
<table class="common-table zebra-striped"> 
	<tr>
    	<td >User:</td>
    	<td>  
    		<a href="?page=users&sub=search&do={$USER_ID}">{$USER}</a>
    	</td>
	</tr> 
	<tr>
	    <td >Domain:</td>
	    <td>   
	   		<a target="_blank" href="http://{$REAL_DOMAIN}">{$REAL_DOMAIN}</a>
	    </td>
  	</tr>  
      <tr>
    <td >Billing cycles:</td>
    <td>
    {$BILLING_CYCLES}
    </td>
  </tr>
  
  
     <tr>
    <td >Packages:</td>
    <td>
    {$PACKAGES}
    </td>
  </tr> 
  
   <tr>
    <td >Package amount:</td>
    <td>{$PACKAGE_AMOUNT}</td>
  </tr>
  
  
  <tr>
    <td >Addons:</td>
    <td>
    {$ADDON}
    </td>
  </tr>   
  <tr>
    <td >Status:</td>
    <td>
    {$STATUS}
    </td>
  </tr>
      <tr>
    <td >Creation date:</td>
    <td>
    {$CREATED_AT}
    </td>
  </tr>
	<tr>
    <td >Control Panel Username:</td>
    <td>  		
		{$USERNAME}
    </td>
  </tr>
  
	<tr>
    <td >Control Panel Password:</td>
    <td>  		
		{$PASSWORD}
    </td>
  </tr>
</table>
{$INVOICE_LIST}