# Glossary #
  * **Client**: A user that is registered in the BNPanel database.

  * **BNP Admin**: An administrator of the BNPanel system.

  * **Client Templates**: In ISPConfig3 we can create "Client templates". They define the limits that a user can manage i.e. 10 databases, 10 domains, 100 Mb HD quota, etc. We can check those values in ISPConfig3 but also in BNPanel if the backend\_id is correctly set. (Go to Package- > Edit Package). Check also this wiki page [BNPanelPackages](BNPanelPackages.md)

  * **Web hosting packages (Packages)**: Web hosting packages are created by the BNP Admin in BNPanel. He can add a description and features in plain HTML (hard disk quota, bandwidth, # of databases, etc ...) as well as a price depending on billing cycles. Packages have sense because they have a "backend\_id" that is related with a "Client Template" in ISPConfig. This is the main product that will be sold to a client.

  * **Addons: Addons are extra features that are related to a Package.** They do not have any direct implication in ISPConfig3.

  * **Order**: When a client buys a "Package" using BNPanel, a new order is generated in BNPanel. This order contains very important information for ISPConfig3 like the username and password (auto generated) to log in the ISPConfig3 system (if wanted). The order is sent to ISPConfig only if:

  1. BNP Admins sets an order with the Active status.
  1. A client pays the invoice via PayPal.

  * **Invoice**: After an order is submitted, a new Invoice is created with a "Pending" status. One order can have many invoices. But 1 invoice is related to 1 order.

  * **Billing cycle**: Is the quantity of months that BNPanel will check if an invoice is unpaid to send reminders to a user. Packages and addons are related.

  * **Domain**: When a user buys a package, he must own a domain.

  * **Site**: As soon as the user completes the whole cycle and has the web hosting package working, he has a functional "site".