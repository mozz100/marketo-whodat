While using Marketo's Munchkin API I realised how useful it would be to be able to fetch info about the currently-browsing user, and alter the page to suit.

* personalise offers
* restrict access to premium content
* remind people to register for events they've not joined yet
* and so on...

Turns out Munchkin doesn't have this ability yet, so I adapted some code from the Marketo Community site to get the job done.

PHP code here calls the Marketo SOAP API and looks up the current user based on their marketo cookie.
If your pages are already built dynamically and you're ok with the speed hit, you could just use the info as you're building the page.
It was too slow for me, so instead, I wrote my PHP to return the info in JSON format.
Then, I wrote some Javascript to call that PHP: it gets back info about who's looking at the page.

I started from the code here, and didn't have to do a great deal more: http://community.marketo.com/MarketoArticle?id=kA050000000Kyqw

How to use
==========

* Put this folder on your webserver.
* Copy settings.sample.php to settings.php and put real values in.
* Visit /path/to/whodat.php in your browser: you should see JSON output.

Licence
-------

<a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/uk/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/2.0/uk/88x31.png" /></a><br />This work by <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Richard Morrison</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/uk/">Creative Commons Attribution-ShareAlike 2.0 UK: England &amp; Wales License</a>.<br />Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="https://github.com/mozz100/marketo-whodat" rel="dct:source">github.com</a>.
