<ERRORS>
<div class="row">
	<div class="span8 columns offset4">
	<form class="form-stacked" id="login" name="login" method="post" action="{$url}client/">
		<fieldset>
			<legend>Login</legend>
			<div class="clearfix">
				<label>_{Username}</label>
				<div class="input">
					<input type="text" name="user" id="user_login" tabindex="1" size="20"  maxlength ="20"  />
				</div>
			</div>
			<div class="clearfix">
				<label>_{Password}</label>
				<div class="input">
					<input type="password" name="pass" id="pass_login" tabindex="2" size="20" />
				</div>
			</div>
			<div class="clearfix">
				<input type="submit" name="clogin" id="clogin" value="_{Login}" tabindex="3"  class="btn primary" />
			</div>
			<div class="clearfix">
				<a href="{$url}client/?page=forgotpass">_{Lost your password?}</a>
			</div>
		</fieldset>
	</form>
	</div>
</div>
