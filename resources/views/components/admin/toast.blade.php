@if(session()->has('message'))


<div class="bs-toast toast fade bg-success show mx-2" style="position: absolute; right: 0;" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
    <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Success!</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        {{session('message')}}
    </div>
</div>


@endif