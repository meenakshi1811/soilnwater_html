<?php include("header-top.php"); ?>


<div class="login-div">
    <div class="container">

        <div class="login-grid">
            <div class="text-center main-heading mb-3">
                <h1>LogIn</h1>
            </div>

            <form>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="text" class="form-control" placeholder="davidsmith@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <!-- <input type="Password" class="form-control" placeholder="************"> -->

                    <div class="form-group" id="show_hide_password">
                        <input class="form-control" type="password">
                        <div class="input-group-addon">
                            <a href=""><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>
                    </div>

                    <a class="forgot-password" href="forgot-pssword.php">Forgot Password?</a>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn w-100">Log In</button>
                </div>
            </form>

        </div>


    </div>
</div>

<?php include("footer.php"); ?>