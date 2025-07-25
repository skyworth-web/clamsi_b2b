 <!--   Core JS Files   -->
 <script src="{{ asset('/assets/admin/js/jquery.min.js') }}"></script>
 <script src="{{ asset('/assets/admin/js/jquery.js') }}"></script>
 <script src="{{ asset('/assets/js/core/popper.min.js') }}"></script>
 <script src="{{ asset('/assets/js/core/bootstrap.js') }}"></script>
 <script src="{{ asset('/assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
 <script src="{{ asset('/assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
 <script src="{{ asset('/assets/js/plugins/apexcharts.js') }}"></script>
 <script src="{{ asset('assets/admin/js/iziToast.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/dropzone.js') }}"></script>
 <script src="{{ asset('assets/admin/js/bootstrap-table.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/tagify.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jstree.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery.blockUI.js') }}"></script>
 <script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/tinymce.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/sortable.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery-slimScroll.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery-sortable.js') }}"></script>
 <script src="{{ asset('assets/admin/js/lightbox.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery.repeater.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery.repeater.js') }}"></script>
 <script src="{{ asset('js/main.js') }}"></script>
 <script src="{{ asset('js/sidebarMenu.js') }}"></script>

 <!-- !-- start :: include FilePond library -->
 <script src="{{ asset('assets/admin/js/filepond.js') }}"></script>
 <script src="/assets/filepond/dist/filepond.min.js"></script>
 <script src="/assets/filepond/dist/filepond-plugin-image-preview.min.js"></script>
 <script src="/assets/filepond/dist/filepond-plugin-pdf-preview.min.js"></script>
 <script src="/assets/filepond/dist/filepond-plugin-file-validate-size.js"></script>
 <script src="/assets/filepond/dist/filepond-plugin-file-validate-type.js"></script>
 <script src="/assets/filepond/dist/filepond-plugin-image-validate-size.js"></script>
 <script src="/assets/filepond/dist/filepond.jquery.js"></script>


 <script src="{{ asset('/assets/admin/js/tableExport.min.js') }}"></script>
 <script src="{{ asset('/assets/admin/js/bootstrap-table-export.min.js') }}"></script>

 <!-- =================== js files for datepicker ========================================= -->


 <script src="{{ asset('/assets/admin/js/moment.min.js') }}"></script>
 <script src="{{ asset('/assets/admin/js/daterangepicker.js') }}"></script>

 <!-- =================== js files for stepper ========================================= -->
 <script src="{{ asset('assets/admin/js/jquery.validate.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/additional-methods.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/jquery.steps.min.js') }}"></script>

 <script src="{{ asset('assets/admin/js/nouislider.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/wNumb.js') }}"></script>
 <script src="{{ asset('assets/admin/js/stepper.js') }}"></script>

 <!-- Rating library -->

 <script src="{{ asset('/assets/js/plugins/jquery.rateyo.min.js') }}"></script>

 {{-- data sortable and dragable js --}}

 <script src="{{ asset('assets/admin/js/TweenMax.min.js') }}"></script>
 <script src="{{ asset('assets/admin/js/draggable.min.js') }}"></script>

 <script src="{{ asset('assets/admin/custom/pos.js') }}?v={{ \Illuminate\Support\Str::random(10) }}"></script>
 {{-- <script src="{{ asset('assets/admin/custom/custom.js') }}"></script> --}}
 <script src="{{ asset('assets/admin/custom/custom.js') }}?v={{ \Illuminate\Support\Str::random(10) }}"></script>

 <script>
     @if (Session::has('message'))
         toastr.options = {
             " closeButton": true,
             "progressBar": true
         }
         toastr.success("{{ session('message') }}");
     @endif
     @if (Session::has('error'))
         toastr.options = {
             "closeButton": true,
             "progressBar": true
         }
         toastr.error("{{ session('error') }}");
     @endif
     @if (Session::has('info'))
         toastr.options = {
             "closeButton": true,
             "progressBar": true
         }
         toastr.info("{{ session('info') }}");
     @endif
     @if (Session::has('warning'))
         toastr.options = {
             "closeButton": true,
             "progressBar": true
         }
         toastr.warning("{{ session('warning') }}");
     @endif
 </script>
 </body>

 </html>
