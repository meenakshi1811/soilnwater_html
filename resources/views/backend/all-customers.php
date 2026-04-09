<?php include("header.php"); ?>

<div class="content-wrapper">


    <div class="mb-5 d-flex sec-search-bar">

        <h1>All Customers</h1>

        <form class="d-flex">
            <div class="search-bar-grid">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control m-0" placeholder="Search">
            </div>
            <div class="search-bar-grid">
                <i class="fa fa-filter"></i>
                <input type="text" class="form-control m-0" placeholder="Filters">
            </div>
            <div class="me-3">

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
            <div>
                <button type="submit" class="btn">Add Service</button>
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
                                            <span>Name <i class="fa fa-sort"></i></span>
                                        </label>
                                    </div>

                                </th>
                                <th class="cell100 column2">
                                    <span>Email <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column3">
                                    <span>Phone Number <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column4">
                                    <span>Address <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column5">
                                    <span>Postcode <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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
                                            Clara Barton
                                        </label>
                                    </div>
                                </td>
                                <td class="cell100 column2">
                                    <a href="mailto:clarabarton56@gmail.com;">clarabarton56@gmail.com</a>
                                </td>
                                <td class="cell100 column3">
                                    <a href="tel:(212)340-1431;">(212)340-1431</a>
                                </td>
                                <td class="cell100 column4">
                                    47 W 13th St, New York
                                </td>
                                <td class="cell100 column5">
                                    10001
                                </td>
                                <td class="cell100 column6">
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