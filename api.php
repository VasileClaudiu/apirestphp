<?php
/**
 * DB class
 */
class DB
{
	private $conn;
	
	public function __construct($servername, $username, $password, $dbname)
	{
		// Create connection
		$this->conn = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($this->conn->connect_error) {
		    return die("Connection failed: " . $this->conn->connect_error);
		} else {
			//return print "Connected!!";
		}
		//return print "Connected!!";
	}

	public function selrowquery($query){
		$result = $this->conn->query($query);
		return $result->fetch_assoc();
	}

	public function selrowsquery($query){
		$result = $this->conn->query($query);
		if ($result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
		    	$rows[] = $row;
		    };
		    return $rows;
		} else {
		   return print "0 results";
		}
	}

	public function singleinsert($query){
		if ($this->conn->query($query) === TRUE) {
		 	return print "New record created successfully";
		} else {
		    return print "Error: " . $query . "<br>" . $this->conn->error;
		}
	}

	public function insert($query){
		if ($this->conn->multi_query($query) === TRUE) {
		 	return print "New records created successfully";
		} else {
		    return print "Error: " . $query . "<br>" . $this->conn->error;
		}
	}

	public function update($query){
		if ($this->conn->query($query) === TRUE) {
		 	return print "New record updated successfully";
		} else {
		    return print "Error: " . $query . "<br>" . $this->conn->error;
		}
	}

	public function delete($query){
		if ($this->conn->query($query) === TRUE) {
		 	return print "Deleted successfully";
		} else {
		    return print "Error: " . $query . "<br>" . $this->conn->error;
		}
	}

	public function __destruct(){
		$this->conn->close();
		return print "";//return print "Database Closed successfully";
	}
}

class Product{
 
    // database connection and table name
    private $conn;
    private $table_name = "products";
 
    // object properties
    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $created;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read products
    public function read(){
 
    // select all query
    $query = "SELECT
                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
            FROM
                " . $this->table_name . " p
                LEFT JOIN
                    categories c
                        ON p.category_id = c.id
            ORDER BY
                p.created DESC";
 
    // prepare query statement
    $stmt = $this->conn->selrowsquery($query);
 
    // execute query
    //$stmt->execute();
 
    return $stmt;
	}

	// create product
	public function create(){
	 
	    // query to insert record
	    $query = "INSERT INTO
	                " . $this->table_name . "
	            SET
	                name='".$this->name."', price=".$this->price.", description='".$this->description."', category_id=".$this->category_id.", created='".$this->created."'";
	 
	    // sanitize
	    $this->name=htmlspecialchars(strip_tags($this->name));
	    $this->price=htmlspecialchars(strip_tags($this->price));
	    $this->description=htmlspecialchars(strip_tags($this->description));
	    $this->category_id=htmlspecialchars(strip_tags($this->category_id));
	    $this->created=htmlspecialchars(strip_tags($this->created));

	    // prepare query
	    $stmt = $this->conn->singleinsert($query);
	 
	    return true;
	}

	//read one product
	public function readOne(){
 
	    // query to read single record
	    $query = "SELECT
	                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
	            FROM
	                " . $this->table_name . " p
	                LEFT JOIN
	                    categories c
	                        ON p.category_id = c.id
	            WHERE
	                p.id =".$this->id."
	            LIMIT
	                0,1";
	 
	    // prepare query statement
	    $stmt = $this->conn->selrowquery( $query );
	 
	    // get retrieved row
	    $row = $stmt;
	 
	    // set values to object properties
	    $this->name = $row['name'];
	    $this->price = $row['price'];
	    $this->description = $row['description'];
	    $this->category_id = $row['category_id'];
	    $this->category_name = $row['category_name'];
	}

	// update the product
	public function update(){
	 
	    // update query
	    $query = "UPDATE
	                " . $this->table_name . "
	            SET
	                name = '".$this->name."',
	                price = ".$this->price.",
	                description = '".$this->description."',
	                category_id = '".$this->category_id."'
	            WHERE
	                id = ".$this->id;
	 
	    // prepare query statement
	    $stmt = $this->conn->update($query);
	 
	    // sanitize
	    $this->name=htmlspecialchars(strip_tags($this->name));
	    $this->price=htmlspecialchars(strip_tags($this->price));
	    $this->description=htmlspecialchars(strip_tags($this->description));
	    $this->category_id=htmlspecialchars(strip_tags($this->category_id));
	    $this->id=htmlspecialchars(strip_tags($this->id));
	 
	    return true;
	}

	// delete the product
	public function delete(){
	 
	    // delete query
	    $query = "DELETE FROM " . $this->table_name . " WHERE id = ".$this->id;
	 
	    // prepare query
	    $stmt = $this->conn->delete($query);
	 
	    // sanitize
	    $this->id=htmlspecialchars(strip_tags($this->id));
	 
	    return true;
	     
	}

}

// instantiate database
$db= new DB('localhost','root','1234','phpapi');

// initialize object
$product = new Product($db);

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];

// create SQL based on HTTP method
switch ($method) {
  	case 'GET':
  		if(!isset($_GET['id'])){
		// required headers
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		// query products
		$stmt = $product->read();
		$num = count($stmt);
		 
		// check if more than 0 record found
		if($num>0){
		 
		    // set response code - 200 OK
		    http_response_code(200);
		 
		    // show products data in json format
		    echo json_encode($stmt);
		} else {
		 
		    // set response code - 404 Not found
		    http_response_code(404);
		 
		    // tell the user no products found
		    echo json_encode(
		        array("message" => "No products found.")
		    );
		}
		} else {
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Headers: access");
			header("Access-Control-Allow-Methods: GET");
			header("Access-Control-Allow-Credentials: true");
			header('Content-Type: application/json');

			// set ID property of record to read
			$product->id = isset($_GET['id']) ? $_GET['id'] : die();
			 
			// read the details of product to be edited
			$product->readOne();
			 
			if($product->name!=null){
			    // create array
			    $product_arr = array(
			        "id" =>  $product->id,
			        "name" => $product->name,
			        "description" => $product->description,
			        "price" => $product->price,
			        "category_id" => $product->category_id,
			        "category_name" => $product->category_name
			 
			    );
			 
			    // set response code - 200 OK
			    http_response_code(200);
			 
			    // make it json format
			    echo json_encode($product_arr);
			}
			 
			else{
			    // set response code - 404 Not found
			    http_response_code(404);
			 
			    // tell the user product does not exist
			    echo json_encode(array("message" => "Product does not exist."));
			}

		}
	break;
	case 'PUT':
    //$sql = "update `$table` set $set where id=$key"; break;
    break;
  	case 'POST':
	    // required headers
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		header("Access-Control-Allow-Methods: POST");
		header("Access-Control-Max-Age: 3600");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
		 // get posted data
		$data = json_decode(file_get_contents("php://input"));
		
		if(!isset($data->id)){ 
			// make sure data is not empty
			if(
			    !empty($data->name) &&
			    !empty($data->price) &&
			    !empty($data->description) &&
			    !empty($data->category_id)
			){
			 
			    // set product property values
			    $product->name = $data->name;
			    $product->price = $data->price;
			    $product->description = $data->description;
			    $product->category_id = $data->category_id;
			    $product->created = date('Y-m-d H:i:s');
			 
			    // create the product
			    if($product->create()){
			 
			        // set response code - 201 created
			        http_response_code(201);
			 
			        // tell the user
			        echo json_encode(array("message" => "Product was created."));
			    }
			 
			    // if unable to create the product, tell the user
			    else{
			 
			        // set response code - 503 service unavailable
			        http_response_code(503);
			 
			        // tell the user
			        echo json_encode(array("message" => "Unable to create product."));
			    }
			}
			 
			// tell the user data is incomplete
			else{
			 
			    // set response code - 400 bad request
			    http_response_code(400);
			 
			    // tell the user
			    echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
			}
		} else {
			// set ID property of product to be edited
			$product->id = $data->id;
			 
			// set product property values
			$product->name = $data->name;
			$product->price = $data->price;
			$product->description = $data->description;
			$product->category_id = $data->category_id;
			
			//var_dump($product->id);
			//die();			
			// update the product
			if($product->update()){
			 
			    // set response code - 200 ok
			    http_response_code(200);
			 
			    // tell the user
			    echo json_encode(array("message" => "Product was updated."));
			}
			 
			// if unable to update the product, tell the user
			else{
			 
			    // set response code - 503 service unavailable
			    http_response_code(503);
			 
			    // tell the user
			    echo json_encode(array("message" => "Unable to update product."));
			}

		}
	break;
  	case 'DELETE':
    //$sql = "delete `$table` where id=$key"; break;
    // required headers
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Methods: DELETE");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
	// get product id
	$data = json_decode(file_get_contents("php://input"));
	 
	// set product id to be deleted
	$product->id = $data->id;
	//var_dump($data->id);
	//die();
	 
	// delete the product
	if($product->delete()){
	 
	    // set response code - 200 ok
	    http_response_code(200);
	 
	    // tell the user
		echo json_encode(array("message" => "Product was deleted."));
	}
	 
	// if unable to delete the product
	else{
	 
	    // set response code - 503 service unavailable
	    http_response_code(503);
	 
	    // tell the user
	    echo json_encode(array("message" => "Unable to delete product."));
	}

    break;
}
?>