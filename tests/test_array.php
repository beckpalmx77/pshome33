<?php

include('../config/connect_db.php');

$account_type = "admin";

$sql = "SELECT sub_menu, main_menu, permission_detail FROM ims_permission where  permission_id = '" . $account_type
    . "' order by main_menu,sub_menu ";
$query = $conn->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0) {
    foreach ($results as $result) {
        //echo "Main : " . $result->main_menu . " - Sub : " . $result->sub_menu . " " . $result->permission_detail . "<br>";
        $main_menus = (explode(",", $result->main_menu));
        $sub_menus = (explode(",", $result->sub_menu));

        foreach ($main_menus as $main_menu) {

            $sql_main_menu = "SELECT * FROM menu_main where main_menu_id = '" . $main_menu . "' order by main_menu_id ";
            $query_main_menu = $conn->prepare($sql_main_menu);
            $query_main_menu->execute();
            $result_mains = $query_main_menu->fetchAll(PDO::FETCH_OBJ);

            foreach ($result_mains as $result_main) {

                //echo $main_menu . " : " . $result_main->label . "<br>" ;
                ?>
                <input type="checkbox" id="<?php echo $main_menu ?>" name="menu_main"
                       value="<?php echo $main_menu ?>"><?php echo $main_menu . " " . $result_main->label ?><br/>
                <?php
                foreach ($sub_menus as $sub_menu) {

                    $sql_sub_menu = "SELECT * FROM menu_sub where main_menu_id = '" . $main_menu . "' and  sub_menu_id = '" . $sub_menu . "'"
                        . " order by main_menu_id,sub_menu_id  ";
                    $query_sub_menu = $conn->prepare($sql_sub_menu);
                    $query_sub_menu->execute();
                    $result_subs = $query_sub_menu->fetchAll(PDO::FETCH_OBJ);

                    foreach ($result_subs as $result_sub) {
                        //echo " : " . $sub_menu . " : " . $result_sub->label . "<br>" ;
                        ?>

                        &nbsp;&nbsp; <input type="checkbox" id="<?php echo $sub_menu ?>" name="menu_sub"
                                            value="<?php echo $sub_menu ?>"><?php echo $sub_menu . " " . $result_sub->label ?>
                        <br/>
                        <?php

                    }
                }
            }
        }
    }
}


$conn = null;

?>

<!DOCTYPE html>
<html>
<body>
<!--label>Check Which you have</label><br/>
<input type="checkbox" id="checkbox1" name="bike" value="Bike"> Bike <br/>
<input type="checkbox" id="checkbox2" name="car" value="Car"> Car   <br/>
<input type="checkbox" id="checkbox3" name="home" value="Home"> Home <br/-->
<button onclick="getValue()">Get Value</button>
<p id="p1"></p>
<button onclick="ClearValue()">Clear Value</button>
<script>
    function getValue() {

        let main_list = document.getElementsByName("menu_main");
        for (let i = 0; i < main_list.length; i++) {
            if (document.getElementsByName("menu_main")[i].checked === true) {
                alert("Array = " + i + " " + main_list[i].value);
            }
        }

        let sub_list = document.getElementsByName("menu_sub");
        for (let j = 0; j < sub_list.length; j++) {
            if (document.getElementsByName("menu_sub")[j].checked === true) {
                alert("Array = " + j + " " + sub_list[j].value);
            }
        }

        /*
                let ele=[]
                let bike = document.getElementById("checkbox1")
                if(bike.checked){
                    ele.push(bike.value);
                }
                let car = document.getElementById("checkbox2")
                if(car.checked){
                    ele.push(car.value);
                }
                let home = document.getElementById("checkbox3")
                if(home.checked){
                    ele.push(home.value);
                }
                if(ele.length>0){
                    document.getElementById("p1").innerHTML = ele;
                }
                else{
                    document.getElementById("p1").innerHTML = "You Dont have any thing";
                }


         */

    }

</script>

<script>
    function ClearValue() {


        let main_list = document.getElementsByName("menu_main");
        for (let i = 0; i < main_list.length; i++) {
            document.getElementsByName("menu_main")[i].checked = false;
        }

        let sub_list = document.getElementsByName("menu_sub");
        for (let j = 0; j < sub_list.length; j++) {
            document.getElementsByName("menu_sub")[j].checked = false;
        }

    }


</script>
</body>
</html>
