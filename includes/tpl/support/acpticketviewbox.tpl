<div class="subborder" id="ticket-{$ID}">	
    	<table class="common-table" width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr bgcolor="{$URGCOLOR}">
          	<td width="5px">
          		<a href="?page=tickets&sub=view&do={$ID%">#%ID}</a>
          	</td>
            <td >
            	<a href="?page=tickets&sub=view&do={$ID}">
            		<strong>{$TITLE}</strong>
				</a>
            	<img title="{$STATUS_TITLE%" src="{$icon_dir}%STATUS_IMG}.png">
            	<br />Last Updated: {$UPDATE}
            </td>			            
            <td width="30" align="center" >            
            	<a href="javascript:void(0);" class="ticket-delete" id="ticket-delete-{$ID%" title="Delete the ticket '%TITLE}'."><img alt="Delete Ticket" src="{$icon_dir}delete.png"></a>
            </td>      
        </table>    
</div>