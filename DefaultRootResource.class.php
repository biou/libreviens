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
 * root resource class
 * lists all services
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class DefaultRootResource implements RESTRootResource {
 	/**
 	 * list of resources
 	 */
	private $listResources;	

 	/**
 	 * reference to the request object
 	 */	
	private	$request;

 	/**
 	 * Constructor
 	 * @param	HttpRequest		$pReq			request
 	 */
    public function __construct(RESTHttpRequest $pReq) {
    	$this->request = $pReq;
    }
    
 	/**
 	 * list of items (this method is optional)
 	 * @param	string		$pLogin	login
 	 * @param	string		$pPass	password
 	 * @return	boolean				true if the login is successful or if it is a public resource 
 	 */    
    public function auth($pLogin, $pPass) {
    	return true;
    }

 	/**
 	 * list of services
 	 * GET /
 	 * or HEAD /
 	 * @param	boolean	$pHead	true if we return only headers
 	 */
    public function index($pHead = false) {
		if ($pHead) {
			return;
		}
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo '<html><head><title>List of services</title></head><body><ul>';
		foreach (ServiceEnumerator::listDefinedResources() as $i) {
			echo '<li><a href="./'.$i.'/">'.$i.'</a></li>';
		}
		echo '</ul></body></html>';
    }
}
?>