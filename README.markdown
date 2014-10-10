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

Example use
===========

I've used this to link Marketo through to Olark (www.olark.com).  That means we can see:

* who's on the website right now (names and emails, if Marketo knows 'em)
* what they searched for
* whether they've been before
* which page they're viewing

Also, we can use Olark's !push command to send the visitor to a Marketo form, capturing their details in an integrated fashion (they end up correctly associated in salesforce.com).

To use the Marketo-Olark integration:

* include marketo-olark.js in your page (check that the URL for the PHP is correct within the .js file), ideally above the Olark code
* below the Olark code, include this in your page (Olark callbacks need to execute within an anon function, I haven't got to the bottom of that, yet):

<pre>
&lt;script type="text/javascript"&gt;
// has to be on the page below/after olark code
olark('api.box.onShow', function() { MarketoOlark.doIntegration(); });
olark('api.box.onHide', function() { MarketoOlark.doIntegration(); });
&lt;/script&gt;
</pre>


#### How the Olark integration works

The `whodat()` function (defined in `marketo-Olark.js`) is called repeatedly until it detects the Marketo cookie called `mkto_track`.

When the Marketo cookie is known to be present, make an AJAX GET request to `whodat.php`.

The browser will send the Marketo cookie to `whodat.php` as part of the GET request, and PHP can use the Marketo API to look up information about the user. It returns the information as JSON data.

Javascript uses the JSON data and passes it to the `updateOlarkUserInfo` function, which uses Olark's API to share the data (first name, last name, company, country/inferred country) over to the chat.  If first name and last name are unknown, and `useMktoIDifNoName` is set (it defaults to `false`), then Olark is given the string `'Marketo ID: x'` as the full name instead.

Licence
-------

<a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/uk/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/2.0/uk/88x31.png" /></a><br />This work by <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Richard Morrison</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/uk/">Creative Commons Attribution-ShareAlike 2.0 UK: England &amp; Wales License</a>.<br />Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="https://github.com/mozz100/marketo-whodat" rel="dct:source">github.com</a>.
