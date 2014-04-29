dds-server
==========

The DDS Server


Installing a new DDS Server
===========================

To install a new DDS Server follow the following steps


1. Provision a new LAMP server on your preferred hardware (VM recommended).  You can make DDS server work on a WAMP or WIMP (hehe) Server but we recommend a LAMP environment.
2. Clone the dds-server repo into your web server's root directory `git clone https://github.com/crew/dds-server`
3. Create a new MySQL database and user with permissions full permissions to the new database 
        ```
           
        mysql -u root -p

        CREATE DATABASE <database name>;
        CREATE USER <database user>@localhost;
        SET PASSWORD FOR <database user>@localhost= PASSWORD("<password>");        
        GRANT ALL PRIVILEGES ON <database name>.* TO <database user>@localhost IDENTIFIED BY '<password>';
        FLUSH PRIVILEGES;
        
        exit
4. Now access the web server in a web browser *(Note use the actual way you want the pie's to address the server in this step i.e. If you want to use static ip addresses use the static IP here, if you want to use the FQDN of the server use that instead)*
5. Run the Install of WordPress following the directions on the screen.
6. Activate ALL the plugins on the Plugins Page
7. Start Creating PIEs, then groups, then Slides!


TODO:
* SSL Config
* Permissions
* Standard LAMP Config Notes

