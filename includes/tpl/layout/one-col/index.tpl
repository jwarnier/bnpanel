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
<div id="masthead">
    <div class="inner">
        <div class="container">
            <h1>BNPanel 1.0</h1>
                <p class="lead">
                    BNPanel supports hosting plans, hosting addons, customers, servers, tickets, paid hosting, cPanel and ISPConfig 3 integration, multi language support and more. 
                    <br />
                    It includes base CSS and HTML for typography, forms, buttons, tables, grids, navigation, and more.
                    <br />
                </p>
                <p>
                    <strong></strong>
                </p>
        </div>
    </div>
</div>

<div class="container">	
    {$content}
</div>

{include file="layout/one-col/footer.tpl"}