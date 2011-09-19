<script type="text/javascript">
function doswirl(id) {
	document.getElementById("swirl"+id).innerHTML = '<img src="{$url}themes/icons/ajax-loader.gif">';
	window.location = 'index.php?page=invoices&iid='+id;
}
</script>

<h2>Invoice #%ID%</h2>
<ERRORS>
<table class="content" border="0" cellspacing="2" cellpadding="0">

  <tr>
    <td class="label" width="20%">Order id:</td>
    <td><a href="?page=orders&sub=view&do=%ORDER_ID%">#%ORDER_ID%</a></td>
  </tr>  
     <tr>
    <td class="label">User:</td>
    <td>
    %USER%
    </td>
  </tr> 
     <tr>
    <td class="label">Domain:</td>
    <td>
   <a target="_blank" href="http://%REAL_DOMAIN%">%REAL_DOMAIN%</a>
    </td>
  </tr>  
      <tr>
    <td class="label">Description:</td>
    <td>%NOTES%</td>
  </tr>
  
        <tr>
    <td class="label">Billing cycles:</td>
    <td>
    %BILLING_CYCLES%
    </td>
  </tr>
  
  
     <tr>
    <td class="label">Package:</td>
    <td>
    %PACKAGE_NAME%
    </td>
  </tr> 
  
     <tr>
    <td class="label">Package amount:</td>
    <td>%PACKAGE_AMOUNT%</td>
  </tr>
  

  
       <tr>
    <td class="label">Addons</td>
    <td>
    %ADDON%
    </td>
  </tr>
    
    
	<tr>
    <td class="label">Status</td>
    <td>
    <strong>%STATUS%</strong>
    </td>
  </tr> 
      
	<tr>
    <td class="label">Due date:</td>
    <td>
    %DUE%
    </td>
  </tr> 
  
  
   <tr>
    <td class="label">Total:</td>
    <td  >
    	<p class="price">%TOTAL%</p>
    </td>
  </tr>
  
	<tr>
    	<td class="label">
			<div class="submit_button" id="swirl%ID%">
				%pay%	
			</div>
    	</td>
  	</tr>    
</table>