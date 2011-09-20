<script type="text/javascript">
function doswirl(id) {
	document.getElementById("swirl"+id).innerHTML = '<img src="{$url}themes/icons/ajax-loader.gif">';
	window.location = 'index.php?page=invoices&iid='+id;
}
</script>
<tr>
	<td><a href="index.php?page=invoices&sub=view&do={$id}">{$id}</a></td>	
	<td><a target="_blank"  href="http://{$domain}">{$domain}</a></td>
	<td>{$amount}</td>
  	
  	<!-- <td>{$package}</td>  -->    
  	<!-- <td>{$billing_cycle}</td> -->    
	<!-- <td>{$addon_fee}</td> -->    

  	<td>{$paid}</td>
  	<td>{$due}</td>
  	<td><div id="swirl{$id}">{$pay}</div></td>  	           
</tr>                