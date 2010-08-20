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
	
	$("#tbody").load("<AJAX>function=getOrders&page=1", Hide_Load());

	$("#pagination").paginate({
		count 					: %COUNT%,
		start 					: 1,
		display     			: 5,
		border					: true,
		border_color			: '#fff',
		text_color  			: '#2A5685',
		background_color    	: '#EEE',	
		border_hover_color		: '#ccc',
		text_hover_color  		: '#000',
		background_hover_color	: '#E5E3E3', 
		images					: false,
		mouse					: 'press',
		onChange     			: function(page) {
									var pageNum = page;
									Display_Load();
									$("#tbody").load("<AJAX>function=getOrders&page=" + pageNum, Hide_Load());
									}
	});
		
});
</script>	
<p>From here you can see all orders in your BNPanel installation</p>
<ERRORS>
<div id="pagination_loading" ></div>
<table class="content_table"  width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
		<tr> 
	        <th width="30px"><div align="left"><b>&nbsp;No.</b></div></td>
	        <th width="200px"><strong>User</strong></td>        
	        <th width="140px"><strong>Package</strong></td>        
	        <th width="100px"><strong><a title="Domain name" class="tooltip"><img src="<ICONDIR>world.png" border="0" /></a> Domain</strong></td>
	        <th width="70px"><strong>Creation date</strong></td>
	        <th><strong>Status</strong></td>
	        <th width="80px"><strong>Actions</strong></td>                
		</tr>
	</thead>
	<tbody id="tbody"></tbody>	
</table>
<div id="pagination"></div> 
