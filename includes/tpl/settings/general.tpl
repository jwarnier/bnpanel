<ERRORS>
<form class="content" id="settings" name="settings" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Site Name:</td>
    <td>
      <input name="name" type="text" id="name" value="%NAME%" />
      <a title="Your BNPanel Website's Name." class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  
   <!-- <tr>
    <td width="20%">Site Email</td>
    <td>
      <input name="site_email" type="text" id="site_email" value="SITE_EMAIL" />
      <a title="Warnings, reminders, alerts will be sent with this email " class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr> -->
  
  <tr>
    <td width="20%">URL: (Including trailing slash)</td>
    <td>
      <input name="url" type="text" id="host" value="%URL%" />
      <a title="Your BNPanel Website's URL. (Recommended: http://%RECURL%/)" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Default Page:</td>
    <td>%DROPDOWN%    <a title="The Default page shown when accessing the root directory." class="tooltip"><img src="{$url}themes/icons/information.png" /></a></td>
  </tr>
  
  <tr>
    <td width="20%">Rows per page</td>
    <td>
      <input name="rows_per_page" type="text" id="rows_per_page" value="%ROWS_PER_PAGE%" />
      <a title="Rows per page in orders, invoices, etc " class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  
  <tr>
    <td width="20%">Domain/Subdomain options</td>
    <td>
      %DOMAIN_OPTIONS%
      <a title="Allow the registration of Domains, Subdomains or Both" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  
  
    <tr>
    <td width="20%">Server Status</td>
    <td>
      %SERVER_STATUS%
      <a title="Server status" class="tooltip"><img src="{$url}themes/icons/information.png" /></a>
    </td>
  </tr>
  
  
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" /></td>
  </tr>
</table>
</form>
