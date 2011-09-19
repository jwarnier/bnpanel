<script type="text/javascript">
function addme() {
	$("#addbox").slideToggle(500);	
}
</script>
<script type="text/javascript">
function editme(id) {
	$.get("{$ajax}function={$AJAX}&id="+id, function(data) {
			var result = data.split("{}[]{}");
			if(document.getElementById("editbox").style.display == "none") {
				document.getElementById("editname").value = result[0];
				tinyMCE.get("editdescription").execCommand('mceSetContent',false, result[1] );
				$("#editbox").slideDown(500);	
			}
			else {
				$("#editbox").slideUp(500, function(data) {
					document.getElementById("editname").value = result[0];
					tinyMCE.get("editdescription").execCommand('mceSetContent',false, result[1] );
					$("#editbox").slideDown(500);
														});		
			}
			document.getElementById("id").value = id;
															});
}
</script>
<script type="text/javascript">
	tinyMCE.init({
	mode : "textareas",
	skin : "o2k7",
	theme : "advanced",
	
	theme_advanced_toolbar_location : "top",		
	theme_advanced_toolbar_align : "left",		
	theme_advanced_statusbar_location : "bottom",		
	theme_advanced_resizing : true,
	height:"300px",
	width:"100%",
	});
</script>
<ERRORS>
<div class="subborder">
	<div class="sub">
   	  <table width="100%" border="0" cellspacing="2" cellpadding="0">
    	  <tr>
    	    <td width="1%"><img src="{$icon_dir}add.png"></td>
    	    <td><a href="Javascript:addme()">Add {$NAME}</a></td>
  	    </tr>
  	  </table>
	</div>
</div>
<form action=""  id="addbox" style="display:none;" method="post" name="add{$NAME}">
    <div class="subborder">
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Add {$NAME}</strong></td>
            </tr>
            {$CATID}
            <tr>
                <td width="20%">{$SUB}:</td>
                <td><input name="name" type="text" id="name" size="40" /></td>
            </tr>
            <tr>
                <td width="20%" valign="top">{$SUB2}:</td>
                <td><textarea name="description" id="description" cols="" rows=""></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="add" id="add" type="submit" value="Add {$NAME}" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
<form action="" id="editbox" style="display:none;" method="post" name="edit{$NAME}">
    <div class="subborder" >
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Edit {$NAME}</strong></td>
            </tr>
            <tr>
                <td width="20%">{$SUB}:</td>
                <td><input name="editname" type="text" id="editname" size="40" /></td>
            </tr>
            <tr>
                <td width="20%" valign="top">{$SUB2}:</td>
                <td><textarea name="editdescription" id="editdescription" cols="" rows=""></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="edit" id="edit" type="submit" value="Edit {$NAME}" />
                <input name="id" id="id" type="hidden" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
{$BOXES}