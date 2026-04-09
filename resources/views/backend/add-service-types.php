<?php include("header.php"); ?>

<div class="content-wrapper">

    <div class="d-flex main-heading">
        <h1>Add Service Types</h1>
        <!-- <a class="btn" href="#">Add New Region</a> -->
    </div>

    <div class="mb-5 grid-bg form-grid">
        <form>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group custom-select">
                        <label class="form-label">Service Name</label>
                        <select class="form-select form-control selectpicker">
                            <option selected>David Smith (davidsmith56@gmail.com)</option>
                            <option value="1">Robert (robert34@gmail.com)</option>
                            <option value="2">Elizabeth Grace (elizabethgrace35@gmail.com)</option>
                            <option value="3">Clara Barton (clarabarton56@gmail.com)</option>
                            <option value="4">Dorothea Dix (dorotheadix75@gmail.com)</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Service Hourly Rate</label>
                        <input type="text" class="form-control" placeholder="$15000">
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

    <div class="mb-5 d-flex sec-search-bar">

        <h4 class="h3">All Service Types</h4>

        <form class="d-flex">
            <div class="search-bar-grid">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control m-0" placeholder="Search">
            </div>
            <div class="search-bar-grid">
                <i class="fa fa-filter"></i>
                <input type="text" class="form-control m-0" placeholder="Filters">
            </div>
            <div>
                <!-- <button type="submit" class="btn">
                    Search
                </button> -->

                <div class="dropdown">
                    <button class="btn btn-grey dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        Download
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </div>

            </div>
        </form>


    </div>

    <div class="form-table bg-style">
        <form>
            <div class="table-grid">
                <div class="table100-nextcols table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr class="row100 head">
                                <th class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label" style="font-weight: unset;">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            <span>Sr. No. <i class="fa fa-sort"></i></span>
                                        </label>
                                    </div>

                                </th>
                                <th class="cell100 column2">
                                    <span>Service Name <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column3">
                                    <span>Service Hourly Rate <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column4">
                                    <span>Action <i class="fa fa-sort"></i></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-----------row-------------->

                            <tr>
                                <td class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            1
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">Roof Maintenance</td>
                                <td class="cell100 column3">$1500</td>
                                <td class="cell100 column4">
                                    <div class="dropdown">
                                        <button class="btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    <span>Edit</span></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    <span>Delete</span></a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-----------row-------------->

                            <tr>
                                <td class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            2
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">Roof Maintenance</td>
                                <td class="cell100 column3">$1500</td>
                                <td class="cell100 column4">
                                    <div class="dropdown">
                                        <button class="btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    <span>Edit</span></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    <span>Delete</span></a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>


                            <!-----------row-------------->

                            <tr>
                                <td class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            3
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">Roof Maintenance</td>
                                <td class="cell100 column3">$1500</td>
                                <td class="cell100 column4">
                                    <div class="dropdown">
                                        <button class="btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    <span>Edit</span></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    <span>Delete</span></a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>


                            <!-----------row-------------->

                            <tr>
                                <td class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            4
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">Roof Maintenance</td>
                                <td class="cell100 column3">$1500</td>
                                <td class="cell100 column4">
                                    <div class="dropdown">
                                        <button class="btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    <span>Edit</span></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    <span>Delete</span></a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>


                            <!-----------row-------------->

                            <tr>
                                <td class="cell100 column1">
                                    <div class="check-grid">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="" />
                                            5
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">Roof Maintenance</td>
                                <td class="cell100 column3">$1500</td>
                                <td class="cell100 column4">
                                    <div class="dropdown">
                                        <button class="btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    <span>Edit</span></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    <span>Delete</span></a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-----------row-------------->




                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>


</div>

<?php include("footer.php"); ?>