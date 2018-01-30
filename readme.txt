=== My auctions allegro FREE EDITION ===
Contributors: grojanteam
Tags: allegro, wordpress, import, auction, auctions, display
Requires at least: 4.0
Tested up to: 4.9.2
Stable tag: 1.6.1
Support Link: https://grojanteam.pl/
Demo Link: https://grojanteam.pl/wp
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X8DEMZYH2MT9W
Text Domain: gj_myauctions_allegro
Domain Path: /

== Description ==

Plug-in display auctions from popular polish auction website called allegro.pl 

== Support ==

1. Upload plug-in to wp-content/plugins
2. Enable plug-in in your panel administration
3. Go to My auctions allegro and configure connection with allegro API
4. You can fill all  fields but only Web API Key is optional
5. Go to My auctions allegro - auction settings
6. Add a new one and set your configuration of imported auctions
7. GO to My auctions allegro - Import
8. Choose your profile and click send
9. After import you can create a widget where you will show auctions
10. Also you can create a shortcode. Choose allegro icon from icons, and set-up your shortcode.
11. Then you can see how it looks on front of your WordPress.

== Screenshots ==

1. assets/screenshot-1.png
2. assets/screenshot-2.png
3. assets/screenshot-3.png
4. assets/screenshot-4.png
5. assets/screenshot-5.png
6. assets/screenshot-6.png
7. assets/screenshot-7.png
8. assets/screenshot-8.png
9. assets/screenshot-9.png
10. assets/screenshot-10.png
11. assets/screenshot-11.png

== Frequently Asked Questions ==

Q: How to import auctions from allegro
A: First, we need to add allegro profile to your plug-in. Go to My auctions allegro -> Auction settings. Click add, set your profile and click save. Then you can go to import auctions and choose your added profile and click "Send" to import your auctions.

== Change Log ==

= 1.6.1 =
- Fix synchronize auctions using cron

= 1.6 =
- Added cron job to sync auctions once per day for profile

= 1.5 =
- Compatible with PHP 7
- Fixed Category Update
- Fixed translations
- Fixed widget form

= 1.4.1 =
- Removed feedback list

= 1.4 =
- Support Google Structural Data

= 1.3.5 =
- Not supported aukro.cz (bug with getting sites) FIXED

= 1.3.4 =
- Added new option of auction sort

= 1.3.3 =
- Fix shortcode popup

= 1.3.2 =
- Fix import categories for all countries
- Fix import auctions from Aukro.cz

= 1.3.1 =
- Fix encryption password stored in database
- Update categories will be change automatically

= 1.3 =
- Added to view link `details`
- You can choose that you want to show link `details`
- Click on `details` show your html auction in popup
- Fix to show more than 10 auctions

= 1.2.2 =
- Import auctions from allegro with details or not, before start import you need to choose

= 1.2.1 =
- Fix category loading when you edit existing settings of auctions

= 1.2.0.1 =
- Fix translations for plug-in

= 1.2 =
- Added to WordPress catalog plugins (Refactor)

= 1.1 =
- possibility to show comments from allegro profile
- preparing database to showing details of auction on WordPress Page / Post
- add 2nd stage of importing auctions to WordPress (details)

= 1.0 =
- possibility to add unlimited auction settings
- possibility to import auctions from allegro
- possibility to show auctions as widget
- possibility to show auctions on post/page as ShortCode
- possibility to set up settings of auctions based on category, query search or type of auction