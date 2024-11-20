<footer class="main-footer">
    <strong>Copyright © 2021-2026 <a href="https://srs-ssms.com">SRS-SSMS.COM</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.0.5
    </div>
</footer>

{{-- <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.11.5/datatables.min.js"></script> --}}

<script src="{{ asset('js/js_tabel/jquery-3.5.1.js') }}"></script>
<script src="{{ asset('js/js_tabel/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/buttons.flash.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/jszip.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/vfs_fonts.js') }}"></script>
<script src="{{ asset('js/js_tabel/buttons.html5.min.js') }}"></script>
<script src="{{ asset('js/js_tabel/buttons.print.min.js') }}"></script>

<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
</script>

<script>
    $(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    })
</script>