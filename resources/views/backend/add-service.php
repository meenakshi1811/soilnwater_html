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
                    <div class="form-group custom-select">
                        <label class="form-label">Customer</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>David Smith</option>
                            <option value="1">Robert</option>
                            <option value="2">Elizabeth Grace</option>
                            <option value="3">Clara Barton</option>
                            <option value="4">Dorothea Dix</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Service</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>Roof Maintenance</option>
                            <option value="1">Robert</option>
                            <option value="2">Elizabeth Grace</option>
                            <option value="3">Clara Barton</option>
                            <option value="4">Dorothea Dix</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Service Time Interval</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>Quarterlu</option>
                            <option value="1">Robert</option>
                            <option value="2">Elizabeth Grace</option>
                            <option value="3">Clara Barton</option>
                            <option value="4">Dorothea Dix</option>
                        </select>
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