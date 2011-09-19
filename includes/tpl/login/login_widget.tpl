<div id="login_form" title="Login">
	
	<form class="login_wrapper" id="login" name="login" method="post" action="{$url}client/">
	<p>
		<label>{t}Username{/t}</label>	
	    <br />
		<input type="text" name="user" id="user_login" tabindex="1" size="20" maxlength ="20"  />
	</p>
	<p>
		<label>{t}Password{/t}</label><br />
		<input type="password" name="pass" id="pass_login" tabindex="2" size="20" />
	</p>
	<p class="submit">
		<input type="button" ondblclick="return false" onclick="loginUser();" name="clogin" id="clogin" value="{t}Login{/t}" tabindex="3" />
	</p>
	</form>
</div>