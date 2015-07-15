# How to link BNPanel Packages with ISPConfig3? #

Web hosting Packages (Packages) are created by the BNP Admin in BNPanel. You can add a description and features in plain HTML (hard disk quota, bandwidth, #databases, etc ) as well as a price depending of the Billing cycle.

Packages make sense because they have a "backend\_id" that is related with a "Client Template" in ISPConfig3.

In ISPConfig3, you can create "Client templates". They define the limits that a user can manage i.e 10 databases, 10 domains, 100 Mb hd quota, etc. You can check those values in ISPConfig3 but also in BNPanel if the backend\_id is correctly set. (Go to Package- > Edit Package)

## How to get the client template id in ISPConfig3? ##

This is difficult for non-developers: check the database table "client\_temaplate" in ISPConfig3, or check the id with Firebug (Firefox Extension) while editing a Client Template in ISPConfig3.

We will soon add a feature to detect ISPConfig3 Client Templates using the Remoting class.