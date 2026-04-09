<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Welcome | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="shortcut icon" href="favicon.png" sizes="32x32" type="image/x-icon">
    <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>


<body>

    <header id="header" class="bg-sitecolor">
        <div class="header-area">
            <div class="site-m-logo d-lg-none">
                <div class="site-menu">
                    <i class="las la-bars"></i>
                    <i class="las la-times"></i>
                </div>
                <a href="index.php">
                    <img src="images/logo.png" alt="logo">
                </a>
            </div>

            <div class="header-right d-flex">



                <ul class="notification m-0 d-flex">
                    <li>
                        <form>
                            <div class="search-box-header">
                                <input type="text" class="form-control">
                                <button type="submit" class="btn-search"><i class="las la-search"></i></button>
                            </div>
                        </form>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-bell"></i></a>
                    </li>
                </ul>

                <div class="profile-action">
                    <div id="mSearch" class="m-search-icon d-md-none">
                        <i class="las la-search"></i>
                        <i class="las la-times"></i>
                    </div>
                    <a>
                        <div class="user-img" style="background-image: url('images/demo-img.png');"></div>
                        <h6>David Smith </h6><i class="fa fa-caret-down"></i>
                    </a>
                    <div class="drop-area">
                        <ul>
                            <li>
                                <a href="owner-profile.php">
                                    <i class="las la-lock"></i> <span>Change Password</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void()">
                                    <i class="las la-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </header>

    <div class="layer"></div>

    <aside class="main-sidebar bg-sitecolor">
        <div class="site-logo d-none d-lg-flex">
            <a href="index.php">
                <img src="images/logo.png" alt="logo-dashboard">
            </a>
        </div>
        <section class="sidebar">
            <ul>
                <li class="active">
                    <a href="index.php">
                        <i class="las la-border-all"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-map-marked-alt"></i>
                        <span>Regions</span>
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="las la-cog"></i>
                        <span>Services</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-users"></i>
                        <span>Customers</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Add Customer</a></li>
                        <li><a class="dropdown-item" href="#">All Customers</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-route"></i>
                        <span>Route Planning</span>
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="las la-suitcase"></i>
                        <span>All Jobs</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="las la-cogs"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void()">
                        <i class="las la-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </section>
        <div class="side-copy text-center">
            <p>© 2023. ALL RIGHTS RESERVED</p>
        </div>
    </aside>