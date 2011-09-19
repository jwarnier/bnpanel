<script type="text/javascript">
var loading = '<img src="{$url}themes/icons/ajax-loader.gif">';
function status(id, status) {
	document.getElementById("statuschange").innerHTML = loading;
	$.get("{$ajax}function=status&id="+id+"&status="+status, function(data) {
		document.getElementById("statuschange").innerHTML = data;														  
	});
}
</script>
<div class="subborder">
	<div class="sub">
        <table width="100%" border="0" cellspacing="2" cellpadding="0">
        <tr>
            <td width="25%">Status:</td>
            <td>
            	{$DROPDOWN}
            </td>
            <td width="74%" id="statuschange"></td>
          </tr>
        </table>
    </div>
</div>