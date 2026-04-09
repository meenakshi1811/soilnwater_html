<?php include("header.php"); ?>

<div class="content-wrapper">

    <div class="d-flex main-heading">
        <h1>Dashboard</h1>
        <a class="btn" href="#">Add New Region</a>
    </div>


    <div class="categories-grid">
        <div class="grid">
            <div class="coloum" style="background-image: url('./images/4.jpg');">
                <div class="d-flex mb-3 coloum-heading">
                    <figure>
                        <img src="images/icn-1.png" alt="">
                    </figure>
                    <h2 class="h4">Regions</h2>
                </div>
                <h3 class="h2">10</h3>
            </div>
            <div class="coloum" style="background-image: url('./images/3.jpg');">
                <div class="d-flex mb-3 coloum-heading">
                    <figure>
                        <img src="images/icn-2.png" alt="">
                    </figure>
                    <h2 class="h4">Services</h2>
                </div>
                <h3 class="h2">5</h3>
            </div>
            <div class="coloum" style="background-image: url('./images/2.jpg');">
                <div class="d-flex mb-3 coloum-heading">
                    <figure>
                        <img src="images/icn-3.png" alt="">
                    </figure>
                    <h2 class="h4">customers</h2>
                </div>
                <h3 class="h2">2.5K</h3>
            </div>
            <div class="coloum" style="background-image: url('./images/1.jpg');">
                <div class="d-flex mb-3 coloum-heading">
                    <figure>
                        <img src="images/icn-4.png" alt="">
                    </figure>
                    <h2 class="h4">Area Admins</h2>
                </div>
                <h3 class="h2">10</h3>
            </div>
        </div>
    </div>


    <div class="mb-5 d-flex sec-search-bar">

        <h4 class="h3">Upcoming Service</h4>

        <form class="d-flex">
            <div class="search-bar-grid">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control m-0" placeholder="Search">
            </div>
            <div class="search-bar-grid">
                <div class='input-group date' id='startDate'>
                    <input type='date' class="form-control ps-2" name="startDate" />
                    <!-- <span class="input-group-addon input-group-text"><span class="fa fa-calendar"></span>
                    </span> -->
                </div>
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
                                            <span>Name <i class="fa fa-sort"></i></span>
                                        </label>
                                    </div>

                                </th>
                                <th class="cell100 column2">
                                    <span>Email <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column3">
                                    <span>Address <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column4">
                                    <span>Phone Number <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column5">
                                    <span>Date <i class="fa fa-sort"></i></span>
                                </th>
                                <th class="cell100 column6">
                                    <span>Cost <i class="fa fa-sort"></i></span>
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
                                <td class="cell100 column3">47 W 13th St, New York</td>
                                <td class="cell100 column4"><a href="tel:(212)340-1431;">(212)340-1431</a></td>
                                <td class="cell100 column5">20 Nov, 2023</td>
                                <td class="cell100 column6">$150</td>
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
                                <td class="cell100 column3">47 W 13th St, New York</td>
                                <td class="cell100 column4"><a href="tel:(212)340-1431;">(212)340-1431</a></td>
                                <td class="cell100 column5">20 Nov, 2023</td>
                                <td class="cell100 column6">$150</td>
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
                                <td class="cell100 column3">47 W 13th St, New York</td>
                                <td class="cell100 column4"><a href="tel:(212)340-1431;">(212)340-1431</a></td>
                                <td class="cell100 column5">20 Nov, 2023</td>
                                <td class="cell100 column6">$150</td>
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
                                <td class="cell100 column3">47 W 13th St, New York</td>
                                <td class="cell100 column4"><a href="tel:(212)340-1431;">(212)340-1431</a></td>
                                <td class="cell100 column5">20 Nov, 2023</td>
                                <td class="cell100 column6">$150</td>
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
                                <td class="cell100 column3">47 W 13th St, New York</td>
                                <td class="cell100 column4"><a href="tel:(212)340-1431;">(212)340-1431</a></td>
                                <td class="cell100 column5">20 Nov, 2023</td>
                                <td class="cell100 column6">$150</td>
                            </tr>

                            <!-----------row-------------->




                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>


    <div class="inner-heading">
        <h4 class="h3">Earning</h4>
    </div>


    <div class="earning">
        <div class="row">
            <div class="col-lg-6">
                <div class="grid grid-bg">
                    <div class="grid-heading mb-4">
                        <h5>$65,038<span>Spent in current year</span></h5>
                        <div class="dropdown-grid">
                            <div class="form-group">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>New York</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>Day</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <figure>
                        <img src="images/graph.jpg" alt="graph-img">
                    </figure>

                </div>
            </div>
            <div class="col-lg-6">
                <div class="grid grid-bg">
                    <div class="grid-heading mb-4">
                        <h5>$65,038<span>Spent in current year</span></h5>
                        <div class="dropdown-grid">
                            <div class="form-group">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>New York</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>Day</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <figure>
                        <img src="images/graph.jpg" alt="graph-img">
                    </figure>

                </div>
            </div>
        </div>
    </div>


</div>

<?php include("footer.php"); ?>