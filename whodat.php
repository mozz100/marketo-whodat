<?php

print get_whodat_status();

/**
* Check to see if they user is valid Marketo lead.
* If they are, we add a permanant cookie so we don't need to hit the Marketo API again.
* If they are not we, add a session cookie.
* The cookies allow us to cache the API response.
*
* @param string $cookie
* @return whodat data (JSON)
*/

function get_whodat_status($cookie = 'whodat')
{
  
  include 'settings.php';  // load in variables containing Marketo secret key, etc.

  if (isset($_COOKIE[$cookie]))
  {
    // If the whodat cookie is already set, return its value.
    // Ideally, the cookie would just be a pointer/ID reference to something like memcache, or a 
    // database.  On our site we don't have that easily available, so for now just lazily store the
    // JSON in a cookie.

    // decode what's stored in the cookie and wrap it in some status JSON, returning 
    return json_encode(array(
      "result" => true,
      "from" => "cookie",
      "data" => json_decode($_COOKIE[$cookie])
    ));
  }
  else {
    // We access the Marketo API with the user's Marketo cookie to see who they are
    if(isset($_COOKIE['_mkto_trk'])) {
      // We only include the Marketo API class if it's needed.
      include_once('class.marketoapi.php');

      // What cookie type do we need to set?
      $cookie_type = 'session';

      $marketo_api = new MarketoAPI();
      $result = $marketo_api->getLead('COOKIE', $_COOKIE['_mkto_trk']);
      $data = $result->result->leadRecordList->leadRecord;
      $whodat = array("Id" => $data->Id, "Email" => $data->Email);
      // can we get a name?
      foreach ($data->leadAttributeList->attribute as $attribute) {
          //only record stuff we're truly interested in
          if ((bool)array_search($attribute->attrName, array("{dummy}","FirstName","LastName","InferredCompany","InferredCountry","Company","Country"))) {
              $whodat[$attribute->attrName] = $attribute->attrValue;
          }
      };
     
      // Save a cookie containing the whodat data, so that there's no need to hit the Marketo API
      // for future requests during this session.  (expire = 0)
      // Remember to configure cookie_domain in settings.php.
      setcookie($name = $cookie, $value = json_encode($whodat), $expire = 0, $path = '/', $domain = $cookie_domain);

      return json_encode(array(
        "result" => true, 
        "from" => "marketo", 
        "data" => $whodat
      ));
    }
    else
    {
      return '{"result": true, "from": null, "data": null, "msg": "No Marketo cookie"}';
    }
  }

  return '{"result": false}';  // tell the world that we found nothing.
}

?>
