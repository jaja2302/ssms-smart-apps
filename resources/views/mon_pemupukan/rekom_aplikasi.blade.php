@include('layout.header')
<style>
    #map {
        height: 500px;
    }

    .man-marker {
        /* color: white; */
        filter: invert(35%) sepia(63%) saturate(5614%) hue-rotate(2deg) brightness(102%) contrast(107%);
    }

    .label-bidang {
        font-size: 10pt;
        color: white;
        text-align: center;
        /* opacity: 0.6; */
    }

    .label-estate {
        font-size: 20pt;
        color: white;
        text-align: center;
        opacity: 0.4;

    }

    .content {
        font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
        font-size: 15px;
    }

    th,
    td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        /* width: 800px; */
        margin: 0 auto;
    }

    table.dataTable thead tr th {
        border: 1px solid black;
    }
</style>
<div class="content-wrapper ">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>

    <section class="content">
        <div class="container-fluid pb-2">
            <a href="{{ route('dash_pemupukan') }}"> <i class="nav-icon fa-solid fa-arrow-left "></i> Kembali</a>
            <div class="card mt-3">
                <div class="card-body">
                    <h3 class="mb-3">Rekomendasi dan Aplikasi ({{$sm}} - {{$rot}} - {{$est}} - {{$afd}})</h3>
                    <p class="mb-3">Tanggal : <i>{{$tgl}}</i></p>

                    <table id="tableDetail" class="table table-bordered text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th rowspan="2">Blok</th>
                                @foreach ($arrResult as $key => $value)
                                @foreach ($value as $key1 => $value1)
                                <th colspan="2" class="text-center">{{ $value1['nama'] }}</th>
                                @endforeach
                                @break
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($arrResult as $key => $value)
                                @foreach ($value as $key1 => $value1)
                                <th>Rekomendasi</th>
                                <th>Aplikasi</th>
                                @endforeach
                                @break
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($arrResult as $key => $value)
                            <tr>
                                <td>{{ $key }}</td>
                                @foreach ($value as $key1 => $value2)
                                <td>{{ $value2['rekom'] }}</td>
                                <td>{{ $value2['apl'] }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

</div>
@include('layout.footer')

{{-- <script src=" {{ asset('lottie/93121-no-data-preview.json') }}" type="text/javascript">
</script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.4/lottie.min.js" integrity="sha512-ilxj730331yM7NbrJAICVJcRmPFErDqQhXJcn+PLbkXdE031JJbcK87Wt4VbAK+YY6/67L+N8p7KdzGoaRjsTg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- jQuery -->
<script src="{{ asset('/public/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('/public/plugins/bootstrap/js/bootstrap.bundle.min.js') }}">
</script>
<!-- ChartJS -->
<script src="{{ asset('/public/plugins/chart.js/Chart.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('/public/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('/public/js/demo.js') }}"></script>

<script src="{{ asset('/public/js/loader.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#tableDetail').DataTable({
            scrollX: true,
        });
    });
</script>