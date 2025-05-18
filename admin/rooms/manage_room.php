<?php

require_once('../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `room_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
}
?>
<div class="container-fluid">
	<form action="" id="room-form">
		<input type="hidden" name ="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="dorm_id" class="control-label">Dorm</label>
			<select name="dorm_id" id="dorm_id" class="form-control form-control-sm rounded-0" required>
				<option value="" disabled <?= !isset($dorm_id) ? 'selected' : "" ?>></option>
				<?php 
				$dorm_qry = $conn->query("SELECT * FROM `dorm_list` where `status` = 1 and delete_flag = 0 ".(isset($dorm_id) && $dorm_id > 0? " or id = '{$dorm_id}'" : "")." order by `name` asc");
				while($row = $dorm_qry->fetch_assoc()):
				?>
				<option value="<?= $row['id'] ?>" <?= isset($dorm_id) && $dorm_id == $row['id'] ? "selected" : '' ?>><?= $row['name'] ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="form-group">
			<label for="name" class="control-label">Name</label>
			<input type="text" name="name" id="name" class="form-control form-control-sm rounded-0" value="<?php echo isset($name) ? $name : ''; ?>"  required/>
		</div>
		<div class="form-group">
			<label for="name" class="control-label">Category</label>
			<label for="dorm_id" class="control-label">Category</label>
			<select name="category" id="category" class="form-control form-control-sm rounded-0" required>
			<option value="" disabled <?= !isset($dorm_id) ? 'selected' : "" ?>></option>
			<?php 
				$dorm_qry = $conn->query("SELECT * FROM `dorm_list` where `status` = 1 and delete_flag = 0 ".(isset($dorm_id) && $dorm_id > 0? " or id = '{$dorm_id}'" : "")." order by `name` asc");
				while($row = $dorm_qry->fetch_assoc()):
				?>
				<option value="<?= $row['id'] ?>" <?= isset($dorm_id) && $dorm_id == $row['id'] ? "selected" : '' ?>><?= $row['category'] ?></option>
				<?php endwhile; ?>
			
			</select>
		</div>
		<!-- <div class="form-group">
			<label for="slots" class="control-label">Bed/s</label>
			<input type="number" min="1" name="slots" id="slots" class="form-control form-control-sm rounded-0 text-right" value="<?php //echo isset($slots) ? $slots : ''; ?>"  required/>
		</div> -->
		<div class="form-group">
			<label for="price" class="control-label">Price per Month</label>
			<input type="number" step="any" min="0" name="price" id="price" class="form-control form-control-sm rounded-0 text-right" value="<?php echo isset($price) ? $price : 0; ?>"  required/>
		</div>
		<div class="form-group">
			<label for="status" class="control-label">Status</label>
			<select name="status" id="status" class="form-control form-control-sm rounded-0" required>
			<option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
			<option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
			</select>
		</div>
	</form>
</div>
<script>
	$(document).ready(function(){
		$('#uni_modal').on('shown.modal.bs', function(){
			$('#dorm_id').select2({
				placeholder:'Please Select Dorm Here',
				width:"100%",
				containerCssClass:"form-control form-control-sm rounded-0"
			})
		})
		$('#room-form').submit(function(e){
			e.preventDefault();
            var _this = $(this)
			 $('.err-msg').remove();
			start_loader();
			$.ajax({
				url:_base_url_+"classes/Master.php?f=save_room",
				data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
				error:err=>{
					console.log(err)
					alert_toast("An error occured",'error');
					end_loader();
				},
				success:function(resp){
					if(typeof resp =='object' && resp.status == 'success'){
						location.reload()
					}else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            $("html, body, .modal").scrollTop(0)
                            end_loader()
                    }else{
						alert_toast("An error occured",'error');
						end_loader();
                        console.log(resp)
					}
				}
			})
		})

	})
</script>