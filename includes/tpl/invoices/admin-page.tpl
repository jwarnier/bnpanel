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
	
	$("#tbody").load("<AJAX>function=getInvoices&page=1", Hide_Load());

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
		onChange     			: 	function(page) {
										var pageNum = page;
										Display_Load();
										$("#tbody").load("<AJAX>function=getInvoices&page=" + pageNum, Hide_Load());
									}
	 });	
});
</script>
<p>From here you can see all invoices in your BNPanel installation.</p>
<ERRORS>
<div id="pagination_loading" ></div>
<table  class="content_table"   width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
	<tr> 
        <th width="45px"><div align="left"><b>&nbsp;No.</b></div></td>
        <th><strong>User</strong></td>
        <th><strong><a title="Domain name" class="tooltip"><img src="<ICONDIR>world.png" border="0" /></a> Domain</strong></td>
        <th><strong><a title="The amount of money you owe." class="tooltip"><img src="<ICONDIR>money.png" border="0" /></a> Amount</strong></td>      	
        <!--  <td><b><a title="Package name" class="tooltip"><img src="<ICONDIR>package_green.png" border="0" /></a> Package</b></td>  --> 
         <!--  <td><a title="Billing cycle" class="tooltip"><img src="<ICONDIR>information.png" border="0" /></a> <strong>Billing cycle</strong></td>  -->
        <!-- <td><b>Addon List</b></td> -->
        <!--  -->
        <th><strong>Status</strong></td>
        <th><strong><a title="When it's due." class="tooltip"><img src="<ICONDIR>time.png" border="0" /></a> Due date</strong></td>
        <th width="80px"><strong>Actions</strong></td>                
	</tr>
	</thead>
	<tbody id="tbody"></tbody>	
</table>
<div id="pagination"></div>