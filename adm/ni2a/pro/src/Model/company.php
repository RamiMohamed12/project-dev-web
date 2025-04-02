<?php

require_once __DIR__ . '/../../config/config.php'; 

// this is a Model class for the company table inside the database
class Company {

    // this is a property that will hold the connection to the database
    private $conn; 
    private $error = ''; 
    public function __construct($conn){
    
       $this->conn = $conn; 
       $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }
    public function sanitize($data){
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); 
    }
    // this is a public method to create a company inside the table 
    public  function create($name, $location, $description,$email,$phone)
    
    {
        try {
        $name = $this->sanitize($name); 
        $location = $this->sanitize($location);
        $description = $this->sanitize($description);
        $email = $this->sanitize($email);
        $phone = $this->sanitize($phone);

        $sql = "INSERT INTO company (name_company, location, description, email, phone_number) VALUES (:name_company, :location, :description, :email, :phone_number)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name_company', $name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone);
        $stmt-> execute(); 
        return $stmt->rowCount(); 
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage(); // set the error message
            return false; // return false if there is an error
        }
    }

    // this is a public method to read a company from the table
    public function read($id_company){
        
        try {
        $sql = "SELECT * FROM company WHERE id_company = :id_company";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); 
        
        } catch(Exception $e) {
            $this->error = 'Error: ' . $e->getMessage(); // set the error message
            return false; // return false if there is an error
        }
    
    }
    
    public function readAll() {

        try { 
            $sql = "SELECT * FROM company"; 
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // fetch all rows as an associative array
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage(); // set the error message
            return false; // return false if there is an error
        }
        
    }

    // this is a public method to read all companies from the table
    public function update($id_company, $name, $location, $description,$email,$phone){
    
        try {    
        $id_company = (int)$id_company; // cast to int to prevent SQL injection
        $name = $this->sanitize($name);
        $location = $this->sanitize($location);
        $description = $this->sanitize($description);
        $email = $this->sanitize($email);
        $phone = $this->sanitize($phone);

        $sql = "UPDATE company SET name_company = :name_company, location = :location, description = :description, email = :email, phone_number = :phone_number WHERE id_company = :id_company";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->bindParam(':name_company', $name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone);
        $stmt->execute(); 
        return $stmt->rowCount(); 
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage(); // set the error message
            return false; // return false if there is an error
        }
    
    }

    // this is a public method to delete a company from the table
    public function delete($id_company){
        try {   
        $id_company = (int)$id_company; // cast to int to prevent SQL injection
        $sql = "DELETE FROM company WHERE id_company = :id_company";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute(); 
        return $stmt->rowCount(); // return the number of rows affected
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage(); // set the error message
            return false; // return false if there is an error
        }
    }
}


?>