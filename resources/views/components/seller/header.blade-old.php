 <!-- Navbar -->
 <?php

    use App\Models\Seller;
    use App\Models\Store;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    $user = Auth::user();
    $user_image = !empty($user->image) && file_exists(public_path(config('constants.USER_IMG_PATH') . $user->image)) ?
        getImageUrl($user->image, "", "", 'image', 'USER_IMG_PATH') :
        getImageUrl('no-user-img.jpeg', "", "", "image", 'NO_USER_IMAGE');
    $seller_detail = fetchDetails('seller_data', ['user_id' => $user->id], 'store_ids')[0];
    $store_ids = explode(',', $seller_detail->store_ids);
    $store_details = DB::table('stores')->whereIn('id', $store_ids)->get();



    if (session('store_id') !== null && !empty(session('store_id'))) {
        $store_id = session('store_id');
    } else {
        $store_id = $store_details[0]->id;
        session(['store_id' => $store_details[0]->id]);
        session(['store_name' => $store_details[0]->name]);
        session(['store_image' => $store_details[0]->image]);
    }


    $store_name = session('store_name') !== null && !empty(session('store_name')) ? session('store_name') : '';
    $store_image = session('store_image') !== null && !empty(session('store_image')) ? session('store_image') : '';

    ?>
 <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl ps-12" id="navbarBlur" data-scroll="false">
     <div id="app_url" data-app-url="{{ config('app.url') }}"></div>
     <div class="container-fluid py-1 px-3">
         <div class="collapse mt-2 navbar-collapse justify-content-end" id="navbar">
             <ul class="navbar-nav col">
                 <li class="nav-item dropdown pe-2 d-flex align-items-center dropdown-store">
                     <a class="nav-link  dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                         <img src="{{$store_image ? getImageUrl($store_image) : getImageUrl($store_details[0]->image) }}" class="avatar rounded-circle avatar-sm">
                         <span class="ms-2 ">{{ $store_name ?: $store_details[0]->name }}</span>
                     </a>
                     <div class="moduleDropDown">
                         <ul class="dropdown-menu " data-bs-popper="none" data-bs-placement="bottom-start" id="store-dropdown">
                             <div class="row ms-2">
                                 <p class="col-12 text-bold">Select Module</p>
                             </div>

                             <div class="d-flex flex-row">
                                 <?php
                                    if ($store_details->isEmpty()) {
                                    ?>
                                     <li class="dropdown-item disabled d-flex justify-content-center">
                                         <div class="col-6 text-center">No Stores found.</div>
                                     </li>

                                 <?php } else {
                                        foreach ($store_details as $store) {
                                            echo '<li  class="dropdown-item" data-store-id="' . $store->id . '" data-store-name="' . $store->name . '" data-store-image="' . getimageurl($store->image)  . '">';
                                            echo '<div class="align-items-center">';
                                            echo '<div>';
                                            echo '<img src="' . getimageurl($store->image) . '"  class="">';
                                            echo '</div>';
                                            echo '<span class="fw-semibold d-block p-2 text-center">' . $store->name . '</span>';
                                            echo '</div>';
                                            echo '</li>';
                                        }
                                    } ?>
                             </div>
                         </ul>
                     </div>
                 </li>
             </ul>
             <li class="nav-item dropdown pe-2 d-flex align-items-center mx-3 notifiationDropDown">
                 <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                     <i class="far fa-bell cursor-pointer ms-2"></i>
                 </a>
                 <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                     <li class="mb-2">
                         <a class="dropdown-item border-radius-md" href="javascript:;">
                             <div class="d-flex py-1">
                                 <div class="my-auto">
                                     <img src="" class="avatar avatar-sm  me-3 ">
                                 </div>
                                 <div class="d-flex flex-column justify-content-center">
                                     <h6 class="text-sm font-weight-normal mb-1">
                                         <span class="font-weight-bold">New message</span> from Laur
                                     </h6>
                                     <p class="text-xs text-secondary mb-0">
                                         <i class="fa fa-clock me-1"></i>
                                         13 minutes ago
                                     </p>
                                 </div>
                             </div>
                         </a>
                     </li>
                 </ul>
             </li>
             <div id="profileDropDown" class="input-group-text">
                 <li class="nav-item dropdown pe-2 d-flex align-items-center">
                     <a href="javascript:;" class="nav-link text-white p-0 nav-link-text ms-1" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                         <img class="avatar rounded-circle avatar-sm" src="{{$user_image}}"> {{$user->username}} <i class="fas fa-angle-down"></i>
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                         <li>
                             <a class="dropdown-item text-dark" href="/admin/account/{{ auth()->user()->id }}">Profile</a>
                         </li>
                         <li>
                             <a class="dropdown-item text-dark" href="{{ route('admin.logout') }}">Logout</a>
                         </li>
                     </ul>
                 </li>
             </div>
         </div>
     </div>
 </nav>
 <?php $store_id = getStoreId();

    $store_details = fetchDetails('stores', ['id' => $store_id], ['primary_color', 'secondary_color', 'hover_color', 'active_color']);
    $primary_colour = (isset($store_details[0]->primary_color) && !empty($store_details[0]->primary_color)) ?  $store_details[0]->primary_color : '#B52046';
    $secondary_color = (isset($store_details[0]->secondary_color) && !empty($store_details[0]->secondary_color)) ?  $store_details[0]->secondary_color : '#201A1A';
    $hover_color = (isset($store_details[0]->hover_color) && !empty($store_details[0]->hover_color)) ?  $store_details[0]->hover_color : '#911A38';
    $active_color = (isset($store_details[0]->active_color) && !empty($store_details[0]->active_color)) ?  $store_details[0]->active_color : '#6D132A';

    ?>

 <style>
     * {
         --primary-color: <?= $primary_colour ?>;
         --secondary-color: <?= $secondary_color ?>;
         --hover-color: <?= $hover_color ?>;
         --active-color: <?= $active_color ?>;
     }
 </style>
 <!-- End Navbar -->