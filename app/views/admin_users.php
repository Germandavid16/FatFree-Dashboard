<? if ($users) { ?>
<div class="card">
	<table class="table">
		<thead>
			<tr>
				<th class="text-right fc-time">Login</th>
				<th class="text-center">E-mail</th>
				<th>Role</th>
				<th>Last change</th>
				<th>Last login</th>
				<th>Clinic Name</th>
				<th>Doctor Name</th>
				<th>Active</th>
				<th>Active</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<? foreach ($users as $user) { ?>
			<tr data-id="<?=$user['id']?>">
				<td class="text-right"><?=$user['login']?></td>
				<td class="text-center"><?=$user['email']?></td>
				<td><?=$user['role']?></td>
				<td><?=$user['date_change']?></td>
				<td><?=$user['last_visit']?></td>
				<td><?=$user['clinic_name']?></td>
				<td><?=$user['doc_first_name'].' '.$user['doc_last_name']?></td>
				<td>
					<div class="checkbox">
	                    <label>
	                        <input type="checkbox" class="active" <?if($user['active']){?> checked<?}?><?if ($user['id'] == $current_user){?> disabled<?}?>>
	                    </label>
	                </div>
				</td>
				<td>
					<a href="<?=$user['link']?>" rel="tooltip"  title="Edit" class="edit btn btn-info btn-round"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
				</td>
				<td>
					<a href="#" rel="tooltip"  class="del btn btn-danger btn-round" title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>
				</td>
			</tr>
			<? } ?>   
		</tbody>
	</table>
</div>

<script>
var CSRF = '<?=$CSRF?>';
$('.del').on('click', function() {
	tr = $(this).parents('tr');
	id = tr.data('id');
	name = tr.find('.email').text();
	if (!confirm('Are you sure you want to delete user "'+name+'" and all linked data?')) {
		return false;
	}
	$.ajax({
		url: '',
		dataType: 'json',
		method: 'POST',
		data: 'delete='+id+'&token='+CSRF,
	}).done(function(page) {
		$('input[name="token"]').val(page['CSRF']);
		CSRF = page['CSRF'];
		if (page['deleted']) {
			tr.hide(400);
			tr.remove();
		}
	});
	return false;
});    
$('.active').on('change', function() {
	input = $(this);
	tr = input.parents('tr');
	id = tr.data('id');
	state = input.prop('checked');
	input.prop('disabled', true);
	$.ajax({
		url: '',
		dataType: 'json',
		method: 'POST',
		data: 'active='+id+'&state='+state+'&token='+CSRF,
	}).done(function(page) {
		$('input[name="token"]').val(page['CSRF']);
		CSRF = page['CSRF'];
		if (page['active_changed']) {
			input.prop('disabled', false);
		}
	});
});    
</script>    
<? } ?>