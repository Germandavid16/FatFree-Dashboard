<div class="wrapper wrapper-full-page">
    <div class="full-page login-page" filter-color="black" data-image="img/login.jpg">
        <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-md-offset-4 col-sm-offset-3">
                        <form method="POST">
                        	<input type="hidden" name="token" value="<?=$CSRF?>">
                            <div class="card card-login card-hidden">
                                <div class="card-header text-center" data-background-color="blue">
                                    <h3 class="card-title">Sign In</h3>
                                    <div class="social-line">
                                        <a href="#" class="card-title">
                                            Precision Quality
                                        </a>
                                    </div>
                                </div>
                                <p class="category text-center">
                                   &nbsp
                                </p>
                                <div class="card-content">
                                    <div class="input-group" style="margin-bottom: 10px">
                                        <span class="input-group-addon">
                                            <i class="material-icons">face</i>
                                        </span>
                                        <div class="form-group label-floating">
                                            <label class="control-label">E-mail or user name:</label>
                                            <input type="text" name="login" id="login"  class="form-control">
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="material-icons">lock_outline</i>
                                        </span>
                                        <div class="form-group label-floating">
                                            <label class="control-label">Password</label>
                                            <input type="password" name="password" id="password" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-rose btn-lg">Enter</button>
                                </div>
                                <p style="margin-top:20px" class="text-center"><a href="/recovery">Forgot your password?</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>