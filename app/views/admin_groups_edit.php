<script type="text/javascript">var edit_flag = 0; </script>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<form class="form-horizontal" method="POST">
						<input type="hidden" name="token" value="<?=$CSRF?>">
						<div class="card-header card-header-icon" data-background-color="green">
							<i class="material-icons">beenhere</i>
						</div>
						<div class="card-content customer_card">
							<h4 class="card-title" style="color:#51ab56"><?=$title?></h4>

							<?php if (isset($data)) { ?>
								<script type="text/javascript">
									edit_flag = 1;
									insurances = <?php echo json_encode($data['insurances']); ?>
								</script>

								<input type="hidden" name="id" value="<?=$data['id']?>">
								<div class="row" style="margin-bottom: 30px">
									<div class="col-md-6 col-sm-offset-3 dontAskConfirmation">
										<label class="col-sm-3 label-on-left">Group Name:</label>
										<div class="col-md-9">
											<input class="form-control" name="group_name" type="text" required="true" value="<?=$data['group_name']?>">
										</div>
									</div>
								</div>
								<div class="row group" style="margin-bottom: 30px">
									<div class="col-md-2 text-center">
										<a class="btn btn-warning btn-sm insurance_plus">
											<i class="material-icons" style="font-size: 30px;">playlist_add</i>
										</a>
									</div>
									<div class="col-md-10" id="main_content">
									<?php foreach ($data['insurances'] as $key => $insurance_value) { ?>

										
										<div class="row sub_content" style="margin-bottom: 30px">
											<!-- ================= Insurance Content ======================= -->
											<div class="col-md-5">
												<div class="col-md-10 select_bar">
													<select class="selectpicker insurance_select" data-style="select-with-transition" title="Choose Insurance">
														<option disabled> Choose Insurance</option>
														<?php foreach ($insurances_all as $key1 => $insurance) { ?>
															<option value="<?=$insurance['id']?>" <?php echo $insurance_value['id']==$insurance['id']?'selected':'' ?>> <?=$insurance['title']?> </option>
														<?php } ?>
														<input type="hidden" class="selected_value" name="insurance_ids[]" value="<?=$insurance_value['id']?>">
													</select>
												</div>
												<a class="btn btn-sm btn-just-icon btn-round insurance_delete"><i class="material-icons" style="font-size: 20px;">delete_sweep</i></a>
											</div>

											<!-- ==================== Field Content ======================= -->
											<div class="col-md-7 field_group">
												<label class="col-sm-2 label-on-left">Fields:</label>
												<div class="col-md-8 field_content">
													<?php 
													if (isset($data['series'][$insurance_value['id']]) || !empty($data['series'][$insurance_value['id']])) {
														foreach ($data['series'][$insurance_value['id']] as $key2 => $value) {
													?>
													<div class="row">
														<div class="col-md-10">
															<input class="form-control" name="fields[]" type="text" value="<?=$value?>" >
														</div>
														<a class="btn btn-just-icon btn-round btn-sm field_delete"><i class="material-icons">delete</i></a>
													</div>
													
													<?php } 
													} else {
													?>

													<div class="row">
														<div class="col-md-10">
															<input class="form-control" name="fields[]" type="text" >
														</div>
														<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
													</div>
													<div class="row">
														<div class="col-md-10">
															<input class="form-control" name="fields[]" type="text" >
														</div>
														<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
													</div>
													<?php } ?>
												</div>

												<div class="col-md-2">
													<a class="field_plus btn btn-just-icon btn-round btn-warning">
														<i class="material-icons">exposure_plus_1</i>
													</a>
													<input type="hidden" class="field_count" name="f_count[]" value="<?php echo count($data['series'][$insurance_value['id']]) == 0 ? '2' : count($data['series'][$insurance_value['id']]); ?>">
												</div>
											</div>
										</div>

									<?php } ?>
								</div>
								<div class="row text-center">
									<button id="account_plus" class="btn btn-success">
										Save
									</button>
								</div>

							<?php } else { ?>
								<div class="row" style="margin-bottom: 30px">
									<div class="col-md-6 col-sm-offset-3 dontAskConfirmation">
										<label class="col-sm-3 label-on-left">Group Name:</label>
										<div class="col-md-9">
											<input class="form-control" name="group_name" type="text" required="true">
										</div>
									</div>
								</div>
								
								<div class="row group" style="margin-bottom: 30px">
									<div class="col-md-2 text-center ">
										<a class="btn btn-warning btn-sm insurance_plus">
											<i class="material-icons" style="font-size: 30px;">playlist_add</i>
										</a>
									</div>
									<div class="col-md-10" id="main_content">
										<div class="row sub_content" style="margin-bottom: 30px">
											<div class="col-md-5">
												<div class="col-md-10 select_bar">
													<select class="selectpicker insurance_select" data-style="select-with-transition" title="Choose Insurance">
														<option disabled> Choose Insurance</option>
														<?php foreach ($insurances_all as $key => $insurance) { ?>
															<option value="<?=$insurance['id']?>"> <?=$insurance['title']?> </option>
														<?php } ?>
													</select>
													<input type="hidden" class="selected_value" name="insurance_ids[]">
												</div>
												<a class="btn btn-sm btn-just-icon btn-round insurance_delete"><i class="material-icons" style="font-size: 20px;">delete_sweep</i></a>
											</div>

											<div class="col-md-7 field_group">
												<label class="col-sm-2 label-on-left">Fields:</label>
												<div class="col-md-8 field_content">
													<div class="row">
														<div class="col-md-10">
															<input class="form-control" name="fields[]" type="text" >
														</div>
														<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
													</div>
													<div class="row">
														<div class="col-md-10">
															<input class="form-control" name="fields[]" type="text" >
														</div>
														<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
													</div>
												</div>
												<div class="col-md-2">
													<a class="field_plus btn btn-just-icon btn-round btn-warning">
														<i class="material-icons">exposure_plus_1</i>
													</a>
													<input type="hidden" class="field_count" name="f_count[]" value="2">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row text-center">
									<button id="account_plus" class="btn btn-success">
										Add
									</button>
								</div>
							<?php } ?>
						</div>
					</form>
				</div>
			</div>

			<div class="col-md-10" id="insurance_content" style="display: none">
				<div class="row sub_content" style="margin-bottom: 30px">
					<div class="col-md-5">
						<div class="col-md-10 select_bar">
							<select class="insurance_select" name="insurance_ids[]" data-style="select-with-transition" title="Choose Insurance">
								<option disabled> Choose Insurance</option>
								<?php foreach ($insurances_all as $key => $insurance) { ?>
									<option value="<?=$insurance['id']?>"> <?=$insurance['title']?> </option>
								<?php } ?>
							</select>
							<input type="hidden" class="selected_value" name="insurance_ids[]">
						</div>
						<a class="btn btn-sm btn-just-icon btn-round insurance_delete"><i class="material-icons" style="font-size: 20px;">delete_sweep</i></a>
					</div>

					<div class="col-md-7 field_group">
						<label class="col-sm-2 label-on-left">Fields:</label>
						<div class="col-md-8 field_content">
							<div class="row">
								<div class="col-md-10">
									<input class="form-control" name="fields[]" type="text" >
								</div>
								<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
							</div>
							<div class="row">
								<div class="col-md-10">
									<input class="form-control" name="fields[]" type="text" >
								</div>
								<a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a>
							</div>
						</div>
						<div class="col-md-2">
							<a class="field_plus btn btn-just-icon btn-round btn-warning">
								<i class="material-icons">exposure_plus_1</i>
							</a>
							<input type="hidden" class="field_count" name="f_count[]" value="2">
						</div>
					</div>
				</div>
			</div>

			<p class="copyright pull-right bottom_name"></p>
		</div>
	</div>
</div>

<script type="text/javascript">
	var field_html = '<div class="row"><div class="col-md-10"><input class="form-control" name="fields[]" type="text" ></div><a class="field_delete btn btn-just-icon btn-round btn-sm"><i class="material-icons">delete</i></a></div>';

	var insurance_count = "<?php echo count($insurances_all); ?>"
	var element_count = 1;


	var previous;

	$(document).ready(function() {
		
		if (edit_flag) {
			var insu_selecs = $('select.insurance_select');
			for (key in insurances) {
				var order = getOrder(insu_selecs[0].options, insurances[key]['id']);
				for (var i = insu_selecs.length - 1; i >= 0; i--) {
					insu_selecs[i].options[order].disabled = true;
				}
			}
			element_count = insurances.length;
		}

		var default_select = $('#insurance_content').find('select');

		$('.insurance_plus').click(function() {
			default_select.addClass('new');
			var html = $('#insurance_content').html();
			default_select.removeClass('new');
			if (element_count < insurance_count) {
				element_count++;
				$('#main_content').append(html);
				$('select.new').addClass('selectpicker');
				$('select.new').removeClass('new');
				$('.selectpicker').selectpicker('refresh');
			}
		});

		$('body').on('click', '.field_plus', function () {
			var f_count = $(this).closest('div').find('.field_count');
			var f_group = $(this).parents('div.field_group').find('.field_content');
			var count = f_count.val();
			f_count.val(parseInt(count) + 1);
			f_group.append(field_html);
		});

		$('body').on('click', '.insurance_delete', function() {
			var div = $(this).parents('div.sub_content');
			var select_value = div.find('select.insurance_select')[0].value;
			console.log(select_value.length);
			if (select_value.length) {
				var selects = $('.selectpicker.insurance_select');
				value_order = getOrder(selects[0].options, select_value);
				for (var i = selects.length-1; i >= 0; i--) {
					selects[i].options[value_order].disabled = false;
				}
				$('.selectpicker.insurance_select').selectpicker('refresh');
				default_select[0].options[parseInt(value_order) - 1].disabled = false;
			}
			element_count--;
			div.remove();
		});

		$('body').on('click', '.field_delete', function() {
			var div = $(this).parent('div.row');
			var input = div.find('input[name="fields[]"]').val();
			var field_count = $(this).parents('div.sub_content').find('.field_count');
			field_count.val(parseInt(field_count.val()) - 1);
			div.remove();
		});

		$('body').on('change', '.insurance_select', function() {
			if ($(this).val().length === 0) return;
			var selects = $('.selectpicker.insurance_select');
			var input_value = $(this).parents('div.select_bar').find('input.selected_value');

			var value = $(this).val();
			input_value.val(value);
			value_order = getOrder(selects[0].options, value);
			previous_order = getOrder(selects[0].options, previous);
			for (var i = selects.length-1; i >= 0; i--) {
				selects[i].options[previous_order].disabled = false;
				selects[i].options[value_order].disabled = true;
			}
			$('.selectpicker.insurance_select').selectpicker('refresh');
			if (previous_order.length > 0)
				default_select[0].options[parseInt(previous_order) - 1].disabled = false;
			default_select[0].options[parseInt(value_order) - 1].disabled = true;
		});

		$('body').on('shown.bs.select', '.insurance_select', function() {
			previous = $(this).find('option:selected').val();
			console.log(previous);
		});

		function getOrder(array, value) {
			for (var i = array.length - 1; i >= 0; i--) {
				if (array[i].value == value)
					return i;
			}
		}
	})
</script>