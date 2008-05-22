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
 * RESTC Rest Controller
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class RESTC
{

 	/**
 	 * reference to the request object
 	 */
	private $request;

 	/**
 	 * name of the application
 	 */	
	private $realm;
	
 	/**
 	 * list of resources classes handled by this controller
 	 * (according to rest, a resource is what can be identified by an URL, a resource class can handle a set of resources)
 	 */	
	private $listResources;

 	/**
 	 * special resource class, that handle the listing of resources that are binded to the root
 	 */
	private $rootResource;

 	/**
 	 * Constructor
 	 * @param	HttpRequest		$pReq		request
 	 * @param	HttpRequest		$pRootResource	root resource
 	 * @param	HttpRequest		$pRealm		name of the application
 	 */
	public function __construct(RESTHttpRequest $pReq, $pRootResource, $pRealm) {
		$this->request = $pReq;
		// we compute the list of resources classes
		$this->listResources = ServiceEnumerator::listDefinedResources();
		$this->realm = $pRealm;
		$this->rootResource = $pRootResource;
	}

 	/**
 	 * Resource dispatcher
 	 */
	public function dispatch() {
		$path = $this->request->getPath();
		$method = $this->request->getMethod();
		$emulated_method = '';
		// we emulate PUT and DELETE methods on POST, so as to support html forms
		if (isset($_POST['_method'])) {
			$emu_method = $_POST['_method'];
		}
		if (isset($_GET['_method']) && ($method == 'POST')) {
			$emu_method = $_GET['_method'];
		}
		if (!empty($emu_method)) {
			$emu_method = strtolower($emu_method);
			if ($emu_method == 'put') {
				$method = "PUT";
			} else if ($emu_method == 'delete') {
				$method = "DELETE";
			}
		}

		// if we are at the root level
		if (empty($path) || ($path == '/')) { 
			// display the list of available resources if the method is GET and the client is authenticated

			// true if the client is authenticated
			$authd = $this->rootResource->auth($this->request->getLogin(), $this->request->getPassword());
			if (!$authd) {
				if ($method == 'OPTIONS') {
					$this->sendOptionsResponse(array()); 
				} 				
				throw new RESTException('Unauthorized', 401, array('WWW-Authenticate' => 'Basic realm="'.$this->realm.'"'));
			}

			if ($method == 'OPTIONS') {
				$this->sendOptionsResponse(array('GET', 'HEAD'));
			}

			if ($method != 'GET' && $method != 'HEAD') {
				throw new RESTException('', 405);	
			}

			$this->rootResource->index(($method == 'HEAD'));
		} else {
			// otherwise analyze the URL and pass the request to a resource class
			
			// URL sanitizing
			$path = substr($path, 1);
			// delete trailing slash
			if (substr($path, -1, 1) == '/') {
				$path = substr($path, 0, strlen($path)-1);
			}
			// tabPath is an array of "path variables"
			$limit = strpos($path,"http:");
			if ($limit===false){
				$path_ = $path;
			}
			else {
				$path_ = substr($path,0,$limit-1);
			}
			
			$tabPath = explode('/', $path_);
			
			if($limit!==false) {
				$newinst = substr($path,$limit,strlen($path));
				$tabPath[] = $newinst;
			}
	
			// call to the URL analysis
			$new = false;
			$edit = false;
			$ext = '';
			$res = $this->analyzeURL($tabPath, $ext, $new, $edit); 

			// we isolate the last couple, as this is the one that will handle the request
			// the $res array contains the rest of the parsed path variables
			$the_last = $res[count($res)-1];
			unset($res[count($res)-1]);
			$res_name = $the_last[0];
			$id = (isset($the_last[1]))?$the_last[1]:'';
			
			// we instanciate the resource class
			if (defined('RESOURCES_NS')) {	
				$res_name = RESOURCES_NS.$res_name;
			}
			$resource = new $res_name($this->request, $ext, $res);

			// true if the client is authenticated
			$authd = $resource->auth($this->request->getLogin(), $this->request->getPassword());
			if (!$authd) {
				if ($method == 'OPTIONS') {
					$this->sendOptionsResponse(array()); 
				} 
				throw new RESTException('Unauthorized', 401, array('WWW-Authenticate' => 'Basic realm="'.$this->realm.'"'));
			}

			// choice of the method 
			if ($method == 'GET') {
				if (empty($id)) {
					if (!$new) {
						if (!method_exists($resource, 'index')) {
							throw new RESTException('', 405);
						}
						$resource->index();
					} else {
						if (!method_exists($resource, 'formNew')) {
							throw new RESTException('', 405);
						}
						$resource->formNew();
					}
				} else {
					if (!$edit) {
						if (!method_exists($resource, 'show')) {
							throw new RESTException('', 405);
						}
						$resource->show($id);
					} else {
						if (!method_exists($resource, 'formEdit')) {
							throw new RESTException('', 405);
						}
						$resource->formEdit($id);
					}					
				}
			} else if ($method == 'HEAD') {
				if (empty($id)) {
					if (!$new) {
						if (!method_exists($resource, 'index')) {
							throw new RESTException('', 405);
						}
						$resource->index(true);
					} else {
						if (!method_exists($resource, 'formNew')) {
							throw new RESTException('', 405);
						}
						$resource->formNew(true);
					}
				} else {
					if (!$edit) {
						if (!method_exists($resource, 'show')) {
							throw new RESTException('', 405);
						}
						$resource->show($id, true);
					} else {
						if (!method_exists($resource, 'formEdit')) {
							throw new RESTException('', 405);
						}
						$resource->formEdit($id, true);
					}					
				}
			} else if ($method == 'POST') {
				if (!method_exists($resource, 'create')) {
					throw new RESTException('', 405);
				}
				$resource->create();
			} else if ($method == 'PUT') {
				if (!method_exists($resource, 'update')) {
					throw new RESTException('', 405);
				}				
				$resource->update($id);
			} else if ($method == 'DELETE') {
				if (!method_exists($resource, 'delete')) {
					throw new RESTException('', 405);
				}
				$resource->delete($id);
			} else if ($method == 'OPTIONS') {
				$methods = array();
				if (empty($id)) {
					if (!$new) {
						if (method_exists($resource, 'index')) {
							$methods[] = 'GET';
							$methods[] = 'HEAD';
						}
						if (method_exists($resource, 'create')) {
							$methods[] = 'POST';
						}
					} else {
						if (method_exists($resource, 'formNew')) {
							$methods[] = 'GET';
							$methods[] = 'HEAD';
						}
					}
				} else {
					if (!$edit) {
						if (method_exists($resource, 'show')) {
							$methods[] = 'GET';
							$methods[] = 'HEAD';
						}
						if (method_exists($resource, 'update')) {
							$methods[] = 'PUT';
						}
						if (method_exists($resource, 'delete')) {
							$methods[] = 'DELETE';
						}
					} else {
						if (method_exists($resource, 'formEdit')) {
							$methods[] = 'GET';
							$methods[] = 'HEAD';
						}
					}					
				}
				$this->sendOptionsResponse($methods);
			}
		}
	}

 	/**
 	 * URL parser
 	 * @param	array		$pTabPath		array of "path variables"
 	 * @param	string		$pExt			extension asked in the url (return)
 	 * @param	boolean		$pNew			if the url is ended by ";new" (return)
 	 * @param	boolean		$pEdit			if the url is ended by ";edit" (return)
 	 * @return	array					a list of couples (e1, e2) where e1 is a resource class and e2 is an id of a resource (if any)
 	 */	
	private function analyzeURL($pTabPath, &$pExt, &$pNew, &$pEdit) {
		// we iterate on $pTabpath and we call analyzeCouple on each couple
		$n = count($pTabPath);		
		$path = array();
		for ($i = 0; $i < $n ; $i=$i+2) {
			if (isset($pTabPath[$i])) {
				$final = (!isset($pTabPath[$i+2]));
				if (empty($pTabPath[$i+1])) {
					$pTabPath[$i+1] = '';
				}
				$res = $this->analyzeCouple($pTabPath[$i], $pTabPath[$i+1], $final);
				if (!isset($res['e2'])) {
					$res['e2'] = '';
				}
				$path[] = array($res['e1'], $res['e2']);
				if ($final) {
					if (isset($res['new']) && $res['new']) {
						$pNew = true;
					}
					if (isset($res['edit']) && $res['edit']) {
						$pEdit = true;
					}
					if (isset($res['ext']) && $res['ext']) {
						$pExt = $res['ext'];
					}
				}
			} else {
				break;
			}
		}
		
		return $path;
	}
	
 	/**
 	 * analyzeCouple
 	 * @param	string		$pE1		first element (resource class)
 	 * @param	string		$pE2		second element (id of a resource)
 	 * @param	boolean		$pFinal		if this couple is the last of the list (at the end of the URL)
	 * @return	array				an associative array containing all the properties relative to this couple
  	 */	
	private function analyzeCouple($pE1, $pE2, $pFinal) {
		$result = array();
		// if no id 
		if (empty($pE2)) {
			// if this is the last element
			if ($pFinal) {
				$elt = $this->analyzeFinalElement($pE1);
				$pE1 = $elt['elem'];
				unset($elt['elem']);
				$result = $elt;
			}
			// test if this is a known resource class
			if (!in_array($pE1, $this->listResources)) {
					throw new RESTException('Unknown Resource '.$pE1, 404);
			}
			// all is ok, store the element
			$result['e1'] = $pE1;
		} else {
			// an id is present
			// if this is the last element
			if ($pFinal) {
				$elt = $this->analyzeFinalElement($pE2);
				$pE2 = $elt['elem'];
				unset($elt['elem']);
				$result = $elt;
			}
			// test if the first element is a known resource class
			if (!in_array($pE1, $this->listResources)) {
					throw new RESTException('Unknown Resource: '.$pE1, 404);
			}
			
			// if the method isId is present in the resource class
			// we test the validity of the id			
			if (is_callable(array($pE1, 'isId'), false)) {
				$isId = call_user_func(array($pE1, 'isId'), $pE2);
				if ($isId === false) {
					throw new RESTException('Unable to check id validity', 500);
				} else if ($isId === 0) {
					throw new RESTException('Bad Id: '.$pE2, 400);
				}
			}
			// if ok, store the results
			$result['e1'] = $pE1;
			$result['e2'] = $pE2;
		}
		return $result;
	}

 	/**
 	 * analyzes the final element of the url (file extension, ";new" and ";edit")
 	 * @param	string		$pE		final element of the URL
 	 * @return	array				an associative array containing all the properties of this element
 	 */	
	private function analyzeFinalElement($pE) {
		$result = array();
		// handling of the create and edit forms
		if (strpos($pE, ';edit')) {
			$result['edit'] = true;
			$pE = str_replace(';edit', '', $pE);
		} else if (strpos($pE, ';new')) {
			$result['new'] = true;
			$pE = str_replace(';new', '', $pE);
		}
		if ($p = strrpos($pE, '.')) {
			// handling of the extension
			$ext = substr($pE, $p+1, strlen($pE));
			$result['ext'] = $ext;
			$elem = substr($pE, 0, $p);
			$result['elem'] = $elem;
		} else {
			$result['elem'] = $pE;
		}
		return $result;
	}

 	/**
 	 * send response to OPTIONS request
 	 * @param	array		$pTab		array of methods
 	 */	
	private function sendOptionsResponse($pTab) {
		$str = 'Allow: ';
		foreach ($pTab as $k => $v) {
				$str .= $v;
			if ($k != count($pTab)-1) {
				$str .= ', ';
			}
		}
		header($str);
		echo $str;
		exit;
	}
}

?>