<?php

include('config/connect_db.php');

$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp'); // valid extensions
$path = 'img_doc/'; // upload directory

if(!empty($_POST['id']) || $_FILES['image'])
{
    $id = $_POST['id'];
    $img = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

// get uploaded file's extension
    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

// can upload same image using rand function
    $final_image = rand(1000,1000000).$img;

/*
    $txt = $final_image . " | " . $id . " File Name = " . $img ;
    $my_file = fopen("leave_a.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

// check's valid format
    if(in_array($ext, $valid_extensions))
    {
        $path = $path.strtolower($final_image);
        $picture = strtolower($final_image);

        if(move_uploaded_file($tmp,$path))
        {
            // echo "<img src='$path' />";

            $sql_update = "UPDATE dleave_event SET picture=:picture
                               WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();

            $response = 0;
            $response = $path;
            echo $response;
            exit;

//echo $insert?'ok':'err';
        }
    }
    else
    {
        echo 'invalid';
    }
}
