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
 * Service Enumerator, lists the resources classes
 * @package REST
 * @author Luc Dehand - Alain Vagner
 */
class ServiceEnumerator
{
	private static $listResources = null;

 	/**
 	 * get the list of resources implemented in the folder rest/resources
 	 * @return	array				list of resources
 	 */	
	public static function listDefinedResources() {
		
		$prefix = null;
		if (defined('RESOURCES_NS')) {		
			if ((strlen(RESOURCES_NS)>= 2) && (substr(RESOURCES_NS, -2, 2) != '::')) {
				$pos = strpos(RESOURCES_NS, '::');
				if ($pos !== false) {
					$prefix = substr(RESOURCES_NS, $pos+2, strlen(RESOURCES_NS)-1);
				} else {
					$prefix = RESOURCES_NS;
				}
			}
		}
		
		$resources = array();
		if (self::$listResources === null) {
			// verify if RESOURCES_PATH is defined
			if (!defined('RESOURCES_PATH')) {
				throw new Exception('RESOURCES_PATH is not defined');
			}
			
			$dir = new DirectoryIterator(RESOURCES_PATH);
			foreach ($dir as $file) {
				$filename = $file->getFileName();
				$matches = array();
				$regex = '';
				if ($prefix !== null) {
					$regex = '/'.$prefix.'(.*)\.class\.php$/';
				} else {
					$regex = '/(.*)\.class\.php$/';
				}
				$res = preg_match($regex, $filename, $matches);
				if (isset($matches[1])) {
					$resources[] = $matches[1];
				}
			}
			self::$listResources = $resources;
		} else {
			$resources = self::$listResources;
		}
		return $resources;
	}
}
?>