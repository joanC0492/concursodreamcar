<?php
// header('Content-type: application/json');
// $data = json_decode(file_get_contents("php://input"), TRUE);
// if (!empty($_FILES)) {
//   $storeFolder = '/images/concurso/';   //2
//   $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//   $nn = rand(1, 10).$characters[rand(0, 25)];
//   $path = $_FILES['file']['name'];
//   $ext = pathinfo($path, PATHINFO_EXTENSION);
//   $newname = 'DC'.$nn.strtotime(date('Y-m-d H:i:s')).'.'.$ext;
//   $idfile = 'DC'.$nn.strtotime(date('Y-m-d H:i:s'));
  
//   $tempFile = $_FILES['file']['tmp_name'];          //3             
    
//   $targetPath = dirname( __FILE__ ) .$storeFolder;  //4
  
//   // $targetFile =  $targetPath. $_FILES['file']['name'];  //5
//   $targetFile =  $targetPath.$newname;  //5

//   move_uploaded_file($tempFile,$targetFile); //6
//   echo json_encode(array("target_file" => $newname,"id_file"=>$idfile));
 
// }

?>