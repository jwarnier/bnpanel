<script type="text/javascript" src="<URL>includes/javascript/jpaginate/jquery.paginate.js"></script>
<link rel="stylesheet" href="<URL>includes/javascript/jpaginate/css/style.css" type="text/css" />
<script type="text/javascript">

$(document).ready(function() {
	
	//Display Loading Image
	function Display_Load() {
	    $("#pagination_loading").fadeIn(900,0);
		$("#pagination_loading").html("<img src='<URL>themes/icons/ajax-loader2.gif' />");
	}
	
	//Hide Loading Image
	function Hide_Load() {
		$("#pagination_loading").fadeOut('slow');
	};	
	
	Display_Load();
	
	$("#tbody").load("<AJAX>?function=getOrders&page=1", Hide_Load());

	$("#pagination").paginate({
		count 					: %COUNT%,
		start 					: 1,
		display     			: 5,
		border					: true,
		border_color			: '#fff',
		text_color  			: '#fff',
		background_color    	: 'black',	
		border_hover_color		: '#ccc',
		text_hover_color  		: '#000',
		background_hover_color	: '#fff', 
		images					: false,
		mouse					: 'press',
		onChange     			: function(page) {
									var pageNum = page;
									Display_Load();
									$("#tbody").load("<AJAX>?function=getOrders&page=" + pageNum, Hide_Load());
									}
	});
		
});
</script>	
<p>From here you can see all orders in your BNPanel installation</p>
<ERRORS>
<div id="pagination_loading" ></div>
<table width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
		<tr> 
	        <td width="20px"><div align="left"><b>&nbsp;No.</b></div></td>
	        <td width="200px"><strong>User</strong></td>        
	        <td width="140px"><strong>Package</strong></td>        
	        <td width="100px"><strong><a title="Domain name" class="tooltip"><img src="<ICONDIR>world.png" border="0" /></a> Domain</strong></td>
	        <td width="120px"><strong><a title="When it's due." class="tooltip"><img src="<ICONDIR>time.png" border="0" /></a> Creation date</strong></td>
	        <td><strong>Status</strong></td>
	        <td width="100px"><strong>Actions</strong></td>                
		</tr>
	</thead>
	<tbody id="tbody"></tbody>
	<tfoot>
		<tr>
			<td colspan="7"></td>
		</tr>
	</tfoot>
</table>
<div id="pagination"></div> 
