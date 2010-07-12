<style>
a:hover {
	color:#DF3D82;
	text-decoration:underline;
}
#loading { 
	width: 100%; 
	position: absolute;
}
#pagination {
	text-align:center;
	margin-left:auto;
	margin-right:auto;
}
#pagination li {	
	list-style: none; 
	float: left; 
	margin-right: 16px; 
	padding:5px; 
	border:solid 1px #dddddd;
	color:#0063DC; 
}
#pagination  li:hover { 
	color:#FF0084; 
	cursor: pointer; 
}
</style>

<script type="text/javascript">

$(document).ready(function() {
	
	//Display Loading Image
	function Display_Load() {
	    $("#loading").fadeIn(900,0);
		$("#loading").html("<img src='bigLoader.gif' />");
	}
	
	//Hide Loading Image
	function Hide_Load() {
		$("#loading").fadeOut('slow');
	};
	

   //Default Starting Page Results   
	$("#pagination li:first").css({'color' : '#FF0084'}).css({'border' : 'none'});
	
	Display_Load();
	
	$("#tbody").load("<AJAX>?function=getOrders&page=1", Hide_Load());

	//Pagination Click
	$("#pagination li").click(function(){
			
		Display_Load();
		
		//CSS Styles
		$("#pagination li")
		.css({'border' : 'solid #dddddd 1px'})
		.css({'color' : '#0063DC'});
		
		$(this)
		.css({'color' : '#FF0084'})
		.css({'border' : 'none'});

		//Loading Data
		var pageNum = this.id;
		
		$("#tbody").load("<AJAX>?function=getOrders&page=" + pageNum, Hide_Load());
	});	
});
</script>

<div id="loading" ></div>
	
<p>From here you can see all orders in your BNPanel installation</p>
<ERRORS>

<table width="100%" border="0" cellspacing="3" cellpadding="0">
	<thead>
		<tr> 
	        <td width="5%"><div align="left"><b>&nbsp;No.</b></div></td>
	        <td><strong>User</strong></td>        
	        <td><strong>Package</strong></td>        
	        <td><strong><a title="Domain name" class="tooltip"><img src="<ICONDIR>world.png" border="0" /></a> Domain</strong></td>
	        <td><strong><a title="When it's due." class="tooltip"><img src="<ICONDIR>time.png" border="0" /></a> Creation date</strong></td>
	        <td><strong>Status</strong></td>
	        <td width="150px"><strong>Actions</strong></td>                
		</tr>
	</thead>	
	<tbody id="tbody"></tbody>
	<tfoot>
		<tr>
			<td colspan="7" align="center">	%pagination%</td>
		</tr>
	</tfoot>
</table>