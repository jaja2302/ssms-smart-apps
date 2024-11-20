@extends('pupuk.template')

@section('content')
<div class="row mt-5 mb-5">
    <div class="col-lg-12 margin-tb">
        <div class="float-left">
            <h2>Database Pupuk</h2>
        </div>
        <div class="float-right">
            <a class="btn btn-success" href="{{ route('pupuk.create') }}"> Tambah Jenis Pupuk</a>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

{{-- <h3>List Daftar Pupuk</h3> --}}
<table class="table table-bordered" id="myTable">
    <tr>
        <th width="20px" class="text-center">No</th>
        <th width="280px" class="text-center">Nama Pupuk</th>
        <th width="280px" class="text-center">Action</th>
    </tr>
    @foreach ($data as $pupuk)
    <tr>
        <td class="text-center">{{ $loop->iteration }}</td>
        <td>{{ $pupuk->nama }}</td>
        <td class="text-center">
            <form action="{{ route('pupuk.destroy',$pupuk->id) }}" method="POST">

                {{-- <a class="btn btn-info btn-sm" href="{{ route('pupuk.show',$pupuk->id) }}">Show</a> --}}

                <a class="btn btn-primary btn-sm" href="{{ route('pupuk.edit',$pupuk->id) }}">Edit</a>

                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach

</table>


@endsection

<script src="//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>