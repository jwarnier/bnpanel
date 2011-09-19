<ERRORS>
<h2>Ticket #{$ID}</h2>
<div class="ticket">
<h3>{$TITLE}</h3>    	
    	
		<table cellspacing="2" cellpadding="0" border="0" width="100%" class="content"> 
		<tr>
			<td >Author:</td>
			<td>    
				{$AUTHOR}
			</td>
		</tr>
		<tr>
		<td >Status:</td>
			<td>    
				{$STATUS}
			</td>
		</tr>
		<tr>
			<td >Urgency:</td>
			<td>    
				{$URGENCY}
			</td>
		</tr>
		<tr>
			<td >Created on:</td>
			<td>    
				{$TIME}
			</td>
		</tr>
				
		<tr>
			<td >Last Updated:</td>
			<td>    
				{$UPDATED}
			</td>
		</tr>		
		</table>
		<hr>
		<p>
 		<strong>Description</strong>
 		{$DESCRIPTION}<br />
 		</p>
        <!--  <strong>Number of replies:</strong> {$NUMREPLIES}<br />  -->
</div>

{$REPLIES}
{$ADDREPLY}