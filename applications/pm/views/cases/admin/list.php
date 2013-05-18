<script type="text/javascript">
    $(document).ready(function () {
        $('.create_new_case_div_info , .reassign_case_div_info, .delete_users_case_div_info').css('display', 'none');
        $('.reassign_case').live('change',function(){
            var user_id = $(this).val();
            if(user_id != ''){
                $.post(
                    "<?= base_url(); ?>admin/ajax_get_usersdata/",
                    {user_id: user_id},
                    function(result){
                        $('.reassign_case_div_info').html(result.userdata);
                        $('.reassign_case_div_info').css('display', 'block');
                    },
                    "json"
                );
            }else{
                $('.reassign_case_div_info').css('display', 'none');
            }

        });
        $('.create_new_case').live('change',function(){
            var user_id = $(this).val();
            if(user_id != ''){
                $.post(
                    "<?= base_url(); ?>admin/ajax_get_usersdata/",
                    {user_id: user_id},
                    function(result){
                        $('.create_new_case_div_info').html(result.userdata);
                        $('.create_new_case_div_info').css('display', 'block');
                    },
                    "json"
                );
            }else{
                $('.create_new_case_div_info').css('display', 'none');
            }

        });
        $('.delete_users_case').live('change',function(){
            var user_id = $(this).val();
            if(user_id != ''){
                $.post(
                    "<?= base_url(); ?>admin/ajax_get_usersdata/",
                    {user_id: user_id},
                    function(result){
                        $('.delete_users_case_div_info').html(result.userdata);
                        $('.delete_users_case_div_info').css('display', 'block');
                    },
                    "json"
                );
            }else{
                $('.delete_users_case_div_info').css('display', 'none');
            }

        });
    })
</script>

<?php
	$message = $this -> session -> flashdata('message');
	if ( ! empty($message))
	{
		echo '<p class="message">'.$message.'</p>';
	}
?>

<?php echo form_open('/admin/delete_user_cases/')?>
<p>Delete user's cases</p>
<?php echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin delete_users_case"')?>
<?php echo form_submit('submit', 'Delete Cases', "class='more_visible'")?>
<div class="delete_users_case_div_info">hello</div>
<?php echo form_close()?>

<?php echo form_open('/admin/draft_case/')?>
<p>Delete a case</p>
<?php echo form_label('Start case number: ', 'start_case_number').form_input('start_case_number','','class="admin"')?>
<?php echo form_label('End case number: ', 'end_case_number').form_input('end_case_number','','class="admin"')?>
<?php echo form_submit('submit', 'Delete', "class='more_visible'")?>
<?php echo form_close()?>

<?php echo form_open('/admin/assign_client_to_case/')?>
<p>Reassign a case client</p>
<?php echo form_label('Case number: ', 'case_number').form_input('case_number','','class="admin"')?>
<?php echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin reassign_case"')?>
<?php echo form_submit('submit', 'Reassign', "class='more_visible'")?>
<div class="reassign_case_div_info">hello</div>
<?php echo form_close()?>

<?php echo form_open('/admin/create_case/')?>
<p>Create new case</p>
<?php echo form_label('Case number: ', 'case_number').form_input('case_number','','class="admin"');
 echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin create_new_case"');
 echo form_submit('submit', 'Create Case', "class='more_visible'")?>
<div class="create_new_case_div_info">hello</div>
<?php echo form_close()?>