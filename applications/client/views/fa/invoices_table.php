<table>
<?php foreach($fa_invoices as $invoice) { ?>
    <tr>
        <td>
            <a href="<?php echo base_url() ?>fa/download_invoice/<?php echo $invoice->invoice_id ?>">Download Invoice (<?php echo $invoice->filename ?>)</a>
        </td>
        <td>
            <a class="delete_invoice" href="<?php echo $invoice->invoice_id ?>"><?php if(empty($disabled)) { ?><img height="20px" src="/client/assets/img/delete_main_image.png"><?php } ?></a>
        </td>
    </tr>
    <?php } ?>
</table>
