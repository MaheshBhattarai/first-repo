<?php
  $app->post('/userRegistration/',function($request,$response,$args){
    try{
      $catchJsonData=$request->getBody();
      $decodeJsonData=json_decode($catchJsonData);
      $firstName=$decodeJsonData->firstName;
      $lastName=$decodeJsonData->lastName;
      $designation=$decodeJsonData->designation;
      $role=$decodeJsonData->role;
      $password=md5($decodeJsonData->password);
      $email=$decodeJsonData->email;
      $emailvalidation = $this->db->prepare("SELECT * FROM user WHERE email='$email'");
      $emailvalidation->execute();
      $message=$emailvalidation->fetchAll(PDO::FETCH_OBJ);
      $rowCount=$emailvalidation->rowCount();
      if($rowCount>0){
          $result=array('success'=>false,'message'=>'Email '.$email.' already exist!!');
      
      }else{
          $dataInsert = $this->db->prepare("INSERT INTO user (first_name,last_name,designation,role,password,email) VALUES ('$firstName','$lastName','$designation','$role','$password','$email')");
          $dataInsert->execute();
          $lastInsertId=$this->db->lastInsertId();
          if($lastInsertId>0){
              $result=array('success'=>true,'message'=>'successfully register!!','userid'=>$lastInsertId,'name'=>$firstName.' '.$lastName,'email'=>$email);
          }else{
              $result = array('success'=>false,'message'=>'unable to register!!');
          }
      }    
      return $response->withJson($result);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $result=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($result);
    }
  });
  $app->get('/allUser/',function($request,$response,$args){
    try{
        $allUser=$this->db->prepare("SELECT * FROM user");
        $allUser->execute();
        $data=$allUser->fetchAll();
        return $response->withJson($data);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'error'=>'internal server error!!');
        return $response->withJson($message);
    }
  });
  $app->post('/updateUser/[{userid}]',function($request,$response,$args){
    try{
        $singleUser=$this->db->prepare("SELECT * FROM user WHERE user_id= :user_id");
        $singleUser->execute(array(':user_id' => $args['userid']));
        $data=$singleUser->fetchAll(PDO::FETCH_OBJ);
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $firstName=$decodeJsonData->firstName;
        $lastName=$decodeJsonData->lastName;
        $designation=$decodeJsonData->designation;
        $role=$decodeJsonData->role;
        $password=md5($decodeJsonData->password);
        $email=$decodeJsonData->email;
        $updateUser=$this->db->prepare("UPDATE user SET first_name='$firstName',last_name='$lastName',designation='$designation',role='$role',password='$password',email='$email' WHERE user_id= :user_id");
        $updateUser->execute(array(':user_id'=>$args['userid']));
        $message=array('success'=>true,'message'=>'successfully updated!!');
        return $response->withJson($message);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
    }
  });
  $app->get('/deleteUser/[{userid}]',function($request,$response,$args){
        try{
            $deleteUser=$this->db->prepare("DELETE FROM user WHERE user_id=:user_id");
            $deleteUser->execute(array(':user_id'=>$args['userid']));
            $message=array('success'=>true,'message'=>'successfully deleted!!');
            return $response->withJson($message);
        }catch(Exception $e){
            $errorMessage=$e->getMessage();
            $message=array('success'=>false,'message'=>'internal server error!!');
            return $response->withJson($message);
        }
  });
  $app->get('/getSelectedUser/[{userid}]',function($request,$response,$args){
       try{
           $selectedUser=$this->db->prepare("SELECT  * FROM user WHERE user_id=:user_id");
           $selectedUser->execute(array(':user_id'=>$args['userid']));
           $data=$selectedUser->fetchAll(PDO::FETCH_OBJ);
           return $response->withJson($data);
       }catch(Exception $e){
           $errorMessage=$e->getMessage();
           $message=array('success'=>false,'message'=>'internal server error!!');
           return $response->withJson($message);
       }
  });
/*
  End api for user crud operation
*/
/*
  Start api for product crud operation
*/
$app->post('/addProduct/',function($request,$response,$args){
     try{
         $catchJsonData=$request->getBody();
         $decodeJsonData=json_decode($catchJsonData);
         $productName=$decodeJsonData->productname;
         $productDescription=$decodeJsonData->productdescription;
         $productValidation=$this->db->prepare("SELECT * FROM product WHERE name='$productName' AND description='$productDescription'");
         $productValidation->execute();
         $rowCount=$productValidation->rowCount();
         if($rowCount>0){
             $message=array('success'=>false,'message'=>'product '.$productName. ' already exist!!');
         }else{
            $insertProduct = $this->db->prepare("INSERT INTO product (name,description) VALUES ('$productName','$productDescription')");
            $insertProduct->execute();
            $lastInsertId=$this->db->lastInsertId();
            if($lastInsertId>0){
                $message=array('success'=>true,'message'=>'successfully saved!!','productid'=>$lastInsertId,'productname'=>$productName);
            }
         }
         return $response->withJson($message);
     }catch(Exception $e){
         $errorMessage=$e->getMessage();
         $message=array('success'=>false,'messsage'=>'internal server error !!');
         return $response->withJson($message);
     }
});
$app->get('/allProducts/',function($request,$response,$args){
    try{
        $getProducts=$this->db->prepare("SELECT * FROM product");
        $getProducts->execute();
        $data=$getProducts->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }
});
$app->post('/updateProducts/[{productid}]',function($request,$response,$args){
    try{
        $selectParticularId=$this->db->prepare("SELECT * FROM product WHERE product_id=:productid");
        $selectParticularId->execute(array(':productid'=>$args['productid']));
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $productName=$decodeJsonData->productname;
        $productDescription=$decodeJsonData->productdescription;
        $updateProduct=$this->db->prepare("UPDATE product SET name='$productName', description='$productDescription' WHERE product_id=:productId");
        $updateProduct->execute(array(':productId'=>$args['productid']));
        $message=array('success'=>true,'message'=>"successfully updated");
        return $response->withJson($message);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }

});
$app->get('/deleteProduct/[{productid}]',function($request,$response,$args){
    try{
        $deleteProducts=$this->db->prepare("DELETE FROM product WHERE product_id=:productId");
        $deleteProducts->execute(array(':productId'=>$args['productid']));
        $message=array('success'=>true,'message'=>'successfully deleted!!');
        return $response->withJson($message);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }
});
$app->get('/selectedProduct/[{productid}]',function($request,$response,$args){
    try{
        $selectParticularProduct=$this->db->prepare("SELECT * FROM product WHERE product_id=:productId");
        $selectParticularProduct->execute(array(':productId'=>$args['productid']));
        $data=$selectParticularProduct->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message =array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }
});
/*
 end  api for products
*/
/*
 start api for supplier
*/
$app->post('/addSupplier/',function($request,$response,$args){
    try{
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $supplierName=$decodeJsonData->suppliername;
        $email=$decodeJsonData->email;
        $contact=$decodeJsonData->contact;
        $address=$decodeJsonData->address;
        $supplierValidation=$this->db->prepare("SELECT * FROM supplier WHERE supplier_name='$supplierName' AND email='$email' AND contact='$contact' AND address='$address'");
        $supplierValidation->execute();
        $rowCount=$supplierValidation->rowCount();
        if($rowCount>0){
            $message=array('success'=>false,'message'=>'supplier '.$supplierName.' already exist');
        }else{
            $insertSupplier=$this->db->prepare("INSERT INTO supplier (supplier_name,email,contact,address) VALUES('$supplierName','$email','$contact','$address')");
            $insertSupplier->execute();
            $lastInsertId=$this->db->lastInsertId();
            if($lastInsertId>0){
                $message=array('success'=>true,'message'=>'successfully saved!!','supplier id'=>$lastInsertId,'supplier name'=>$supplierName);
            }else{
                $message=array('success'=>false,'message'=>'unable to register!!');
            }
        }
        return $response->withJson($message);
        }catch(Exception $e){
            $errorMessage= $e->getMessage();
            $message=array('success'=>false, 'message'=>'internal server error!!');
            return $response->withJson($message);
        }
});
$app->get('/allSupplier/',function($request,$response,$args){
    try{
        $getAllSupllier = $this->db->prepare("SELECT * FROM supplier");
        $getAllSupllier->exexute();
        $data=$getAllSupllier->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }
});
$app->post('/updateSupllier/[{supllierid}]',function($request,$response,$args){
    try{
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $supplierName=$decodeJsonData->suppliername;
        $email=$decodeJsonData->email;
        $contact=$decodeJsonData->contact;
        $address=$decodeJsonData->address; 
        $updateSupplier=$this->db->prepare("UPDATE supplier SET supplier_name='$supplierName',email='$email',contact='$contact',address='$address' WHERE supplier_id=:supplierId");
        $updateSupplier->execute(array(':supplierId'=>$args['supllierid']));
        $message= array('success'=>true,'message'=>'successfully Updated!!');
        return $response->withJson($message);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }
});
$app->get('/selectedSupllier/[{supplierid}]',function($request,$response,$args){
    try{
        $selectedSupplier=$this->db->prepare("SELECT * FROM supplier WHERE supplier_id=:supplierId");
        $selectedSupplier->execute(array(':supplierId'=>$args['supplierid']));
        $data=$selectedSupplier->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }

});
$app->get('/deleteSupplier/[{supplierid}]',function($request,$response,$args){
     try{
        $deleteProducts=$this->db->prepare("DELETE FROM supplier WHERE supplier_id=:supplierId");
        $deleteProducts->execute(array(':supplierId'=>$args['supplierid']));
        $message=array('success'=>true,'message'=>'successfully deleted!!');
        return $response->withJson($message);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }
});
/*
  End api of supplier
*/
