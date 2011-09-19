<script type="text/javascript" src="{$url}includes/javascript/jpaginate/jquery.paginate.js"></script>
<link rel="stylesheet" href="{$url}includes/javascript/jpaginate/css/style.css" type="text/css" />
<script type="text/javascript">
var pageNum = 0;
var status_id = 0;

//Hide Loading Image
function Hide_Load() {
	$("#pagination_loading").fadeOut('slow');
};


//Display Loading Image
function Display_Load() {
	$("#pagination_loading").fadeIn(900,0);
	$("#pagination_loading").html("<img src='{$url}themes/icons/ajax-loader2.gif' />");
}

$(document).ready(function() {	

	Display_Load();
	
	$("#tbody").load("{$ajax}function=getInvoices&page=1", Hide_Load());

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
										pageNum = page;
										Display_Load();
										$("#tbody").load("{$ajax}function=getInvoices&page=" + pageNum+"&status="+status_id, Hide_Load());
									}
	 });	
});

function filter() {
	status_id = $("#status_id").val();
	$("#tbody").load("{$ajax}function=getInvoices&page=" + pageNum +"&status="+status_id, Hide_Load());
}

</script>
<h2>Invoices</h2>
%STATUS_FILTER%
<a href="#" onclick="filter();">Apply</a>
<ERRORS>


<div id="pagination_loading" ></div>
<table  class="content_table"   width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
	<tr> 
        <th width="45px"><div align="left"><b>&nbsp;No.</b></div></th>
        <th><strong>User</strong></th>
        <th><strong><a title="Domain name" class="tooltip"><img src="{$icon_dir}world.png" border="0" /></a> Domain</strong></th>
        <th><strong><a title="The amount of money you owe." class="tooltip"><img src="{$icon_dir}money.png" border="0" /></a> Amount</strong></th>      	
        <!--  <td><b><a title="Package name" class="tooltip"><img src="{$icon_dir}package_green.png" border="0" /></a> Package</b></td>  --> 
         <!--  <td><a title="Billing cycle" class="tooltip"><img src="{$icon_dir}information.png" border="0" /></a> <strong>Billing cycle</strong></td>  -->
        <!-- <td><b>Addon List</b></td> -->
        <!--  -->
        <th><strong>Status</strong></th>
        <th><strong><a title="When it's due." class="tooltip"><img src="{$icon_dir}time.png" border="0" /></a> Due date</strong></th>
        <th width="80px"><strong>Actions</strong></th>                
	</tr>
	</thead>
	<tbody id="tbody"></tbody>	
</table>
<div id="pagination"></div>