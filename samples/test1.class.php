<?php
/**
 * sample resource class
 */
class Test1 implements RESTResource {

 	/**
 	 * list of test data
 	 */
	private $myList;
	
 	/**
 	 * list of constraints on this resource (parsed path variables)
 	 */	
	private $constraints;
	
 	/**
 	 * extension asked
 	 */	
	private $ext;
	
 	/**
 	 * reference to the request object
 	 */	
	private	$request;

 	/**
 	 * Constructor
 	 * @param	HttpRequest		$pReq			request
 	 * @param	string			$pExt			extension
 	 * @param	array			$pConstraints	list of constraints on this resource
 	 */
    public function __construct(RESTHttpRequest $pReq, $pExt = null, $pConstraints = array()) {
    	$this->myList = array("1", "2", "3");
    	$this->constraints = $pConstraints;
    	$this->request = $pReq;
    	$this->ext = $pExt;    	
    }
    
 	/**
 	 * Tests if a string is an id for this resource class (this method is optional)
 	 * @param	string	$pInputStr	the string tested
 	 * @return	integer	0 for false, and 1 for true (not boolean because this method is called by call_user_func)
 	 */
    public static function isId($pInputStr) {
    	return (is_numeric($pInputStr)?1:0);
    }

 	/**
 	 * authentication
 	 * @param	string		$pLogin	login
 	 * @param	string		$pPass	password
 	 * @return	boolean				true if the login is successful or if it is a public resource 
 	 */    
    public function auth($pLogin, $pPass) {
    	return ($pLogin == 'test' && $pPass == 'test');
    }

	// *** read-only rest methods *** //

 	/**
 	 * list of items (this method is optional)
	 * WARNING: this method should not modify data (REST "Safety" concept)
 	 * GET /item 
 	 * or HEAD /item
 	 * @param	boolean	$pHead	true if we return only headers
 	 */
    public function index($pHead = false) {
    	// this page can have links to formNew, formEdit and delete...
    	echo '<ul>';
    	foreach ($this->myList as $i) {
    		echo '<li><a href="./'.$i.'" >'.$i.'</a></li>';
    	}
    	echo '</ul>';
    }

 	/**
 	 * display an item (this method is optional)
	 * WARNING: this method should not modify data (REST "Safety" concept)
 	 * GET /item/id
 	 * or HEAD /item/id
 	 * @param	string		@pId	id of the resource
 	 * @param	boolean 	$pHead	true if we return only headers
 	 */    
    public function show($pId, $pHead = false) {
		echo 'display of '.$pId;
    }

	// *** read-write rest methods *** //

 	/**
 	 * create form (this method is optional)
	 * WARNING: this method should not modify data (REST "Safety" concept)
 	 * GET /item;new
 	 * @param	boolean 	$pHead	true if we return only headers
 	 */
    public function formNew($pHead = false) {
    	// this form will send a POST /item to the server
    }

 	/**
 	 * create an item (this method is optional)
 	 * POST /item
 	 */    
    public function create() {
    	echo $this->request->getInput();
    	print_r($this->request->getContentType());	
    }

 	/**
 	 * edit form for an item (this method is optional)
	 * WARNING: this method should not modify data  (REST "Safety" concept)
 	 * GET /item/id;edit
 	 * @param	string		@pId	id of the resource
 	 * @param	boolean 	$pHead	true if we return only headers
 	 */
    public function formEdit($pId, $pHead = false) {
    	// this form will send a PUT /item/id to the server 
    	// (in html emulation, it will be a POST /item/id, with the  $_GET or $_POST['_method'] = 'PUT')
    }

 	/**
 	 * update an item (this method is optional)
	 * WARNING: this method should be idempotent (two calls to this method give the same result as one)
 	 * PUT /item/id
 	 * @param	string		@pId	id of the resource
 	 */        
    public function update($pId) {
    	
    }
    
 	/**
 	 * delete an item (this method is optional)
	 * WARNING: this method should be idempotent (two calls to this method give the same result as one)
 	 * DELETE /item/id
 	 * @param	string		@pId	id of the resource
 	 */
    public function delete($pId) {
    	
    }
}
?>
