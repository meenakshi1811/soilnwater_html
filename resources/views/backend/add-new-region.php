<?php include("header.php"); ?>

<div class="content-wrapper">

    <div class="d-flex main-heading">
        <h1>Add New Region</h1>
        <!-- <a class="btn" href="#">Add New Region</a> -->
    </div>

    <div class="mb-5 form-grid">
        <form>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Place Name</label>
                        <div class="form-group-flex">
                            <input type="text" class="form-control" placeholder="New york">
                            <button type="submit" class="btn">Find</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="text" class="form-control" placeholder="40.712776" disabled>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="text" class="form-control" placeholder="40.54634" disabled>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Postcode</label>
                        <input type="text" class="form-control" placeholder="28568">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Region Admin</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>David Smith (davidsmith56@gmail.com)</option>
                            <option value="1">Robert (robert34@gmail.com)</option>
                            <option value="2">Elizabeth Grace (elizabethgrace35@gmail.com)</option>
                            <option value="3">Clara Barton (clarabarton56@gmail.com)</option>
                            <option value="4">Dorothea Dix (dorotheadix75@gmail.com)</option>
                        </select>
                    </div>
                </div>
                <!-- <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Satff</label>
                        <select class="form-select" aria-label="Default select example">
                            <option selected>David Smith (davidsmith56@gmail.com)</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </div> -->
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Link Authority</label>
                        <select class="form-select form-control selectpicker" multiple>
                            <option selected="">Adam Gill (adamgill56@gmail.com) </option>
                            <option value="1">Robert (robert34@gmail.com)</option>
                            <option value="2">Elizabeth Grace (elizabethgrace35@gmail.com)</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn-grid">
                        <button type="submit" class="btn btn-grey">Draft</button>
                        <button type="submit" class="btn">Publish</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


</div>

<?php include("footer.php"); ?>