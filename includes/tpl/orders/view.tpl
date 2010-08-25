<div class="contextual">
	<a href="?page=orders&sub=edit&do=%ID%"> <img src="<URL>themes/icons/pencil.png"> Edit</a>
	<a href="?page=orders&sub=add_invoice&do=%ID%"> <img src="<URL>themes/icons/note_add.png"> Add Invoice</a>
	<a href="?page=orders&sub=change_pass&do=%ID%"> <img src="<URL>themes/icons/key.png"> Change CP Password</a>
	  
</div>
<h2>Order #%ID%</h2>
<ERRORS>
<table class="content" width="100%" border="0" cellspacing="2" cellpadding="0"> 
     <tr>
    <td class="label">User:</td>
    <td>    
    <a href="?page=users&sub=search&do=%USER_ID%">%USER%</a>
    </td>
  </tr> 
     <tr>
    <td class="label">Domain:</td>
    <td>
   %REAL_DOMAIN%
    </td>
  </tr>  
      <tr>
    <td class="label">Billing cycles:</td>
    <td>
    %BILLING_CYCLES%
    </td>
  </tr>
  
  
     <tr>
    <td class="label">Packages:</td>
    <td>
    %PACKAGES%
    </td>
  </tr> 
  
   <tr>
    <td class="label">Package amount:</td>
    <td>%PACKAGE_AMOUNT%</td>
  </tr>
  
  
  <tr>
    <td class="label">Addons:</td>
    <td>
    %ADDON%
    </td>
  </tr>   
  <tr>
    <td class="label">Status:</td>
    <td>
    %STATUS%
    </td>
  </tr>
      <tr>
    <td class="label">Creation date:</td>
    <td>
    %CREATED_AT%
    </td>
  </tr>
	<tr>
    <td class="label">Control Panel Username:</td>
    <td>  		
		%USERNAME%
    </td>
  </tr>
  
	<tr>
    <td class="label">Control Panel Password:</td>
    <td>  		
		%PASSWORD%
    </td>
  </tr>
</table>
%INVOICE_LIST%