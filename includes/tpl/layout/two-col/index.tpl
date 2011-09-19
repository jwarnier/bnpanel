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
            		<a href="?l=es"><img src="../themes/flags/es.gif" alt="es" /> </a>
            	</li>
            	<li>    		
            		<a href="?l=nl"><img src="../themes/flags/nl.gif" alt="nl" /> </a>
            	</li>    	   	
            </ul>            
            <ul class="nav secondary-nav">
                <span id="welcome" class="welcome"><LOGIN></span>
            </ul>            
        </div>
    </div>
        
</div>
 
<div class="container-fluid" style="padding-top: 60px;">    
    <div class="sidebar">
    	<div class="well">    	
        	{$sidebar}
        </div>
    </div>
    <div class="content"> 
		{$sub_menu}
        {$content}
    </div>
</div>


{include file="layout/one-col/footer.tpl"}