<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><APP_TITLE></title>
<JAVASCRIPT>
<CSS>
</head>
<body>
<script type="text/javascript">

$(document).ready(function() {
    //Modal login options see http://jqueryui.com/demos/dialog/#animated for more information
    $('#login_form').dialog({
        autoOpen:   false, //Avoid dialog problem
        modal:      true,
        draggable:  false,
        resizable:  false,
        height:     'auto',
        position:   'center'
            
    });   
});
   
function loginUser() {
    var user = $("#user_login").val();
    var pass = $("#pass_login").val();  
    $.get("<AJAX>function=clientLogin&user="+user+"&pass="+pass, function(data) {
        if (data != '') {
            if (data == 1) {
                /*if (step == '5') {                  
                    showhide(step, step + 1)
                    step = step + 1;                        
                }*/
                $.get("<AJAX>function=getNavigation", function(data2) {
                    $("#welcome").html(data2);  
                });         
                $("#login_form").dialog('close');           
            } else {
                alert('Please, try again');
            }
        }       
    });     
}

function showLogin() {  
    $("#login_form").dialog('open');
}

</script>

<LOGIN_TPL>