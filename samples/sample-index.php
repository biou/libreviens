<?php
	require_once dirname(__FILE__) ."/../includes/common.php";
	$app_name = 'test web service';
	try {
		$re		= new RESTHttpRequest();
		$root	= new DefaultRootResource($re);
		$fc		= new RESTC($re, $root, $app_name);
		$fc->dispatch();
	} catch (RESTException $e) {
		$e->sendError();
	} catch (Exception $e) {
		$ne = new RESTException($e->getMessage(), 500);
		$ne->sendError();
	}
?>
