<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

<?php

$val = 80 ;
$data = "style='width: " . $val . "%'";

?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="progress">
                <div class="progress-bar" role="progressbar" <?php echo $data ?> aria-valuenow="<?php echo $val ?>" aria-valuemin="0"
                     aria-valuemax="100"><?php echo $val . "%" ?>
                </div>
            </div>
        </div>
    </div>
</div>