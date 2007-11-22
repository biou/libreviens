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
 * Http Status
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class HttpStatus {

// from http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	public static $http_status_codes = array(
						# 1xx informational
						100 => 'Continue', 
						101 => 'Switching Protocols', 
						# 2xx Successful
						200 => 'OK',
						201 => 'Created',
						202 => 'Accepted',
						203 => 'Non-Authoritative Information',
						204 => 'No Content',
						205 => 'Reset Content',
						206 => 'Partial Content', 
						# 3xx Redirection
						300 => 'Multiple Choices', 
						301 => 'Moved Permanently', 
						302 => 'Found', 
						303 => 'See Other',
						304 => 'Not Modified',
						305 => 'Use Proxy', 
						307 => 'Temporary Redirect', 
						# 4xx Client Error
						400 => 'Bad Request',
						401 => 'Unauthorized', 
						402 => 'Payment Required', 
						403 => 'Forbidden', 
						404 => 'Not Found',
						405 => 'Method Not Allowed', 
						406 => 'Not Acceptable',
						407 => 'Proxy Authentication Required',
						408 => 'Request Timeout', 
						409 => 'Conflict',
						410 => 'Gone', 
						411 => 'Length Required', 
						412 => 'Precondition Failed', 
						413 => 'Request Entity Too Large', 
						414 => 'Request-URI Too Long',
						415 => 'Unsupported Media Type',
						416 => 'Request Range Not Satisfiable', 
						417 => 'Expectation Failed', 
						# 5xx Server Error
						500 => 'Internal Server Error',
						501 => 'Not Implemented', 
						502 => 'Bad Gateway', 
						503 => 'Service Unavailable',
						504 => 'Gateway Timeout', 
						505 => 'HTTP Version Not Supported'
					);	
					
	public static function getMessage($pId) {
		$msg = self::$http_status_codes[$pId];
		return $pId.' '.$msg;
	}
    
    
}
?>