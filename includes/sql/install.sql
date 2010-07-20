SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- --------------------------------------------------------

--
-- Table structure for table `%PRE%acpnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%acpnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(20) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (id)
);

--
-- Dumping data for table `%PRE%acpnav`
--

INSERT INTO `%PRE%acpnav` (`visual`, `icon`, `link`) VALUES
('Home', 'house.png', 'home'),
('General Settings', 'cog.png', 'settings'),
('Look & Feel', 'rainbow.png', 'lof'),
('Servers', 'server.png', 'servers'),
('Billing Cycles', 'rainbow.png', 'billing'),
('Subdomains', 'link.png', 'sub'),
('Packages', 'package_green.png', 'packages'),
('Addons', 'rainbow.png', 'addons'),
('Staff Accounts', 'user_gray.png', 'staff'),
('Clients', 'group.png', 'users'),
('Client Importer', 'user_orange.png', 'import'),
('Mail Center', 'email_open.png', 'email'),
('Tickets', 'page_white_text.png', 'tickets'),
('Knowledge Base', 'folder.png', 'kb'),
('Orders', 'order.png', 'orders'),
('Invoice Management', 'invoice.png', 'invoices'),
--('Payments', 	'money_dollar.png', 'payment'),
--('Plugins', 'plugin.png', 'plugins'),
('Change Password', 'shield.png', 'pass'),
('Server Status', 'computer.png', 'status'),
('Logs', 'report.png', 'logs');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%articles`
--

CREATE TABLE IF NOT EXISTS `%PRE%articles` (
  `id` mediumint(9) NOT NULL auto_increment,
  `catid` mediumint(9) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%articles`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%cats`
--

CREATE TABLE IF NOT EXISTS `%PRE%cats` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%cats`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%clientnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%clientnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(20) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `%PRE%clientnav`
--

INSERT INTO `%PRE%clientnav` (`visual`, `icon`, `link`) VALUES
('Home', 'house.png', 'home'),
('Announcements', 'bell.png', 'notices'),
-- (4, 'View Package', 'package.png', 'view'), 
('Edit Details', 'user_edit.png', 'details'),
('Delete Account', 'cross.png', 'delete'),
('Invoices', 'script.png', 'invoices'),
('Orders', 'order.png', 'orders'),
('Tickets', 'page_white_text.png', 'tickets');


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%config`
--

CREATE TABLE IF NOT EXISTS `%PRE%config` (
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `%PRE%config`
--

INSERT INTO `%PRE%config` (`name`, `value`) VALUES
('url', 'http://bnpanel.com/'),
('version', '1.2.3'),
('smtp_user', 'user'),
('senabled', '1'),
('whm-ssl', '0'),
('paypalemail', 'your@email.com'),
('default', 'order'),
('theme', 'Reloaded2'),
('name', 'BNPanel'),
('tos', '<p><span style="font-weight: bold;"><span style="color: #333333; font-family: Tahoma; font-size: 11px; font-weight: normal;"><span style="color: #ff0000;">The following content is prohibited on our servers:</span><ol>\r\n<li><strong>Illegal use</strong></li>\r\n<li><strong>Threatening Material</strong></li>\r\n<li><strong>Fraudulent Content</strong></li>\r\n<li><strong>Forgery or Impersonation</strong></li>\r\n<li><strong>Unsolicited Content</strong></li>\r\n<li><strong>Copyright Infringements</strong></li>\r\n<li><strong>Collection of Private Date (Unless DPA Registered)</strong></li>\r\n<li><strong>Viruses</strong></li>\r\n<li><strong>IRC Networks (Including all IRC Material)</strong></li>\r\n<li><strong>Peer to Peer software&nbsp;<br /></strong></li>\r\n<li><strong>Any Adult Content</strong></li>\r\n</ol></span></span></p>'),
('multiple', '1'),
('general', '1'),
('message', '<p>Signups are currently disabled. Contact your host for more information.</p>'),
('delacc', '0'),
('cenabled', '1'),
('cmessage', '<p>Client area isn''t enabled. Sorry, contact your host for more details.</p>'),
('emailmethod', 'php'),
('emailfrom', ''),
('smtp_host', 'localhost'),
('smtp_user', 'user'),
('smtp_password', 'password'),
('show_version_id', '1'),
('show_acp_menu', '1'),
('show_page_gentime', '1'),
('alerts', '<p>&nbsp;</p>\r\n<p><em></em></p>'),
('p2hcheck', ''),
('show_footer', '0'),
('senabled', '1'),
('smessage', '<p>Support area isn''t enabled. Sorry, contact your host for more details.</p>'),
('terminationdays', '14'),
('suspensiondays', '14'),
('tldonly', '0'),
('currency', 'USD'),
('ui-theme', 'cupertino'),
('rows_per_page', '20'),
('paypal_mode', '0'); 

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%invoices`
--

CREATE TABLE IF NOT EXISTS `%PRE%invoices` (
  `id` int NOT NULL auto_increment,
  `uid` int NOT NULL,
  `amount` decimal(16,6) NOT NULL,
  `is_paid` int NOT NULL default '0',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `due` text NOT NULL,
  `is_suspended` int NOT NULL default '0',
  `notes` text NOT NULL,
  `uniqueid` varchar(255) NOT NULL,
  `addon_fee` longtext NOT NULL,
  `status` int NOT NULL,
  `transaction_id` varchar(255) NOT NULL,  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%invoices`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%navbar`
--

CREATE TABLE IF NOT EXISTS `%PRE%navbar` (
  `id` smallint(6) NOT NULL auto_increment,
  `icon` varchar(20) NOT NULL,
  `visual` varchar(70) NOT NULL,
  `link` varchar(20) NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `%PRE%navbar`
--

INSERT INTO `%PRE%navbar` (`id`, `icon`, `visual`, `link`, `order`) VALUES
(1, 'cart.png', 'Order Form', 'order', 1),
(2, 'user.png', 'Client Area', 'client', 0),
(3, 'key.png', 'Admin Area', 'admin', 2),
(4, 'report_magnify.png', 'Knowledge Base', 'support', 3);

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%packages`
--

CREATE TABLE IF NOT EXISTS `%PRE%packages` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `backend` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(10) NOT NULL,
  `server` varchar(20) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `reseller` tinyint(4) NOT NULL,
  `additional` text NOT NULL,
  `order` int(11) NOT NULL default '0',
  `is_hidden` int(1) NOT NULL,
  `is_disabled` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%packages`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%resources`
--

CREATE TABLE IF NOT EXISTS `%PRE%resources` (
  `resource_name` varchar(20) NOT NULL,
  `resource_value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `%PRE%resources`
--

INSERT INTO `%PRE%resources` (`resource_name`, `resource_value`) VALUES
('admin_notes', '<p><strong>Welcome to your BNPanel Installation!</strong></p>\r\n<p>We hope that you like BNPanel and you have a good time with our script. If you need any help, you can ask at the BNPanel Community, or contact us directly. Thanks for using BNPanel, and good luck on your hosting service!</p>\r\n<p>- The BNPanel Team</p>');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%servers`
--

CREATE TABLE IF NOT EXISTS `%PRE%servers` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `host` varchar(50) NOT NULL,
  `user` varchar(20) NOT NULL,
  `accesshash` text NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%servers`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%staff`
--

CREATE TABLE IF NOT EXISTS `%PRE%staff` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `salt` text NOT NULL,
  `perms` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%staff`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%subdomains`
--

CREATE TABLE IF NOT EXISTS `%PRE%subdomains` (
  `id` mediumint(9) NOT NULL auto_increment,
  `subdomain` varchar(20) NOT NULL,
  `server` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%subdomains`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%supportnav`
--

CREATE TABLE IF NOT EXISTS `%PRE%supportnav` (
  `id` mediumint(9) NOT NULL auto_increment,
  `visual` varchar(50) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `link` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `%PRE%supportnav`
--

INSERT INTO `%PRE%supportnav` (`id`, `visual`, `icon`, `link`) VALUES
(1, 'Home', 'house.png', 'home'),
(2, 'Tickets', 'page_white_text.png', 'tickets'),
(3, 'Knowledgebase', 'folder_explore.png', 'kb');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%templates`
--

CREATE TABLE IF NOT EXISTS `%PRE%templates` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `acpvisual` varchar(50) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `%PRE%templates`
--

INSERT INTO `%PRE%templates` (`id`, `name`, `acpvisual`, `subject`, `content`, `description`) VALUES
(1, 'reset', 'Client Reset Password', 'New Password', '<p><span style="font-weight: bold;">Your password has been reset!<br /><span style="font-weight: normal;">New Password: %PASS%<br /><br /><span style="font-weight: bold;"><span style="color: #ff0000;">Notice!<br /><span style="color: #000000;"><span style="font-weight: normal;">If you didn''t use the forgot password feature, we suggest you log into client area with your new password and use a different email.</span></span></span></span></span></span></p>', 'This is the email a user recieves when they reset their password.<br /><br />\r\n\r\nTemplate Variables:<br />\r\n%PASS% = Their new password'),
(3, 'newacc', 'New Hosting Account', 'Your Hosting Account', '<p><span style="font-weight: bold;">Your account has been created!<br /><span style="font-weight: normal;">Your account has been successfully created and you''re now able to log into your client control panel and your web hosting control panel. Your details are as follows:</span></span></p>
<p><span style="font-weight: bold;">Username: </span>%USER%<br /><span style="font-weight: bold;">Password: </span>%PASS%<br /><span style="font-weight: bold;">Email: </span>%EMAIL%<br /><span style="font-weight: bold;">Domain/Subdomain: </span>%DOMAIN%<br /><span style="font-weight: bold;">Package: </span>%PACKAGE%<br /><span style="font-weight: bold;">%CONFIRM%</p>', 'This is the email a client gets when they first go though the order form and complete it. This email should contain all they''re details.<br /><br />Template Variables:<br />%USER% - Client Username<br />%PASS% - Client Password<br />%EMAIL% - Client Email<br />%DOMAIN% - The clients package url<br />%PACKAGE% - The package the client signed up for<br />%CONFIRM% - The confirmation link'),
(4, 'termacc', 'Client Terminated', 'Termination', '<p><span style="font-weight: bold;">Your account has been terminated!<br /><span style="font-weight: normal;">This now means that your client username and password no longer exists. This is the same with your web hosting panel. All your files, databases, records have been removed and aren''t retrievable.<br /><br /><span style="font-weight: bold;">Reason for termination: </span>%REASON%</span></span></p>', 'This is the email the client recives when their account has been terminated by admin or when they manually deleted it in the client control panel.<br /><br />\r\n\r\nEmail Variables:<br />\r\n%REASON% - The reason why their account has been terminated'),
(5, 'suspendacc', 'Suspended Account', 'Suspended', '<p><span style="font-weight: bold;">Your Account Has Been Suspended!<br /><span style="font-weight: normal;">This means that your site on control panel is no longer accessable. Your website and all its content still remain intact. Contact your host for further details.</span></span></p>', 'This is the email the client recieves when they''re account has been suspended. '),
(6, 'unsusacc', 'Unsuspended Account', 'Unsuspended', '<p><span style="font-family: ''Times New Roman''; font-size: 16px;">\r\n<div style="color: #000000; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; background-image: initial; background-repeat: initial; background-attachment: initial; -webkit-background-clip: initial; -webkit-background-origin: initial; background-color: #ffffff; background-position: initial initial; margin: 8px;">\r\n<p><span style="font-weight: bold;">Your Account Has Been Unsuspended!<br /><span style="font-weight: normal;">This means that your site on control panel is now accessable. Your website and all its content still remain intact and can be accessed.</span></span></p>\r\n</div>\r\n</span></p>', 'This is the email a client recieves when their account has been unsuspended.'),
(7, 'newaccadmin', 'Waiting Admin Validation', 'Waiting Admin Validation', '<p><span style="font-weight: bold;">Your account is awaiting admin validation!<br /><span style="font-weight: normal;">Your account has been successfully created but you need to wait for an admin to approve your account! You''re now able to log into your client control panel. Your details are as follows:</span></span></p>
<p><span style="font-weight: bold;">Username:&nbsp;</span>%USER%<br /><span style="font-weight: bold;">Password:&nbsp;</span>%PASS%<br /><span style="font-weight: bold;">Email:&nbsp;</span>%EMAIL%<br /><span style="font-weight: bold;">Domain/Subdomain:&nbsp;</span>%DOMAIN%<br /><span style="font-weight: bold;">Package:&nbsp;</span>%PACKAGE%<br /></p></div></span></p>', 'This is the email a client gets when they first go though the order form and complete it but are awaiting the admin. This email should contain all they''re details.<br /><br />Template Variables:<br />%USER% - Client Username<br />%PASS% - Client Password<br />%EMAIL% - Client Email<br />%DOMAIN% - The clients package url<br />%PACKAGE% - The package the client signed up for<br />%CONFIRM% - The confirmation link'),
(8, 'adminval', 'Admin - User Needs Validating', 'User Awaiting Validation', '<p><span style="font-weight: bold;">A Client Is Awaiting Admin Validation!<br /><span style="font-weight: normal;">Log into your ACP now to approve or decline that client.</span></span></p>', 'This the email you recieve when a user has signed up to a package requiring admin validation and is awaiting it.'),
(9, 'approvedacc', 'Account Approved', 'Account Approved', '<p><span style="font-weight: bold;">Your account has now been approved!<br /><span style="font-weight: normal;">This means that your account is now active and the details that were sent in your previous email now work. You can proceed to your hosting control panel.</span></span></p>', 'This is the email the client recieves when they''re package has been validated by you.'),
(10, 'declinedacc', 'Declined Account', 'Account Declined', '<p><span style="font-weight: bold;">Your account has been declined!<br /><span style="font-weight: normal;">An admin has declined your account. Your record has been deleted from the database. You may try and signup again.</span></span></p>', 'This is the email recieved when you decline a account that needs admin validation.'),
(11, 'p2hwarning', 'Post 2 Host - Posts Warning', 'Monthly Posts Warning', '<p><strong>You haven''t posted enough!<br /></strong>You have posted a total of %USERPOSTS%. %MONTHLY% is required for your package!</p>', 'This is the email a client recieves on the 20th of the month if they haven''t posted enough posts to keep their account.<br />\r\n\r\nTemplate Variables:\r\n%USERPOSTS% - The posts the user has done\r\n%MONTHLY% - The posts required'),
(12, 'new ticket', 'Admin - New Ticket', 'New Ticket', '<p><strong>A Client has posted a new ticket!<br /></strong>The ticket is awaiting your staff reply, please look at it ASAP. It''s details:<br /><br /><strong>Title:&nbsp;</strong>%TITLE%<br /><strong>Urgency:&nbsp;</strong>%URGENCY%<br /><strong>Content:&nbsp;</strong>%CONTENT%</p>', 'This is the email all staff members recieve when a ticket has been created. This is supposed to alert you to the ticket.<br /><br />\r\n\r\nEmail Variables:<br />\r\n%TITLE% - Ticket Title<br />\r\n%CONTENT% - Ticket Content<br />\r\n%URGENCY% - Ticket Urgency<br />'),
(13, 'new response', 'Admin - New Ticket Response', 'New Ticket Response', '<p><strong>%USER% has replied to the ticket ''%TITLE%'' and now is awaiting your response!<br /></strong>Please look at the ticket ASAP and try to resolve the problem.</p>', 'This email is sent when the CLIENT has replied to a ticket.<br /><br />\r\n\r\nTicket Variables:<br />\r\n%TITLE% - The response title<br />\r\n%CONTENT% - The response<br />\r\n%USER% - The user who posted it<br />'),
(14, 'clientresponse', 'New Ticket Response', 'New Ticket Response', '<p><strong>%STAFF% has replied to your ticket!<br /></strong>To view this ticket, please login to your support area and check. The title of his response is: %TITLE%</p>', 'This email is sent to the client when they''re ticket has been replied to by a staff member.<br /><br />\r\n\r\nTicket Variables:<br />\r\n%TITLE% - Reply Title<br />\r\n%CONTENT% - Reply Content<br />\r\n%STAFF% - Staff Member'),
(15, 'areset', 'Admin Reset Password', 'New ACP Password!', '<p><span style="font-weight: bold;">Your ACP password has been reset!<br /><span style="font-weight: normal;">New Password: %PASS%<br /><br /><span style="font-weight: bold;"><span style="color: #ff0000;">Notice!<br /><span style="color: #000000;"><span style="font-weight: normal;">If you didn''t use the forgot password feature, we suggest you log into admin CP with your new password and use a different email.</span></span></span></span></span></span></p>', 'This is the email an admin recieves when they reset their password.<br /><br />\r\n\r\nTemplate Variables:<br />\r\n%PASS% = Their new password'),
(16, 'newinvoice', 'New Invoice', 'New Invoice', '<p><strong>You have a new unpaid invoice on your account!<br /></strong>Make sure you log into your client area (%USER%) and pay the invoice.</p>\r\n<p><strong>Invoice Due: </strong>%DUE%</p>', 'This is the email a client receives when they''ve got a new unpaid invoice. There are certain variables:<br />\r\n%USER% - Username<br />\r\n%DUE% - The Invoice Due Date\r\n'),
(17, 'cancelacc', 'Account Cancelled', 'Cancelled', '<p><span style="font-weight: bold;">Your account has been cancelled!<br /><span style="font-weight: normal;">This now means that your client username and password no longer work and your web hosting package no longer exists. All your files, databases, records have been removed and aren''t retrievable.<br /><br /><span style="font-weight: bold;">Reason for cancellation: </span>%REASON%</span></span></p>', 'This is the email the client recives when their account has been cancelled by admin or when they manually cancel it in the client control panel.<br /><br />\r\n\r\nEmail Variables:<br />\r\n%REASON% - The reason why their account has been cancelled'),
(18, 'renewalnotice', 'Warning notification', 'Renewal notice', '<p>Dear %USERNAME%,<br /><br />This is a notice that an invoice has been generated on %INVOICE_CREATE_DATE%.<br /><br />Your payment method is: %PAYMENT_METHOD%</p><p>Invoice  #%INVOICE_NUMBER%<br />Amount Due: %AMOUNT%<br />Due Date: %INVOICE_DUE_DATE%.<br /><br />Invoice Items<br /><br />Package: %PACKAGE_INFO% - %DOMAIN%<br />Addons: %ADDONS%<br />%INVOICE_SUMMARY%</p><p>You can login to your client area to view and pay the invoice.<br /><br />1. Login at %URL%clients<br /><br />Username: %USER_LOGIN%<br />Password: **********<br /><br />2. Click "My Invoices"<br /><br />3. Click "Pay Now" located at the top right of the invoice.<br /><br />4. Complete the payment forms.<br /><br /><br />Kind regards,<br />%COMPANY_NAME%</p>','This is the email an user recieves when they create a new invoice<br /><br />\r\n\r\nTemplate Variables:<br />\r\n%PASS% = Their new password'),
(19, 'neworder', 'Order Confirmation', 'Order Confirmation', '<p>Dear %FIRSTNAME% %LASTNAME% ,<br />&nbsp;<br />Thank you for ordering a web hosting package from %SITENAME%.<br />&nbsp;<br />Your order has been received and submitted for review. In most instances, the review process should take no more than <strong>24 hours</strong> after payment has been received. If additional review of your order is deemed necessary, you will be notified by e-mail. In all instances, <br />%SITENAME% <br />will contact you via e-mail to communicate the status of your order. <br /><br /><span style="color: #ff0000;">After the order has been processed, an "Activation Notice" e-mail will be sent which will contain all information needed to use the web hosting package.</span><br />&nbsp;<br /><span style="color: #ff0000;">Orders are processed and setup within 24 hours after payment has been received. Please wait 24 hours after paying to receive the Activation Notice.</span><br />&nbsp;<br />The details of your order are:<br />&nbsp;<br />Order Number:<strong> %ORDER_ID%</strong><br />&nbsp;<br />Product/Service: %PACKAGE%<br />Addons&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : %ADDONS%<br />Domain: %DOMAIN%<br />Billing Cycle: %BILLING_CYCLE%<br /><br />Total Due Today: %TOTAL%<br />&nbsp;<br />When you placed your order, you indicated that you agree to the following statements:<br /><br />%TOS%<br />&nbsp;<br />Should you have any questions relating to the status of your order, please feel free to e-mail us at %ADMIN_EMAIL%. Please quote your order reference number if you wish to contact us about this order.<br />&nbsp;<br />&nbsp;<br />Kind regards,<br />%SITENAME%</p>','This is the email an user recieves when a new order is created<br /><br />\r\n\r\nTemplate Variables:<br />\r\n%PASS% = Their new password');


--
-- Table structure for table `%PRE%tickets`
--

CREATE TABLE IF NOT EXISTS `%PRE%tickets` (
  `id` mediumint(9) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `urgency` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `reply` mediumint(9) NOT NULL,
  `ticketid` mediumint(9) NOT NULL,
  `staff` mediumint(9) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `status` mediumint(9) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `%PRE%tickets`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%types`
--

CREATE TABLE IF NOT EXISTS `%PRE%types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(15) NOT NULL,
  `visual` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `%PRE%types`
--

INSERT INTO `%PRE%types` (`id`, `name`, `visual`) VALUES
(1, 'free', 'Free'),
(2, 'p2h', 'Post2Host'),
(3, 'paid', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%users`
--

CREATE TABLE IF NOT EXISTS `%PRE%users` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `ip` text NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `zip` varchar(7) NOT NULL,
  `state` varchar(55) NOT NULL,
  `country` varchar(2) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `status` varchar(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%users`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PRE%orders`
--

CREATE TABLE IF NOT EXISTS `%PRE%orders` (
  `id` mediumint(9) NOT NULL auto_increment,
  `userid` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `pid` varchar(5) NOT NULL,
  `signup` varchar(20) NOT NULL,
  `status` varchar(1) NOT NULL,
  `additional` text NOT NULL,
  `billing_cycle_id` int NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `%PRE%orders`
--

-- --------------------------------------------------------

--
-- Table structure for table `%PRE%logs`
--

CREATE TABLE IF NOT EXISTS `%PRE%logs` (
  `id` mediumint(9) NOT NULL auto_increment,
  `uid` varchar(5) NOT NULL,
  `loguser` varchar(50) NOT NULL,
  `logtime` varchar(20) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- New tables for BNPanel

CREATE TABLE `%PRE%billing_cycles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `number_months` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cycle_month` (`number_months`,`name`)
);


INSERT INTO `%PRE%billing_cycles` (`number_months`, `name`, `status`) VALUES
('12', 'Annually', '1'),
('6', 'Semiannually', '1'),
('1', 'Monthly', '1');


CREATE TABLE  `%PRE%billing_products` (
  `billing_id` int NOT NULL,
  `product_id` int NOT NULL,
  `amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `type` varchar(255) NOT NULL
);

CREATE TABLE  `%PRE%addons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `setup_fee` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `description` varchar(255) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE  `%PRE%package_addons` (
  `package_id` int NOT NULL DEFAULT '0',
  `addon_id` int NOT NULL DEFAULT '0'
);

CREATE TABLE `%PRE%order_addons` (
  `order_id` int NOT NULL,
  `addon_id` int NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
);



CREATE TABLE `%PRE%order_invoices` (
  `id` INT  NOT NULL AUTO_INCREMENT,
  `order_id` INT  NOT NULL,
  `invoice_id` INT  NOT NULL,
  PRIMARY KEY (`id`)
);

