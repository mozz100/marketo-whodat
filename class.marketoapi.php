<?php


/**
* MarketoAPI
*/
class MarketoApi
{

  const DEBUG = FALSE;

	protected $access_key;
	protected $secret_key;
	
	public function __construct()
	{
		include 'settings.php';
		//
		// Your access key, secret key, and SOAP Endpoint are all available in the
		// Admin section of the Marketo Lead Management appliaction under "SOAP API Setup"
		//
		$this->access_key = $access_key;  // define this in settings.php
		$this->secret_key = $secret_key;  // define this in settings.php
		
		//
		// The endpoint is in the "SOAP API Setup" page in the Marketo Admin section
		// ex. $soap_end_point = 'https://xx-1.marketo.com/soap/mktows/';
		//
		$soap_end_point = $soap_end_point; // define this in settings.php

		//
		// Errors are sent to this email address.  Your web server
		// must be configured to send email for this to work correctly.
		//
		// ex. $this->error_email_address = 'example@example.com';
		//
		$this->error_email_address = $error_email_address; // define this in settings.php
		
		$options = array("connection_timeout" => 20, "location" => $soap_end_point);
		
		if (self::DEBUG) 
		{
			$options["trace"] = true;
		}

		$wsdl_url = $soap_end_point . '?WSDL';

		$this->soap_client = new soapClient($wsdl_url, $options);
	}
	
	private function _getAuthenticationHeader()
	{
		$timestamp      = date("c");
		$encrypt_string = $timestamp . $this->access_key;
		$signature      = hash_hmac('sha1', $encrypt_string, $this->secret_key);
		
		$attrs = array(
			'mktowsUserId'     => $this->access_key,
			'requestSignature' => $signature,
			'requestTimestamp' => $timestamp,
		);

		$header = new SoapHeader('http://www.marketo.com/mktows/', 'AuthenticationHeader', $attrs);
		
		return $header;
	}
	
	private function logLastRequest()
	{
		error_log(var_export($this->soap_client->__getLastRequest(), TRUE));
	}
	
	private function sendEmail($subject, $body)
	{
		return;
        $to 		= $this->error_email_address;
		$headers	= 'From: ' . $this->error_email_address . "\r\n" .
					  'Reply-To: ' . $this->error_email_address . "\r\n";
		mail($to, $subject, $body, $headers);
	}
	
	/**
	 * get lead record information
     * 
	 * @param str $key_type this should be either:
     * IDNUM - The Marketo lead ID
     * COOKIE - The entire _mkto_trk cookie
     * EMAIL - The email address of the user
     * SFDCLEADID The Salesforce lead id
     * 
	 * @param str $key_value
	 * @return object The Marketo lead record information
	 **/
	public function getLead($key_type, $key_value, $options = array())
	{
		$key = array(
			'leadKey' => array(
				'keyType'  => $key_type,
				'keyValue' => $key_value
			)
		);
		
		$auth_header = $this->_getAuthenticationHeader();

	    try 
		{
	      	$retval  = $this->soap_client->__soapCall('getLead', array($key), $options, $auth_header);
			// $this->logLastRequest();
		}
		catch (Exception $e)
		{
			// Code 20103 means the LeadKey value did not match any lead
			if (isset($e->detail->serviceException->code) && $e->detail->serviceException->code == '20103') 
			{
				$retval = FALSE;
			}
			else
			{
				$this->sendEmail('Marketo SOAP Exception', $e);
			}
		}
		
		return $retval;
		
	}
	
	/**
	 * See if the API response looks valid by testing for the bundle of returned attributes.
	 *
	 * @param array $response the complete response from the API call
	 * @return bool If response looks valid, TRUE, otherwise FALSE
	 **/
	public function doesResponseLookValid($result)
	{
		$retval = FALSE;

		// First we test the return value to see if it is set, then we test to see if it has returned at least one Marketo field.
		if ($result !== FALSE && isset($result->result->leadRecordList->leadRecord->leadAttributeList->attribute) && count($result->result->leadRecordList->leadRecord->leadAttributeList->attribute) > 0) 
		{
			$retval = TRUE;
		}

		return $retval;
	}

	/**
	 * See if a lead has an account by looking for SRCompanyID__c
	 *
	 * @param array $attributes the attributes array from getLead()
	 * @return bool TRUE if lead has account FALSE if not
	 **/
	public function doesLeadHaveAccount($attributes)
	{
		foreach ($attributes as $attribute) 
		{
			if ($attribute->attrName == 'SRCompanyID__c' && !empty($attribute->attrValue)) 
			{
				$retval = TRUE;
				break;
			}
			else
			{
				$retval = FALSE;
			}
		}
		
		return $retval;
	}

	/**
	 * See if a lead has an email address by looking for Email
	 *
	 * @param array $attributes the attributes array from getLead()
	 * @return bool TRUE if lead has an email address FALSE if not
	 **/
	public function doesLeadHaveEmailAddress($result)
	{
		$retval = FALSE;

		if (isset($result->result->leadRecordList->leadRecord->Email) && "" !== $result->result->leadRecordList->leadRecord->Email)
		{
			$retval = TRUE;
		}

		return $retval;
	}




	
}

?>
