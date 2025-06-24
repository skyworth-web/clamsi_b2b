<!-- Navbar -->
<?php
use App\Models\Store;
$store_details = fetchDetails('stores', '', '*');
$store_details = $store_details;
// Check if a store is selected in the dropdown, if not, set the default store's ID in the session
$default_store_id = '';
$stores = Store::where('is_default_store', 1)
    ->where('status', 1)
    ->get();
if ($stores->isNotEmpty()) {
    $default_store_id = $stores[0]->id;
    $default_store_name = $stores[0]->name;
    $default_store_image = $stores[0]->image;
}else{
    $default_store_id = '';
    $default_store_name = '';
    $default_store_image = '';
}

if (session('store_id') !== null && !empty(session('store_id'))) {
    $store_id = session('store_id');
} else {
    $store_id = $default_store_id;
    session(['store_id' => $default_store_id]);
    session(['store_name' => $default_store_name]);
    session(['store_image' => $default_store_image]);
}

$store_name = session('store_name') !== null && !empty(session('store_name')) ? session('store_name') : '';
?>

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
    data-scroll="false">
    <div id="app_url" data-app-url="{{ config('app.url') }}"></div>
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-white active" aria-current="page">Dashboard</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Dashboard</h6>
        </nav>

        <div class="collapse justify-content-end d-flex navbar-collapse mx-6" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" placeholder="Type here...">
                </div>
            </div>

            <ul class="navbar-nav justify-content-end d-flex">
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user cursor-pointer"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a class="dropdown-item text-dark"
                                href="/admin/account/{{ auth()->user()->id }}"> Profile</a>
                        </li>
                        <li>
                            <a class="dropdown-item text-dark" href="{{ route('admin.logout') }}">Logout</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
                <li class="nav-item dropdown pe-2 d-flex align-items-center mx-3">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <img src="" class="avatar avatar-sm me-3">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">New message</span> from Laur
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i> 13 minutes ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav flex-row align-items-center" >
                <li class="nav-item navbar-dropdown dropdown-store dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow text-white" href="javascript:void(0);"
                        data-bs-toggle="dropdown">
                        <i class="bx bx-map"></i>
                        <span class="ms-2 text-white">{{ $store_name ?: $default_store_name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" data-bs-popper="none" data-bs-placement="bottom-start"
                        id="store-dropdown">
                        <?php
                        $stores = Store::where('is_default_store', 1)
                            ->where('status', 1)
                            ->get();

                        if ($stores->isEmpty()) {
                            ?>
                        <li class="dropdown-item disabled d-flex justify-content-center">
                            <div class="col-6 text-center">No Stores found.</div>
                        </li>
                        <div class="d-flex justify-content-center">
                            <i class='bx bxs-plus-circle'></i><a href="/admin/store" target="_blank">add a store</a>
                        </div>
                        <?php } else {
                            foreach ($store_details as $store) {
                                echo '<li  class="dropdown-item" data-store-id="' . $store->id . '" data-store-name="' . $store->name . '" data-store-image="' . getimageurl($store->image)  . '">';
                                echo '<div class="d-flex align-items-center">';
                                echo '<div >';
                                echo '<img src="' . getMediaImageUrl($store->image) . '" class="table-image">';
                                echo '</div>';
                                echo '<span class="fw-semibold d-block p-2">' . $store->name . '</span>';
                                echo '</div>';
                                echo '</li>';
                            }
                        }?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
