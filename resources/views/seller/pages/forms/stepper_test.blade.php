@extends('admin/layout')
@section('title')
    <?php echo 'Add Product'; ?>
@endsection
@section('content')
    <div class="d-flex row align-items-center">
        <div class="col-md-6 page-info-title">
            <h3>Add Product</h3>
            <p class="sub_title">Add products with power and simplicity</p>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <nav aria-label="breadcrumb" class="float-end">
                <ol class="breadcrumb">
                    <i class='bx bx-home-smile'></i>
                    <li class="breadcrumb-item"><a href="{{ route('seller.home') }}">{{ labels('admin_labels.home', 'Home') }}</a></li>
                    <li class="breadcrumb-item second_breadcrumb_item">Product Manage</li>
                    <li class="breadcrumb-item active" aria-current="page">Add Product</li>
                </ol>
            </nav>
        </div>
    </div>
    <form method="POST" id="signup-form" class="signup-form" action="#">
        <div class="card">
            <h3>Personal info</h3>
            <fieldset>
                <div class="card">
                    <div class="card-body">
                        <h2>Personal information</h2>
                        <p class="desc">Please enter your infomation and proceed to next step so we can build your
                            account</p>
                        <div class="fieldset-content">
                            <div class="form-row">
                                <label for="" class="form-label">Name</label>
                                <div class="form-flex">
                                    <div class="form-group">
                                        <input type="text" name="first_name" id="first_name" />
                                        <span class="text-input">First</span>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="last_name" id="last_name" />
                                        <span class="text-input">Last</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">{{ labels('admin_labels.email', 'Email') }}</label>
                                <input type="email" name="email" id="email" />
                                <span class="text-input">Example :<span> Jeff@gmail.com</span>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" />
                            </div>
                            <div class="form-date">
                                <label for="birth_date" class="form-label">Birth Date</label>
                                <div class="form-date-group">
                                    <div class="form-date-item">
                                        <select id="birth_month" name="birth_month"></select>
                                        <span class="text-input">MM</span>
                                    </div>
                                    <div class="form-date-item">
                                        <select id="birth_date" name="birth_date"></select>
                                        <span class="text-input">DD</span>
                                    </div>
                                    <div class="form-date-item">
                                        <select id="birth_year" name="birth_year"></select>
                                        <span class="text-input">YYYY</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ssn" class="form-label">SSN</label>
                                <input type="text" name="ssn" id="ssn" />
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <h3>Connect Bank Account</h3>
            <fieldset>
                <h2>Connect Bank Account</h2>
                <p class="desc">Please enter your infomation and proceed to next step so we can build your
                    account</p>
                <div class="fieldset-content">
                    <div class="form-group">
                        <label for="find_bank" class="form-label">Find Your Bank</label>
                        <div class="form-find">
                            <input type="text" name="find_bank" id="find_bank" placeholder="Ex. Techcombank" />
                            <input type="submit" value="Search" class="submit">
                            <span class="form-icon"><i class="zmdi zmdi-search"></i></span>
                        </div>
                    </div>
                </div>
            </fieldset>

            <h3>Set Financial Goals</h3>
            <fieldset>
                <h2>Set Financial Goals</h2>
                <p class="desc">Set up your money limit to reach the future plan</p>
                <div class="fieldset-content">
                    <div class="donate-us">
                        <div class="price_slider ui-slider ui-slider-horizontal">
                            <div id="slider-margin"></div>
                            <p class="your-money">
                                Your money you can spend per month :
                                <span class="money" id="value-lower"></span>
                                <span class="money" id="value-upper"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
@endsection
