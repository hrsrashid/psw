# Password manager web app (_depricated_ 😁)

My first project in the beginning of my programming aspirations as a school kid from maybe around 2010, so it's messy as all our first tries.

jQuery library might been updated some time ago.

It was Dockerized recently. For launching `docker-compose up`

Go `localhost` to visit main page (change theme with left bottom button)  
Go `localhost/mobile` to visit mobile verison (jQuery Mobile v1.0b2)

Backend contains temporary (as I thought of it) code to convert old users with their data stored as plain text to new ones with encrypted data with AES-256,  
which involves recreation of SQLite database (because as I found out it still stores deleted data),  
updating `index.php` (replaces old database name with new one; should've used separate config file),  
but deleting old database file is seems to be broken (maybe it can't unlink file in mounted docker volume).

## Challenge

As I had trusted it with my real passwords long time ago, I'm wondering how vulnerable it is.  
I had used prepared SQL queries, so I think no way for SQL-injection.  
Also each user supposedly doesn't see each other's content, so I ruled out XSS too.  
But I'm sure it's vulnerable as hell. 
