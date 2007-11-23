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
 * REST Exception, handles http error codes
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class RESTException extends Exception
{
	
	/** 
	 * http status code
	 * we do not use directly the error code which is in the Exception class, 
	 * because it can be used for other things
	 */
	public $code;
	
	/** 
	 * additional headers
	 */	
	public $additional_headers;

	/**
	 * Constructor
	 * @param	string		$pMsg			Error message
	 * @param	string		$pErrorCode		HTTP Error Code
	 * @param	array		$pAddHeaders	additional headers
	 */
	function __construct($pMsg, $pErrorCode = 500, $pAddHeaders = array()) {
		parent::__construct($pMsg);
		$this->code = $pErrorCode;
		$this->additional_headers = $pAddHeaders;
	}
	
	public function getStatusCode() {
		return $this->code;
	}

	public function getAdditionalHeaders() {
		return $this->additional_headers;
	}
	
	public function sendError() {
		$message	= $this->getMessage();
		$code		= $this->getStatusCode();
		header("HTTP/1.0 ".HttpStatus::getMessage($code));
		foreach ($this->getAdditionalHeaders() as $k => $v) {
			header($k.': '.$v, false);
		}
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo '<html>';
		echo '<head><title>Error</title></head>';
		echo '<body><p>Error '.httpStatus::getMessage($code).'</p>';
		echo '<p>'.$this->getMessage().'</p></body>';
		echo '</html>';
	}
}	

?>