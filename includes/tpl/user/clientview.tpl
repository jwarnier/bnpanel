{literal}
<script type="text/javascript">
	function clientsearch() {
		var type = document.getElementById("type").value;
		var value = document.getElementById("value").value;
		ajaxSlide("clientsajax", "{$ajax}function=search&type="+type+"&value="+value);
                kthx();
	}
	$(document).ready(function() {
	    kthx();
	});
	
	function kthx() {
            $(".suspendIcon").click(function(){
                var status = "{$SUS}";
                if(status == "Suspend") {
                 var reason = prompt('Please state your reason for suspending. Leave blank for none.');
                if(reason != null && reason != "") {
                    var query = window.location + "&func=sus&reason=" + reason;
                }
                else {
                    var query = window.location + "&func=sus";
                }
                window.location = query;
                }
                else if(status == "Unsuspend") {
                    window.location = "{$url}admin/?page=users&sub=search&do={$ID}&func=unsus";
                }
                else if(status == "<a href='?page=users&sub=validate'>Validate</a>") {
                    window.location = "{$url}/admin/?page=users&sub=validate";
                }
                else if(status == "No Action") {
                    alert("No action to be performed.");
                }
                else {
                    alert("Uh oh...");
                }
            });
            $(".cancel").click(function(){
                var status = "cancel";
                if(status == "cancel") {
                 var reason = prompt('Please state your reason for cancelling. Leave blank for none.');
                if(reason != null && reason != "") {
                    var query = window.location + "&func=cancel&reason=" + reason;
                }
                else {
                    var query = window.location + "&func=cancel";
                }
                window.location = query;
                }
                else {
                    alert("Uh oh...");
                }
            });
            $(".term").click(function(){
                var status = "term";
                if(status == "term") {
                 var reason = prompt('Please state your reason for terminating. Leave blank for none.');
                if(reason != null && reason != "") {
                    var query = window.location + "&func=term&reason=" + reason;
                }
                else {
                    var query = window.location + "&func=term";
                }
                window.location = query;
                }
                else {
                    alert("Uh oh...");
                }
            });
        }
</script>
{/literal}

<div class="row">
	<div class="span14">
	<ul class="tabs">
		<li><a href="?page=users&amp;sub=search&amp;do={$ID}"><img src="{$url}themes/icons/user_go.png" /> Client Details</a></li>
		<li><a href="?page=users&amp;sub=edit&amp;do={$ID}"><img src="{$url}themes/icons/pencil.png" /> Edit Details</a></li>       
		<li><a href="?page=users&amp;sub=orders&amp;do={$ID}"><img src="{$url}themes/icons/order.png" /> Orders</a></li>      
		<li><a href="?page=users&amp;sub=invoices&amp;do={$ID}"><img src="{$url}themes/icons/invoice.png" /> Invoices</a></li>
		
		<li><a href="?page=users&amp;sub=email&amp;do={$ID}&amp;"><img src="{$url}themes/icons/email.png" /> Email User</a></li>
		<li><a href="?page=users&amp;sub=passwd&amp;do={$ID}&amp;"><img src="{$url}themes/icons/user_edit.png" /> Change BNPanel Password</a></li>
	</ul>
	</div>	
</div>
 
<div id="1" style="display:none;">
	<a title="The user's account and package will remain on the system but in a cancelled state." class="tooltip"><img src="{$icon_dir}information.png" /></a> <a class="cancel" href="javascript:void(0);">Cancel</a><br />
	<a title="Deletes all traces of the user from the system." class="tooltip"><img src="{$icon_dir}information.png" /></a> <a class="term" href="javascript:void(0);">Terminate</a>
</div>

{$CONTENT}
{$BOX}

  
