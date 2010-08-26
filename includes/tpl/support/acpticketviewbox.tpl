<div class="subborder" id="ticket-%ID%">	
    	<table class="content_table" width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr%URGCOLOR%
          	<td width="5px">
          		<a href="?page=tickets&sub=view&do=%ID%">#%ID%</a>
          	</td>
            <td >
            	<a href="?page=tickets&sub=view&do=%ID%">
            		<strong>%TITLE%</strong>
            	</a>
            	<img alt="Ticket Status" src="<ICONDIR>%STATUS%.png">
            	<br />Last Updated: %UPDATE%
            </td>			            
            <td width="30" align="center" >            
            	<a href="javascript:void(0);" class="ticket-delete" id="ticket-delete-%ID%" title="Delete the ticket '%TITLE%'."><img alt="Delete Ticket" src="<ICONDIR>delete.png"></a>
            </td>      
        </table>    
</div>