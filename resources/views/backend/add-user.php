<?php include("header.php"); ?>

<div class="content-wrapper">

    <div class="d-flex main-heading">
        <h1>Add Service</h1>
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
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Role</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>Admin</option>
                            <option value="1">Staff</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" placeholder="">
                    </div>
                </div>


                <div class="col-lg-12">
                    <div class="btn-grid">
                        <button type="submit" class="btn">Add</button>
                        <button type="submit" class="btn btn-grey">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>




</div>

<?php include("footer.php"); ?>