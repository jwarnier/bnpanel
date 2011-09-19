<ERRORS>
<div class="row">
	<div class="span8 columns offset4">
		<form class="form-stacked" id="login" name="login" method="post" action="">
		<fieldset>
			<legend>Admin Login</legend>
			<div class="clearfix">
				<label>{t}Username{/t}</label>
				<div class="input">
					<input type="text" name="user" id="user_login" tabindex="1" size="20"  maxlength ="20"  />
				</div>
			</div>
			<div class="clearfix">
				<label>{t}Password{/t}</label>
				<div class="input">
					<input type="password" name="pass" id="pass_login" tabindex="2" size="20" />
				</div>
			</div>
			
			<div class="clearfix">
				<input type="submit" name="clogin" id="clogin" value="{t}Login{/t}" tabindex="3" class="btn primary" />
			</div>
			<div class="clearfix">
				<a href="{$url}admin/?page=forgotpass">{t}Lost your password?{/t}</a>
			</div>
		</fieldset>
		</form>	
	</div>
</div>