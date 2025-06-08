<div class="card-body">
    <table id="TableStockList" class="display table table-striped table-bordered"
           cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>คลัง</th>
            <th>ตำแหน่งเก็บ</th>
            <th>จำนวน</th>
        </tr>
        </thead>
        <tfoot>
        </tfoot>
        <tbody>
        <?php
        $product_id = "";
        $sql_total = " SELECT SKU_CODE,WH_CODE,WL_CODE,sum(CAST(QTY AS DECIMAL(10,2))) as  QTY FROM ims_product_stock_balance "
            . " WHERE SKU_CODE = '" . $product_id . "'"
            . " GROUP BY SKU_CODE,WH_CODE,WL_CODE "
            . " HAVING sum(CAST(QTY AS DECIMAL(10,2))) > 0 ";

        $statement_total = $conn->query($sql_total);
        $results_total = $statement_total->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results_total

        as $row_total) { ?>

        <tr>
            <td>
                <p class="number"><?php echo htmlentities($row_total['WH_CODE']); ?></p>
            </td>
            <td>
                <p class="number"><?php echo htmlentities($row_total['WL_CODE']); ?></p>
            </td>
            <td>
                <p class="number"><?php echo htmlentities(number_format($row_total['QTY'], 2)); ?></p>
            </td>
            <?php } ?>
        </tbody>
    </table>
</div>
