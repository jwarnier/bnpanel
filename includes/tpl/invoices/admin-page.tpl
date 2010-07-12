<script type="text/javascript" src="<URL>includes/javascript/jpaginate/jquery.paginate.js"></script>
<link rel="stylesheet" href="<URL>includes/javascript/jpaginate/css/style.css" type="text/css" />



<script type="text/javascript">

$(document).ready(function() {
	
	//Display Loading Image
	function Display_Load() {
    	$("#loading").fadeIn(900,0);
		$("#loading").html("<img src='<URL>themes/icons/ajax-loader2.gif' />");
	}
	
	//Hide Loading Image
	function Hide_Load() {
		$("#loading").fadeOut('slow');
	};
	
	Display_Load();
	
	$("#tbody").load("<AJAX>?function=getInvoices&page=1", Hide_Load());

	$("#pagination").paginate({
		count 		: %COUNT%,
		start 		: 1,
		display     : 5,
		border					: true,
		border_color			: '#fff',
		text_color  			: '#fff',
		background_color    	: 'black',	
		border_hover_color		: '#ccc',
		text_hover_color  		: '#000',
		background_hover_color	: '#fff', 
		images					: false,
		mouse					: 'press',
		onChange     			: function(page){
									var pageNum = page;
									Display_Load();
									$("#tbody").load("<AJAX>?function=getInvoices&page=" + pageNum, Hide_Load());
	  							}
	 });	
});
</script>

<p>From here you can see all invoices in your BNPanel installation.</p>
<!--  <div class="subborder">
	<div class="sub">
		<strong>Invoice Statistics</strong>
		<div class='break'></div>
		<strong>Number of Invoices:</strong> %num%<br />
		<strong>Invoices Paid:</strong> %numpaid%<br />
		<strong>Unpaid Invoices:</strong> %numunpaid%
	</div>
</div> -->


<table width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
	<tr> 
        <td width="5%"><div align="left"><b>&nbsp;No.</b></div></td>
        <td><strong>User</strong></td>
        <td><strong><a title="Domain name" class="tooltip"><img src="<ICONDIR>world.png" border="0" /></a> Domain</strong></td>
        <td><strong><a title="The amount of money you owe." class="tooltip"><img src="<ICONDIR>money.png" border="0" /></a> Amount</strong></td>      	
        <!--  <td><b><a title="Package name" class="tooltip"><img src="<ICONDIR>package_green.png" border="0" /></a> Package</b></td>  --> 
         <!--  <td><a title="Billing cycle" class="tooltip"><img src="<ICONDIR>information.png" border="0" /></a> <strong>Billing cycle</strong></td>  -->
        <!-- <td><b>Addon List</b></td> -->
        <!--  -->
        <td><strong>Status</strong></td>
        <td><strong><a title="When it's due." class="tooltip"><img src="<ICONDIR>time.png" border="0" /></a> Due date</strong></td>
        <td width="150px"><strong>Actions</strong></td>                
	</tr>
	</thead>
	<tbody id="tbody"></tbody>
	<tfoot>
		<tr>
		<td colspan="7">	
			<div id="pagination"></div>
			</td>
		</tr>
	</tfoot>
	
</table>