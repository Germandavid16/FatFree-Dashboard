<? if ($h1_inner) { ?>
<h1><?=$h1_inner?></h1>
<? } ?>
<? if ($h2_inner) { ?>
<h2><?=$h2_inner?></h2>
<? } ?>
<form method="post">
	<input type="hidden" name="token" value="<?=$CSRF?>">
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-3">
			<div class="form-group label-floating">
		        <label class="control-label">E-mail or user name:</label>
		        <input type="text" name="login" id="login"  class="form-control">
		    </div>
		</div>
	</div>
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-3">
		    <div class="form-group label-floating ">
		        <label class="control-label">Password</label>
		        <input type="password" name="password" id="password" class="form-control">
		    </div>
		</div>
	</div>
	<button class="btn btn-primary">Enter</button>

    <p style="margin-top:20px"><a href="/recovery">Forgot your password?</a></p>
</form>