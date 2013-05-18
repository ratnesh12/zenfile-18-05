<script type="text/javascript">
    $('.legalization_choice').hover(function(){
        var coords = $(this).position();
        var top = Math.round(coords.top) + 20 + 'px';
        var left = Math.round(coords.left) - 20 + 'px';
        var data = $(this).attr('value');
       // alert(date);
        if (data) {
            $('.legalization_by_view').css({'top':top, 'left': left}).html(data).fadeIn(300);
        }

    }, function(){
        $('.legalization_by_view').fadeOut(300);
    });
</script>
<div class="legalization_by_view"></div>
<table class="file_view_more" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>Deadline<br/> for Filing</th>
			<th>Hardcopy<br/> Required</th>
			<th>Notarization<br/> Required</th>
			<th>Legalization<br/> Certification</th>
			<th>Final deadline</th>
			<th>Late Filing Fee</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><input type="text" value="<?php if(isset($file_data['file_filing_deadline'])) echo date($this->config->item('client_date_format') , strtotime($file_data['file_filing_deadline'])); ?>" name="" readonly="readonly"></td>
            <?php if(isset($file_data['hardcopy'])){
            if($file_data['hardcopy'] == '1'){
                $class = 'tracker_required';
            }else{
                $class = 'tracker_not_required';
            }
        }else{
            $class = 'tracker_inactive';
        }
            ?>
            <td><button class="<?php echo $class;?>"></button></td>
            <?php if(isset($file_data['notarization'])){
            if($file_data['notarization'] == '1'){
                $class = 'tracker_required';
            }else{
                $class = 'tracker_not_required';
            }
        }else{
            $class = 'tracker_inactive';
        }?>
            <td><button class="<?php echo $class;?>"></button></td>
            <?php if(isset($file_data['legalization'])){
            if($file_data['legalization'] == '1'){
                $class = 'tracker_required';
            }else{
                $class = 'tracker_not_required';
            }
        }else{
            $class = 'tracker_inactive';
        }?>
            <td><button class="legalization_choice <?php echo $class;?>" value="<?php if(isset($file_data['legalization_by'])) echo $file_data['legalization_by'];?>" name="legalization_choice"> </button></td>
			<td><input type="text" value="<?php if(isset($file_data['final_deadline'])) echo date($this->config->item('client_date_format') , strtotime($file_data['final_deadline'])); ?>" name="" readonly="readonly"></td>
			<td><?php if(isset($file_data['fee_currency']) && $file_data['fee_currency']=='usd') echo '$'; if(isset($file_data['fee_currency']) && $file_data['fee_currency'] =='euro') echo '€'; if(isset($file_data['filing_fee'])) echo $file_data['filing_fee']; ?></td>
		</tr>
	</tbody>

</table>
