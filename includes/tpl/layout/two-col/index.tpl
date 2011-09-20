{include file="layout/one-col/header.tpl"}	
<div class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h3><a href="{$url}">{$app_name}</a></h3>
            <ul>
            	{$nav}
            	{$admin_nav}  
            	<li>
            		<a href="?l=en"><img src="../themes/flags/gb.gif" alt="en" /></a>
            	</li>    	
            	<li>    		
            		<a href="?l=es"><img src="../themes/flags/es.gif" alt="es" /></a>
            	</li>
            	<li>    		
            		<a href="?l=nl"><img src="../themes/flags/nl.gif" alt="nl" /></a>
            	</li>    	   	
            </ul>            
			{$login}            
        </div>
    </div>        
</div>
 
<div class="container-fluid" style="padding-top: 60px;">    
    <div class="sidebar">
    	<div class="well">
    		<h5>Settings</h5>    	   	
        	{$sidebar}
        </div>
    </div>
    <div class="content"> 
		{$sub_menu}
		
		{foreach $messages as $message}
			{$message}
		{/foreach}
		
		{if (!empty($content))}
        	{$content}
        {/if}
    </div>
</div>
{include file="layout/one-col/footer.tpl"}