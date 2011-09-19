<ERRORS>
<p>From here you can see the list of your invoices.</p>


<table  class="content_table"   width="100%" border="0" cellspacing="3" cellpadding="0">
	<tr> 
        <th width="5%"><div align="left"><b>&nbsp;No.</b></div></th>        
        <th><strong>User</strong></th>
        <th><strong><a title="Domain name" class="tooltip"> <img src="{$icon_dir}world.png" border="0" /> </a> Domain</strong></th>
        <th><strong><a title="The amount of money you owe." class="tooltip"><img src="{$icon_dir}money.png" border="0" /></a> Amount</strong></th>      	
          <!-- <td><b><a title="Package name" class="tooltip"><img src="{$icon_dir}package_green.png" border="0" /></a> Package</b></td>-->
           <!--<td><a title="Billing cycle" class="tooltip"><img src="{$icon_dir}information.png" border="0" /></a> <strong>Billing cycle</strong></td>-->
        <!-- <td><b>Addon List</b></td> -->
        <!--  -->
        
        <th><strong>Status</strong></th>
        <th><strong><a title="When it's due." class="tooltip"><img src="{$icon_dir}time.png" border="0" /></a> Due date</strong></th>
        <th width="150px"><strong>Actions</strong></th>                
	</tr>
	{$list}
</table>
