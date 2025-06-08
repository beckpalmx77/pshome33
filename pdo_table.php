<?php

    include('config/connect_lotto_db.php');

        $sql = "SELECT * FROM ims_lotto ORDER BY lotto_number";
        $query = $conn->prepare($sql);
        $query->execute();
        $loop = 0;
        while($fetch = $query->fetch()){
            $loop++;
            ?>

            <tr>
                <?php $lt_num = sprintf("%03d", $fetch['lotto_number']); ?>
                <td><?php echo $loop?></td>
                <td><?php echo $fetch['lotto_name']?></td>
                <td><?php echo $fetch['lotto_phone']?></td>
                <td><?php echo $fetch['lotto_province']?></td>
                <td><?php echo $lt_num?></td>
            </tr>

            <?php
        }

?>