<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of libReviens.
# Copyright (c) 2007 Luc Dehand and Alain Vagner.
# All rights reserved.
#
# libReviens is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# libReviens is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with libReviens; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

/**
 * Http Request class
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class RESTHttpRequest {

 	/**
 	 * data passed to the REST web service
 	 */
	private $input;

 	/**
 	 * http method used on the resource (get, put post, delete, ...)
 	 */
	private $method;

 	/**
 	 * path of the resource
 	 */
	private $path;

 	/**
 	 * content_type of the input data
 	 */
	private $content_type;

 	/**
 	 * basic auth login
 	 */
	private $login;
	
 	/**
 	 * basic auth password
 	 */
	private $password;

 	/**
 	 * Constructor
 	 * @param	string		$pPathInfo	overrides server path_info (optional)
 	 */
	function __construct($pPathInfo = null) {
	    $this->input = file_get_contents('php://input');
		$this->method = strtoupper($_SERVER['REQUEST_METHOD']);
		
                if(!function_exists('apache_request_headers')) {
                    // defines apache_request_headers function for IIS.
                    function apache_request_headers() {
                        $headers = array();
                        foreach($_SERVER as $key => $value) {
                            if(substr($key, 0, 5) == 'HTTP_') {
                                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
                            }
                        }
                    return $headers;
                    }
                }
                $headers = apache_request_headers();

		$this->content_type = (isset($headers['Content-Type']))?$headers['Content-Type']:'';
		// auth handling
		$authd = -1;
		if (function_exists('_rest_custom_auth')) {
			// custom auth function
			// If a custom auth is necessary, it is possible to define this kind of function
			// before this library is included.
			// It must have in parameter an associative array containing references on 3 variables :
			// authd: authentication succeeded ? 0 = false, 1 = true, -1 not applicable
			// login: the login of the user
			// pass: the pass of the user
			// These variables are used to return results.
			$login = '';
			$pass = '';
			$result = array('authd' => &$authd, 'login' => &$login, 'pass' => &$pass);
			call_user_func('_rest_custom_auth', array(&$result));
		}
		if ($authd === 1) {
			$this->login = $login;
			$this->password = $pass;				
		} else if ($authd === 0) {
			throw new RESTException('Invalid Authorization token', 409);
		} else if (isset($_GET['auth'])) {
			// http auth emulation on get for bad clients
			// die IE, die ! :)			
			$str = base64_decode($_GET['auth'], true);
			if ($str !== false) {
				$credentials = explode(':', $str);
				if (count($credentials) == 2) {
					$this->login = trim($credentials[0]);
					$this->password = trim($credentials[1]);
				} else {
					throw new RESTException('Invalid Authorization token', 409);
				}
			} else {
				throw new RESTException('Invalid Authorization token', 409);
			}
			unset($_GET['auth']);
		} else if (isset($headers['Authorization'])) {
			// http basic auth handling
					
			$matches = array();
			preg_match('/^Basic (.+)/', $headers['Authorization'], $matches);
			if (isset($matches[1])) {
				$str = base64_decode($matches[1], true);
				if ($str !== false) {
					$credentials = explode(':', $str);
					if (count($credentials) == 2) {
						$this->login = $credentials[0];
						$this->password = $credentials[1];
					} else {
						throw new RESTException('Invalid Authorization token', 409);
					}
				} else {
					throw new RESTException('Invalid Authorization token', 409);
				}
			} else {
				throw new RESTException('Authorization type not handled', 409);
			}
		} else {
			$this->login = '';
			$this->password = '';
		}
		if ($pPathInfo != null) {
			$this->path = $pPathInfo;
		} else {
			if (isset($_SERVER['PATH_INFO'])) {
				$this->path = $_SERVER['PATH_INFO'];
			} else {
				$this->path = '';
			}
		}
		// delete query string
		if (strpos($this->path, '?')) {
			$tmp = explode('?',$this->path);
			$this->path = $tmp[0];
		}
		
		// secure path
		$unsecure_path = explode('/', $this->path);
		$secure_path = array();
		foreach ($unsecure_path as $i) {
			$secure_path[] = trim(stripslashes(htmlentities($i)));
		}
		$this->path = implode('/', $secure_path);
		
		// secure GET, POST
		foreach ($_GET as $k => $v) {
			unset($_GET[$k]);
			$k = trim(stripslashes(htmlentities($k)));
			$v = trim(stripslashes(htmlentities($v)));
			$_GET[$k] = $v;
		}
		foreach ($_POST as $k => $v) {
			unset($_POST[$k]);
			$k = trim(stripslashes(htmlentities($k)));
			$v = trim(stripslashes(htmlentities($v)));
			$_POST[$k] = $v;			
		}
	}

 	/**
 	 * Returns raw input data
 	 * @return	string	raw input data
 	 */
	public function getInput() {
		return $this->input;
	}

 	/**
 	 * Returns http method
 	 * @return	string	http method
 	 */
	public function getMethod() {
		return $this->method;
	}
	
 	/**
 	 * Returns resource path
 	 * @return	string 	path
 	 */	
	public function getPath() {
		return $this->path;
	}

 	/**
 	 * Returns input content type
 	 * @return	string	Content Type
 	 */
	public function getContentType() {
		return $this->content_type;
	}
	
 	/**
 	 * Returns basic auth login
 	 * @return	string	login
 	 */
	public function getLogin() {
		return $this->login;
	}
	
 	/**
 	 * Returns basic auth password
 	 * @return	string	password
 	 */
	public function getPassword() {
		return $this->password;
	}		
	
	/**
	 * This function gives you access to the parsed and validated input DOMDocument
	 * @param	string	$pSchema	path to the schema file used to validate the input document
	 * @return	DOMDocument			dom of the input document
	 */
	public function getInputDOM($pSchema = null)
	{
	 	/* Enable user error handling */
	 	libxml_use_internal_errors(true);
	 	
	 	$doc = new DOMDocument();
	 	$doc->preserveWhiteSpace = false;
	 	
	 	/* load the XML Schema using our own Error Handler */
	 	if(!@$doc->loadXML($this->input))
	 	{
	 		$message = ": ";
	 		$errors = libxml_get_errors();
	 		foreach ($errors as $error) {
	 			$message .= "(" . $error->code . ")" . trim($error->message) . " on line " . $error->line . "; ";
			}
			libxml_clear_errors();
			
			/* there is a problem with the XML, throw a 400 BAD REQUEST*/
	 		throw new RESTException("XML Parse Error".$message, 400);
	 	}
	 	
	 	/* validate the XML Schema using our own Error Handler */
	 	if(isset($pSchema) && !@$doc->schemaValidate($pSchema))
	 	{
	 		$message = ": ";
	 		$errors = libxml_get_errors();
	 		foreach ($errors as $error) {
	 			$message .= "(" . $error->code . ")" . trim($error->message) . " on line " . $error->line . "; ";
			}
			libxml_clear_errors();
			
			/* there is a problem with the XML, throw a 400 BAD REQUEST*/
	 		throw new RESTException("XML Validation Error".$message, 400);
	 	}
	 	
	 	return $doc;
	}
}	
?>
