@extends('auth.auth_registration')
@section('content')

<main class="signup-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">

                    <p
                        style="margin:5% 3% 0 3%;color: #013C5E;font-size: 50px; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif">
                        Registrasi.</p>
                    <p class="text-secondary"
                        style="margin:1% 3% 0 3%;font-style: italic;font-size: 14px;font-family:  Arial, Helvetica, sans-serif">
                        Silakan mengisi form untuk mendaftarkan sebagai user di portal website <span
                            style="color: #4CAF50">Grading TBS
                            SKM</span>!
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
                        <form action="{{ route('auth_registration') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="exampleInputEmail1">Name</label>
                                <input type="text" placeholder="Masukkan nama user" id="name" class="form-control"
                                    name="name" required autofocus>
                                @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label for="exampleInputEmail1">Email</label>
                                <input type="text" placeholder="Masukkan email" id="email_address" class="form-control"
                                    name="email" required autofocus>
                                @if ($errors->has('email'))
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Password</label>
                                <input type="password" placeholder="Masukkan password" id="password"
                                    class="form-control" name="password" required autofocus="autofocus"
                                    style="text-are">
                                @if ($errors->has('password'))
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            <div class="mt-3 d-grid gap-2">
                                <button type="submit" class="btn btn-success mt-3">Submit</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection