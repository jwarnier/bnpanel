<div class="contextual">
	<a href="?page=invoices&sub=edit&do=%ID%"> <img src="<URL>themes/icons/pencil.png"> Edit</a> 
</div>
<h2>Invoice #%ID%</h2>

<form id="addpackage" name="addpackage" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">

  <tr>
    <td width="20%">Order id:</td>
    <td>%ORDER_ID%</td>
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
   %DOMAIN%
    </td>
  </tr>  
      <tr>
    <td valign="top">Description:</td>
    <td>%NOTES%</td>
  </tr>
  
        <tr>
    <td valign="top">Billing cycles</td>
    <td>
    %BILLING_CYCLES%
    </td>
  </tr>
  
  
     <tr>
    <td valign="top">Package</td>
    <td>
    %PACKAGE_NAME%
    </td>
  </tr> 
  
     <tr>
    <td valign="top">Package amount:</td>
    <td>%PACKAGE_AMOUNT%</td>
  </tr>
  

  
       <tr>
    <td valign="top">Addons</td>
    <td>
    %ADDON%
    </td>
  </tr>
    
    
	<tr>
    <td valign="top">Status</td>
    <td>
    %STATUS%
    </td>
  </tr> 
      
	<tr>
    <td valign="top">Due date</td>
    <td>
    %DUE%
    </td>
  </tr> 
  
  
   <tr>
    <td >Total:</td>
    <td valign="top" >
    	<p class="price">%TOTAL%</p>
    </td>
  </tr>
    
</table>


</form>
