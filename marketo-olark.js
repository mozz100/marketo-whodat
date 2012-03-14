// defining functions in the Global (window) scope is bad, so do things within a namespace
// (see http://stackoverflow.com/questions/881515/javascript-namespace-declaration)

var MarketoOlark = {

doIntegration: function() {
    // Get Marketo data and pass to oLark.
    this.whodat('/mkto/whodat.php');       // make sure to pass in the correct path to the PHP file
    
    // Also get Search data from Google Analytics and pass to oLark.
    // _gaq.push(fn) means execute the function after Google Analytics has finished doing its thing.
    // Since we're within 'initStorefront' we can be sure that this is safe to do.  If GA hasn't loaded for
    // any reason, this'll just be added to the end of its queue.
    _gaq.push(this.whatsearch);
},

// readCookie from http://www.quirksmode.org/js/cookies.html
readCookie: function(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
},

// Use the information retrieved from Marketo to update oLark.
// For docs on the Olark API, see http://www.olark.com/developer
updateOlarkUserInfo: function(r) {

    // only try to do anything if Marketo has returned a positive result and we have data
    if (r.result && r.data) {
        
        // Grab name, email, marketo id if present
        var name = [r.data.FirstName, r.data.LastName].join(' ');
        var email = r.data.Email;
        var mktoid = r.data.Id;
        
        // Grab company and fallback to inferred company if no company is present.
        company = (r.data.Company) ? r.data.Company : r.data.InferredCompany;
        if (company == '[not provided]') { company = '' }
        
        // Grab country/inferred country (not used currently)
        country = (r.data.Country) ? r.data.Country : r.data.InferredCountry;

        if (name != ' ') {
            // We have a name.  So build a string...
            // e.g. "Richard Morrison, Acaso Analytics, Marketo ID: 1234".
            var olarkname = name + (company ? ', ' + company : '') + (mktoid ? ', Marketo ID: ' + mktoid : '');
            
            // ...and put it into oLark's FullName field.
            olark('api.visitor.updateFullName', {fullName: olarkname });
        } else {
            // No name found.  Try and at least put their MarketoID into oLark
            if (mktoid) {
                olark('api.visitor.updateFullName', {fullName: 'Marketo ID: ' + mktoid });
            }
        }
        // update oLark with the person's email address, if found in the Marketo data
        if (email) {
            olark('api.visitor.updateEmailAddress', {emailAddress: email});
        }
    }
},

whodatError: function(jqxhr, textStatus, errorThrown) {
    // If we get here, then something's gone wrong calling whodat.php
    //console.log('error', jqxhr, textStatus, errorThrown);
},

// This function gets called until the Marketo cookie is found.
// (Not aware of a "Munchkin finished" callback.)
whodat: function(url_to_whodat_php) {
    // Is the marketo cookie present yet?
    if (this.readCookie('_mkto_trk')) {
        // It is, so use it in a call to whodat.php and look up this user using the Marketo SOAP API.
        $jq.ajax({
            type: 'GET',
            url:url_to_whodat_php, 
            dataType: 'json', 
            error: this.whodatError,
            timeout: 30*1000,              // in ms
            success: this.updateOlarkUserInfo   // on successful response, update oLark with data
        });
    } else {
        // No, try again in 1 second == 1000 ms
        setTimeout(this.whodat, 1000);
    }
},

// What did this user search for in order to find our site?
// Google Analytics cookies have the answer.  Get the info and pass it to oLark.
// Adapted from code on http://stackoverflow.com/questions/5631830/get-the-referrer-paid-natural-and-keywords-for-the-current-visitor-with-google
whatsearch: function() {
    var utmz = this.readCookie('__utmz'); //using a cookie reading function
    // is the GA cookie present yet?
    if (utmz) {
        // perform string manipulation on the contents of the GA cookie to get the info we need.
        var ggl_vals = (function() {
            var pairs = utmz.split('.').slice(4).join('.').split('|');
            var ga = {};
            for (var i = 0; i < pairs.length; i++) {
                var temp = pairs[i].split('=');
                    ga[temp[0]] = temp[1];
            }
            return ga;
        })();

        // ggl_vals.utmctr will contain the search phrase, if known.  Update oLark.
        if (ggl_vals.utmctr) {
            olark('api.chat.updateVisitorStatus', {
                snippet: 'This person searched for "' + unescape(ggl_vals.utmctr) + '"'
            });
        } else {
            // do nothing
        }
    }
}

};
