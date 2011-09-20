<div class="page-header">
	<h2>{t}Orders{/t}</h2>
</div>
<table class="common-table" width="100%" border="0" cellspacing="3" cellpadding="0">
	<tr> 
        <th width="5%">#</td>             
        <th>{t}Package{/t}</td>        
        <th>	
        	<a title="Domain name" class="tooltip">
        	<img src="{$icon_dir}world.png" border="0" /></a> {t}Domain{/t}</td>
        <th>
        	<a title="When it's due." class="tooltip">
        	<img src="{$icon_dir}time.png" border="0" /></a> {t}Creation date{/t}</td>
        <th>{t}Status{/t}</td>               
	</tr>
	{$list}
</table>