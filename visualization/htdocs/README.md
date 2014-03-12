The code for Slate's [Gun Deaths visualization](http://www.slate.com/articles/news_and_politics/crime/2012/12/gun_death_tally_every_american_gun_death_since_newtown_sandy_hook_shooting.html).

<strong>lib/</strong> contains javscript libraries upon which this interactive relies.

<strong>index.php</strong> is designed to output HTML that is then copied and pasted into Slate's CMS. The interactive's HTML is static; index.php is NOT meant to run live. All victim data is retrieved via AJAX.