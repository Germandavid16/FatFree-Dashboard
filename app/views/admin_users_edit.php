<div class="content">
	<div class="container-fluid">
		<div class="col-sm-10 col-sm-offset-1">
			<!--      Wizard container        -->
			<div class="wizard-container">
				<div class="card wizard-card" data-color="rose" id="wizardProfile">
					<form method="POST" id="account_form">
						<input type="hidden" name="token" value="<?=$CSRF?>">

						<!--        You can switch " data-color="purple" "  with one of the next bright colors: "green", "orange", "red", "blue"       -->
						<div class="wizard-header">
							<h2 class="wizard-title">
								<?php echo empty($mpt_name)?'Build Your Profile':'Welcome '.$mpt_name; ?>
							</h2>
						</div>
						<div class="wizard-navigation">
							<ul>
								<li>
									<a href="#user" data-toggle="tab">User Info</a>
								</li>
								<li>
									<a href="#account" data-toggle="tab">Medical Practice</a>
								</li>
								<li>
									<a href="#address" data-toggle="tab">Medical Provider</a>
								</li>
								<li>
									<a href="#insurance" data-toggle="tab">Insurances</a>
								</li>
							</ul>
						</div>
						<div class="tab-content">
							<div class="tab-pane" id="user">
								<div class="row col-lg-offset-1" style="margin-top: 20px; margin-bottom:30px">

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Email</label>
											<input name="email" type="email" class="form-control" value="<?=$user_email?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Login</label>
											<input type="text" name="login" class="form-control" value="<?=$user_login?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">First Name</label>
											<input type="text" name="name" class="form-control" value="<?=$user_name?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Last Name</label>
											<input type="text" name="last_name" class="form-control" value="<?=$user_last_name?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group">
											<select name="role" class="selectpicker" data-style="select-with-transition" title="Choose Role" required>
												<option disabled> Choose Role</option>
												<option value="doctor"<?if($user_role == 'doctor'){?> selected<?}?>>Doctor</option>
												<option value="admin"<?if($user_role == 'admin'){?> selected<?}?>>Admin</option>
											</select>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group">
											<select  name="gender" class="selectpicker" data-style="select-with-transition" title="Choose Gender" required>
												<option disabled>None</option>
												<option value="M"<?if($user_gender == 'M'){?> selected<?}?>>Male</option>
												<option value="F"<?if($user_gender == 'F'){?> selected<?}?>>Female</option>
											</select>
										</div>
									</div>

									

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">New password:</label>
											<input type="password" class="form-control" name="password_new" <?php if (empty($user_login)) echo "required"; ?>>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Confirm new password:</label>
											<input type="text" class="form-control" name="password_confirm" <?php if (empty($user_login)) echo "required"; ?>>
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane" id="account">
								<div class="row col-lg-offset-1" style="margin-top: 20px; margin-bottom:30px">

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Name:</label>
											<input name="mpt_name" type="text" class="form-control" value="<?php echo isset($mpt_name)? $mpt_name: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Address 1:</label>
											<input type="text" name="mpt_address1" class="form-control" value="<?php echo isset($mpt_address1)? $mpt_address1: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Address 2:</label>
											<input type="text" name="mpt_address2" class="form-control" value="<?php echo isset($mpt_address2)? $mpt_address2: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">City:</label>
											<input type="text" name="mpt_city" class="form-control" value="<?php echo isset($mpt_city)? $mpt_city: ''; ?>" required>
										</div>
									</div>

									<? if ($regions) { ?>
									<div class="col-sm-5">
										<div class="form-group">
											<select  name="mpt_state" class="selectpicker" data-style="select-with-transition" title="Choose State">
												<option disabled>- Choose State -</option>
												<? foreach ($regions as $region) { ?>
												<option value="<?=$region['id']?>"<?if ($region['id'] == $default_region){?> selected<?}?>><?=$region['name']?></option>
												<? } ?> 
											</select>
										</div>
									</div>
									<?php } ?>

									<? if ($countries) { ?>
									<div class="col-sm-5">
										<div class="form-group">
											<select  name="mpt_country" class="selectpicker" data-style="select-with-transition" title="Choose Country">
												<option value="0">- choose country -</option>
												<? foreach ($countries as $country) { ?>
												<option value="<?=$country['id']?>"<?if ($country['id'] == $default_country){?> selected<?}?>><?=$country['name']?></option>
												<? } ?>
											</select>
										</div>
									</div>
									<?php } ?>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Full Zip:</label>
											<input type="text" name="mpt_zipcode" class="form-control" value="<?php echo isset($mpt_zipcode)? $mpt_zipcode: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Tel:</label>
											<input type="text" name="mpt_tel" class="form-control" value="<?php echo isset($mpt_tel_number)? $mpt_tel_number: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Fax:</label>
											<input type="text" class="form-control" name="mpt_fax" value="<?php echo isset($mpt_fax)? $mpt_fax: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Practice Email:</label>
											<input type="email" class="form-control" name="mpt_email" value="<?php echo isset($mpt_email)? $mpt_email: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Practice NPI:</label>
											<input type="text" class="form-control" name="mpt_npi" value="<?php echo isset($mpt_npi)? $mpt_npi: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Practice TIN:</label>
											<input type="text" class="form-control" name="mpt_tin" value="<?php echo isset($mpt_tin)? $mpt_tin: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Manager:</label>
											<input type="text" class="form-control" name="mpt_manager" value="<?php echo isset($mpt_manager)? $mpt_manager: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Manager Contact Tel:</label>
											<input type="text" class="form-control" name="mpt_contact_tel" value="<?php echo isset($mpt_contact_tel)? $mpt_contact_tel: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Affilications:</label>
											<input type="text" class="form-control" name="mpt_affilication" value="<?php echo isset($mpt_affilication)? $mpt_affilication: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Type:</label>
											<input type="text" class="form-control" name="mpt_type" value="<?php echo isset($mpt_type)? $mpt_type: ''; ?>">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="address">
								<div class="row col-lg-offset-1" style="margin-top: 20px; margin-bottom:30px">

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">First Name:</label>
											<input name="mpr_first_name" type="text" class="form-control" value="<?php echo isset($mpr_first_name)? $mpr_first_name: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Middle Name:</label>
											<input name="mpr_middle_name" type="text" class="form-control" value="<?php echo isset($mpr_middle_name)? $mpr_middle_name: ''; ?>">
										</div>
									</div>

									<div class="col-sm-12">
										<div class="col-sm-5" style="padding-left: 0px !important;">
											<div class="form-group label-floating">
												<label class="control-label">Last Name:</label>
												<input type="text" name="mpr_last_name" class="form-control" value="<?php echo isset($mpr_last_name)? $mpr_last_name: ''; ?>" required>
											</div>
										</div>
									</div>

									<div class="col-sm-12">
										<div class="col-sm-5" style="padding-left: 0px !important;">
											<div class="form-group">
												<select  name="mpr_gender" class="selectpicker" data-style="select-with-transition" title="Choose Gender" required>
													<option disabled>None</option>
													<option value="M"<?if($mpr_gender == 'M'){?> selected<?}?>>Male</option>
													<option value="F"<?if($mpr_gender == 'F'){?> selected<?}?>>Female</option>
												</select>
											</div>
										</div>
									</div>

									<div class="col-sm-12">
										<div class="col-sm-5" style="padding-left: 0px !important;">
											<div class="form-group">
												 <div class="form-group">
			                                        <label class="label-control">Date of Birthday</label>
			                                        <input type="text" class="form-control datepicker" name="mpr_dob" value="<? echo $mpr_dob == "0000-00-00"?date('Y-m-d'):$mpr_dob; ?>" required />
			                                    </div>
											</div>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Board Specialty:</label>
											<input type="text" name="mpr_board_specialty" class="form-control" value="<?php echo isset($mpr_board_spec)? $mpr_board_spec: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Board Name:</label>
											<input type="text" name="mpr_board_name" class="form-control" value="<?php echo isset($mpr_board_name)? $mpr_board_name: ''; ?>">
										</div>
									</div>

									<div class="col-sm-12">
										<div class="col-sm-5" style="padding-left: 0px !important;">
											<div class="form-group">
												 <div class="form-group">
			                                        <label class="label-control">Board Date</label>
			                                        <input type="text" class="form-control datepicker" name="mpr_board_date" value="<? echo $mpr_board_date = '0000-00-00'?date('Y-m-d'):$mpr_board_date ?>" />
			                                    </div>
											</div>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Board Number:</label>
											<input type="number" name="mpr_board_num" class="form-control" value="<?php echo isset($mpr_board_num)? $mpr_board_num: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Personal NPI:</label>
											<input type="text" name="mpr_npi" class="form-control" value="<?php echo isset($mpr_npi)? $mpr_npi: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Tax ID:</label>
											<input type="text" name="mpr_tax" class="form-control" value="<?php echo isset($mpr_tax)? $mpr_tax: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">License:</label>
											<input type="text" name="mpr_license" class="form-control" value="<?php echo isset($mpr_license)? $mpr_license: ''; ?>" required>
										</div>
									</div>

									
									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">License State:</label>
											<input type="text" name="mpr_license_state" class="form-control" value="<?php echo isset($mpr_license_state)? $mpr_license_state: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">DEA Number:</label>
											<input type="text" name="mpr_dea_num" class="form-control" value="<?php echo isset($mpr_dea_num)? $mpr_dea_num: ''; ?>" required>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Address:</label>
											<input type="text" name="mpr_address1" class="form-control" value="<?php echo isset($mpr_address1)? $mpr_address1: ''; ?>" >
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">City:</label>
											<input type="text" name="mpr_city" class="form-control" value="<?php echo isset($mpr_city)? $mpr_city: ''; ?>">
										</div>
									</div>
									
									<?php if ($regions) { ?>
									<div class="col-sm-5">
										<div class="form-group">
											<select  name="mpr_state" class="selectpicker" data-style="select-with-transition" title="Choose State">
												<option disabled>- Choose State -</option>
												<? foreach ($regions as $region) { ?>
												<option value="<?=$region['id']?>"<?if ($region['id'] == $default_region){?> selected<?}?>><?=$region['name']?></option>
												<? } ?> 
											</select>
										</div>
									</div>
									<?php } ?>

									<?php if ($countries) { ?>
									<div class="col-sm-5">
										<div class="form-group">
											<select  name="mpr_country" class="selectpicker" data-style="select-with-transition" title="Choose Country">
												<option value="0">- Choose Country -</option>
												<? foreach ($countries as $country) { ?>
												<option value="<?=$country['id']?>"<?if ($country['id'] == $mpr_country){?> selected<?}?>><?=$country['name']?></option>
												<? } ?>
											</select>
										</div>
									</div>
									<?php } ?>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Full Zip:</label>
											<input type="text" name="mpr_zipcode" class="form-control" value="<?php echo isset($mpr_zipcode)? $mpr_zipcode: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Email:</label>
											<input type="email" class="form-control" name="mpr_email" value="<?php echo isset($mpr_email)? $mpr_email: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Tel:</label>
											<input type="text" class="form-control" name="mpr_tel" value="<?php echo isset($mpr_tel)? $mpr_tel: ''; ?>">
										</div>
									</div>

									<div class="col-sm-5">
										<div class="form-group label-floating">
											<label class="control-label">Cell:</label>
											<input type="text" class="form-control" name="mpr_cell" value="<?php echo isset($mpr_cell)? $mpr_cell: ''; ?>">
										</div>
									</div>

								</div>
							</div>

							<div class="tab-pane" id="insurance">
								<div class="row col-lg-offset-1" style="margin-top: 20px; margin-bottom:40px">
									<? 
									$i = 0; 
									$user_insurances = unserialize($user_insurances);
									foreach($insurances as $insurance) { $i++; ?>
									<? if ($i == 1): ?>
										<div class="row">
									<? endif; ?>
											<div class="col-sm-4 checkbox">
												<label>
													<? if ($user_insurances): ?>
								                        <input type="checkbox" name="insurances[]" class="active" value="<?=$insurance['id']?>" <? echo in_array($insurance['id'], $user_insurances)? 'checked':''; ?> >
								                        <?=$insurance['title']?>
							                    	<? else: ?>
								                    	<input type="checkbox" name="insurances[]" class="active" value="<?=$insurance['id']?>" >
								                        <?=$insurance['title']?>
								                    <? endif; ?>
							                    </label>
											</div>
									<? if ($i == 3): $i = 0;?></div><? endif; ?>
									<? } ?>
								</div>
							</div>	
						</div>
						<div class="wizard-footer">
							<div class="pull-right">
								<input type='button' class='btn btn-next btn-fill btn-rose btn-wd' name='next' value='Next' />
								<input type='button' class='btn btn-finish btn-fill btn-rose btn-wd' name='finish' value='Finish' />
							</div>
							<div class="pull-left">
								<input type='button' class='btn btn-previous btn-fill btn-default btn-wd' name='previous' value='Previous' />
							</div>
							<div class="clearfix"></div>
						</div>
					</form>
				</div>
			</div>
			<!-- wizard container -->
		</div>
	</div>
</div>

<script>

var CSRF = '<?=$CSRF?>';
var countries = $('select[name="country"]');
var regions = $('select[name="region"]');
var cities = $('select[name="city"]');

function putSelectList(sel, data) {
	clearSelectList(sel);
	text = sel.html();
	for (i = 0; i < data.length; i++) {
		if (data[i]['id'] && data[i]['name']) {
			text += "<option value=\""+data[i]['id']+"\">"+data[i]['name']+"</option>\n";
		}
	}
	sel.html(text);
}

function clearSelectList(sel) {
	sel.html("<option value=\"0\">- choose -</option>\n");
}

countries.on('change', function() {
	country = countries.val();
	clearSelectList(regions);
	clearSelectList(cities);
	regions.prop('disabled', true);
	cities.prop('disabled', true);
	$.ajax({
		url: '',
		dataType: 'json',
		method: 'POST',
		data: 'getregions=1&country='+country+'&token='+CSRF,
	}).done(function(page) {
		$('input[name="token"]').val(page['CSRF']);
		CSRF = page['CSRF'];
		putSelectList(regions, page['regions']);
		regions.prop('disabled', false);
	});
});

regions.on('change', function() {
	country = countries.val();
	region = regions.val(); 
	clearSelectList(cities);
	cities.prop('disabled', true);
	$.ajax({
		url: '',
		dataType: 'json',
		method: 'POST',
		data: 'getcities=1&region='+region+'&country='+country+'&token='+CSRF,
	}).done(function(page) {
		$('input[name="token"]').val(page['CSRF']);
		CSRF = page['CSRF'];
		putSelectList(cities, page['cities']);
		cities.prop('disabled', false);
	});
});

$(document).ready(function() {
	demo.initMaterialWizard();
	setTimeout(function() {
		$('.card.wizard-card').addClass('active');
	}, 600);

	$('input[name="finish"]').click(function() {
		$('#account_form').submit();
	});

	$('input.btn-finish').click(function() {
		var gender = $('select[name="mpr_gender"]').val();
		if (gender.length == 0) {
			alert("Please select Gender");
		}
	});

	demo.initFormExtendedDatetimepickers();
});

</script>