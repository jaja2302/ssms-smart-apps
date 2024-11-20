@extends('auth.auth_login')
@section('content')

<div class="row justify-content-center">
    <div class="col-11 col-md-7 col-lg-5">
        <div class="card mb-5 p-4" style="border-radius: 10px">
            <div class="text-center mt-2">
                <img src="{{ asset('img/logo-SSS.png') }}" style="height: 100%;width:50%">
            </div>
            {{-- <p class="text-center"
                style="margin:0 3% 0 3%;color: #013C5E;font-size: 50px; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif">
                Log in.</p> --}}
            <p class="text-secondary text-center"
                style="margin:1% 3% 0 3%;font-style: normal;font-size: 14px;font-family:  Arial, Helvetica, sans-serif;font-weight: 600">
                Silakan masukkan Email dan Password yang ada miliki untuk mengakses portal <span
                    style="color: #4CAF50">Dashboard Taksasi</span>!
            </p>
            @error('msg')
            <div id="boxAlert" style="margin: 3% 3% -3% 3%">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong> {{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @enderror
            <div class="card-body" style="font-family: Arial, Helvetica, sans-serif">
                <form method="POST" action="{{ route('auth_login') }}">
                    @csrf
                    <div class="form-group mb-3">
                        {{-- <label for="exampleInputEmail1" class="mb-3">Username</label> --}}
                        <input type="text" placeholder="Masukkan email" id="email" class="form-control" name="email"
                            required autofocus>
                    </div>
                    <div class="form-group mb-3">
                        {{-- <label for="exampleInputEmail1" class="mb-3">Password</label> --}}
                        <input type="password" placeholder="Masukkan password" id="password" class="form-control"
                            name="password" required>
                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        <button type="submit" class="btn btn-success mt-3 " style="background-color: #013C5E"> <span
                                class="font-weight-bold"> SUBMIT</button>
                    </div>

                    <div class="text-center mt-4">
                        <img src="{{ asset('img/logo-srs.png') }}" style="height: 100%;width:26%">
                    </div>
                    {{--
                    <div class="font-italic text-muted">
                    </div> --}}
                </form>
            </div>


        </div>
    </div>


</div>


<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('js/demo.js') }}"></script>

<script src="{{ asset('js/loader.js') }}"></script>

<script>
    $('#boxAlert').click(function() {
        $('#boxAlert').attr('hidden', true);
    })
</script>
@endsection