<?php include("header.php"); ?>

<div class="content-wrapper">

    <div class="d-flex main-heading">
        <h1>Settings</h1>
        <!-- <a class="btn" href="#">Add New Region</a> -->
    </div>

    <div class="mb-5 grid-bg form-grid">
        <form>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn-grid">
                        <button type="submit" class="btn">Save</button>
                        <button type="submit" class="btn btn-grey">Cancel</button>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            <div class="mb-3">

                <h4 class="h3">Change Password</h4>

            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Old Password</label>
                        <input type="password" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn-grid">
                        <button type="submit" class="btn">Save</button>
                        <button type="submit" class="btn btn-grey">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>




</div>

<?php include("footer.php"); ?>