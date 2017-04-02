<?php
  $app->post('/userRegistration/',function($request,$response,$args){
    try{
      $catchJsonData=$request->getBody();
      $decodeJsonData=json_decode($catchJsonData);
    //   $designation='';
    //   if(isset($decodeJsonData->designation)){
    //     $designation=$decodeJsonData->designation;
    //   }
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
        // $result=$designation;
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
  $app->post('/login/',function($request,$response,$args){
        try{
            $catchJsonData=$request->getBody();
            $decodeJsonData=json_decode($catchJsonData);
            $name=$decodeJsonData->username;
            $password=md5($decodeJsonData->password);
            $userValidate=$this->db->prepare("SELECT * FROM user WHERE email='$name' AND password='$password'");
            $userValidate->execute();
            $data=$userValidate->rowCount();
            if($data>0){
                $userDetails=$userValidate->fetchAll(PDO::FETCH_OBJ);
                $time=time("H:i:s");
                $sessionTime=$time+180;
                $sessionid=1;
                foreach($userDetails as $details){
                    $userid=$details->user_id;
                }
                $fetchactiveUser=$this->db->prepare("SELECT * FROM active_user WHERE userid='$userid'");
                $fetchactiveUser->execute();
                $rowCount=$fetchactiveUser->rowCount();
                if($rowCount<=0){
                    $insertActiveuser=$this->db->prepare("INSERT into active_user(userid,sessid,expiretime) VALUES($userid,$sessionid,$sessionTime)");
                    $insertActiveuser->execute(); 
                }
                
                $sessionStatus="true";      
                $message=array('success'=>true,'message'=>$sessionTime,'time'=>$time,'row'=>$rowCount,'userid'=>$userid,'session_status'=>$sessionStatus);
            }else{
                $message=array('success'=>false,'message'=>'username and password is incorrect!!');
            }
            return $response->withJson($message);
        }catch(Exception $e){
            $errorMessage=$e->getMessage();
            $message=array('success'=>false,'error'=>'internal server error!!');
            return $response->withJson($message);
        }
  });
  $app->post('/checkSession/',function($request,$response,$args){
      try{
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $userid=$decodeJsonData->userid;
        $currentTime=time("H:i:s");
        $fetchExpireTime=$this->db->prepare("SELECT * FROM active_user WHERE userid='$userid'");
        $fetchExpireTime->execute();
        $data=$fetchExpireTime->fetchAll(PDO::FETCH_OBJ);
        foreach($data as $row){
          $expireTime=$row->expiretime;  
        }
        
        if($currentTime>$expireTime){
            $deleteActiveUser=$this->db->prepare("DELETE FROM active_user WHERE userid='$userid'");
            $deleteActiveUser->execute();
            $message=array('success'=>false,'message'=>'session has been expired!!','session_status'=>'false');
        }else{
            $sessionStatus='true';
            $increasExpireDate=$currentTime+180;
            $updateExpireTime=$this->db->prepare("UPDATE active_user SET expiretime=$increasExpireDate");
            $updateExpireTime->execute();
            $message=array('success'=>true,'message'=>'session updated!!','session_status'=>'true');

        }
        return $response->withJson($message);
      }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
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
        // $selectParticularId=$this->db->prepare("SELECT * FROM product WHERE product_id=:productid");
        // $selectParticularId->execute(array(':productid'=>$args['productid']));
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
        $getAllSupllier->execute();
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
/*
 start api for purchse
*/
$app->post('/addPurchase/',function($request,$response,$args){
    try{
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
        $supplier_id=$decodeJsonData->supplier_id;
        $product_id=$decodeJsonData->product_id;
        $user_id=$decodeJsonData->user_id;
        $amount = $decodeJsonData->amount;
        $quantity = $decodeJsonData->quantity;
        $rate = $decodeJsonData->rate;
        $tax = $decodeJsonData->tax;
        $insertPurchase=$this->db->prepare("INSERT INTO purchase (supplier_id,product_id,user_id,timestamp,amount,quantity,rate,tax) VALUES($supplier_id,$product_id,$user_id,now(),$amount,$quantity,$rate,$tax)");
        $insertPurchase->execute();
        $lastInsertId=$this->db->lastInsertId();
        if($lastInsertId>0){
            $message=array('success'=>true,'message'=>'successfully purchased!!','purchase id'=>$lastInsertId);
         }else{
            $message=array('success'=>false,'message'=>'unable to purchase!!');
        }
        return $response->withJson($message);
        }catch(Exception $e){
            $errorMessage= $e->getMessage();
            $message=array('success'=>false, 'message'=>'internal server error!!');
            return $response->withJson($message);
        }
});
$app->get('/allPurchase/',function($request,$response,$args){
    try{
        $getAllPurchase = $this->db->prepare("SELECT * FROM purchase");
        $getAllPurchase->execute();
        $data=$getAllPurchase->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }
});
$app->post('/updatePurchase/[{purchaseid}]',function($request,$response,$args){
    try{
        $catchJsonData=$request->getBody();
        $decodeJsonData=json_decode($catchJsonData);
         $supplier_id=$decodeJsonData->supplier_id;
        $product_id=$decodeJsonData->product_id;
        $user_id=$decodeJsonData->user_id;
        $amount = $decodeJsonData->amount;
        $quantity = $decodeJsonData->quantity;
        $rate = $decodeJsonData->rate;
        $tax = $decodeJsonData->tax;
        $updateSupplier=$this->db->prepare("UPDATE purchase SET supplier_id=$supplier_id,product_id=$product_id,user_id=$user_id,timestamp=now(),amount=$amount,quantity=$quantity,rate=$rate,tax=$tax WHERE purchase_id=:purchaseid");
        $updateSupplier->execute(array(':purchaseid'=>$args['purchaseid']));
        $message= array('success'=>true,'message'=>'successfully Updated!!');
        return $response->withJson($message);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }
});
$app->get('/selectedPurchase/[{purchaseid}]',function($request,$response,$args){
    try{
        $selectedSupplier=$this->db->prepare("SELECT * FROM purchase WHERE purchase_id=:purchaseid");
        $selectedSupplier->execute(array(':purchaseid'=>$args['purchaseid']));
        $data=$selectedSupplier->fetchAll(PDO::FETCH_OBJ);
        return $response->withJson($data);
    }catch(Exception $e){
         $errorMessage= $e->getMessage();
         $message=array('success'=>false, 'message'=>'internal server error!!');
         return $response->withJson($message);
    }

});
$app->get('/deletePurchase/[{purchaseid}]',function($request,$response,$args){
     try{
        $deleteProducts=$this->db->prepare("DELETE FROM purchase WHERE purchase_id=:purchaseid");
        $deleteProducts->execute(array(':purchaseid'=>$args['purchaseid']));
        $message=array('success'=>true,'message'=>'successfully deleted!!');
        return $response->withJson($message);
    }catch(Exception $e){
        $errorMessage=$e->getMessage();
        $message=array('success'=>false,'message'=>'internal server error!!');
        return $response->withJson($message);
    }
});
/*
End api for purchase

*/