@include('layout.header')
<style>
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
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>

    <section class="content">
        <div class="container-fluid">
            <h3>Dashboard Pemupukan</h3>
            {{-- <div class="row">
                <div class="col-2">
                    Pilih Tanggal
                </div>
                <div class="col-2">
                    Pilih Estate
                </div>
                <div class="col-2">
                    Pilih Afdeling
                </div>

            </div> --}}
            <div class="row mb-3">

                <div class="col-2">
                    <input class="form-control" type="month" name="tgl" value="{{ date('Y-m') }}" id="inputDate">
                </div>

                <div class="col-2">
                    {{csrf_field()}}
                    <select id="estateList" class="form-control">
                        <option selected disabled>Pilih Estate</option>
                    </select>
                </div>

                <div class="col-2">
                    <select id="afdelingList" name="afdelingList" class="form-control" style="width:180px">
                        {{-- <option>Pilih Afdeling</option> --}}
                    </select>
                </div>

            </div>
            <div class="card">
                <div class="card-body">
                    <table id="example" class="table table-bordered text-center" cellspacing="0" width="100%">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2">Last Monitoring</th>
                                <th rowspan="2">Estate</th>
                                <th rowspan="2">Afd</th>
                                <th colspan="2">SM1</th>
                                <th colspan="2">SM2</th>
                                <th rowspan="2">Achievement</th>
                                <th rowspan="2">Varian</th>
                                <th rowspan="2">Annual Achievement (%)</th>
                            </tr>
                            <tr>
                                <th>Rotasi 1</th>
                                <th>Rotasi 2</th>
                                <th>Rotasi 3</th>
                                <th>Rotasi 4</th>
                            </tr>
                        </thead>
                        <tbody id="valBody">

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
    function dateFormat(inputDate, format) {
        //parse the input date
        const date = new Date(inputDate);

        //extract the parts of the date
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        //replace the month
        format = format.replace("MM", month.toString().padStart(2, "0"));

        //replace the year
        if (format.indexOf("yyyy") > -1) {
            format = format.replace("yyyy", year.toString());
        } else if (format.indexOf("yy") > -1) {
            format = format.replace("yy", year.toString().substr(2, 2));
        }

        //replace the day
        format = format.replace("dd", day.toString().padStart(2, "0"));

        return format;
    }

    date = ''

    const params = new URLSearchParams(window.location.search)
    var paramArr = [];
    for (const param of params) {
        paramArr = param
    }

    if (paramArr.length > 0) {
        date = paramArr[1]
    } else {
        date = new Date().toISOString().slice(0, 10)
    }

    $(document).ready(function() {
        $('#example').DataTable();
        $('#rekomData').DataTable({
            "bLengthChange": false,
            "aaSorting": [],
            scrollY: true,
            scrollX: true,
            scrollCollapse: true,
            fixedColumns: true
        });
        $('#aplData').DataTable({
            "bLengthChange": false,
            "aaSorting": []
        });

        document.getElementById("estateList").style.display = "none";
        document.getElementById("afdelingList").style.display = "none";
    })

    $('#inputDate').ready(function() {
        date = $('#inputDate').val();
        var _token = $('input[name="_token"]').val();

        $.ajax({
            url: "{{ route('getListEstateTerpupuk') }}",
            method: "POST",
            data: {
                date: date,
                _token: _token
            },
            success: function(result) {
                if (result != '') {
                    document.getElementById("estateList").style.display = "block";
                    document.getElementById("afdelingList").style.display = "block";
                    def = '<option disabled selected>Pilih Estate</option>';
                    merge = def + result;
                    $('#estateList').html(merge)
                    var select = document.getElementById('estateList');

                    getListAfd(0, date)

                    setTimeout(function() {
                        lastDataPemupukan(date)
                    });
                } else {
                    document.getElementById("estateList").style.display = "none";
                    document.getElementById("afdelingList").style.display = "none";
                    setTimeout(function() {
                        lastDataPemupukan(date)
                    });
                }

            }
        })
    });

    $('#inputDate').change(function() {
        date = $(this).val();
        var _token = $('input[name="_token"]').val();

        $.ajax({
            url: "{{ route('getListEstateTerpupuk') }}",
            method: "POST",
            data: {
                date: date,
                _token: _token
            },
            success: function(result) {
                if (result != '') {
                    document.getElementById("estateList").style.display = "block";
                    document.getElementById("afdelingList").style.display = "block";
                    def = '<option disabled selected>Pilih Estate</option>';
                    merge = def + result;
                    $('#estateList').html(merge)
                    var select = document.getElementById('estateList');

                    getListAfd(0, date)

                    setTimeout(function() {
                        lastDataPemupukan(date)
                    });
                } else {
                    document.getElementById("estateList").style.display = "none";
                    document.getElementById("afdelingList").style.display = "none";
                    setTimeout(function() {
                        lastDataPemupukan(date)
                    });
                }

            }
        })
    });

    $('#estateList').change(function() {

        var value = $(this).val();
        var _token = $('input[name="_token"]').val();

        getListAfd(value, date)

        setTimeout(function() {
            var selectIndex = $("#afdelingList").val($("#afdelingList option:eq(1)").val());
            var defaultAfd = $("#afdelingList option:eq(1)").text();

            getDataPemupukan(defaultAfd, value, date)
        }, 500);
    });

    $('#afdelingList').change(function() {
        afd = $(this).val();
        id_est = document.getElementById("estateList").value
        $("#valBody").empty();

        getDataPemupukan(afd, id_est, date)
    });

    function lastDataPemupukan(date) {

        var _token = $('input[name="_token"]').val();

        $.ajax({
            url: "{{ route('lastDataPemupukan') }}",
            method: "POST",
            data: {
                _token: _token,
                date: date
            },
            success: function(result) {
                var result = JSON.parse(result);

                $('#example').DataTable({
                    "scrollX": true,
                    "aaData": result,
                    "order": [
                        [0, 'desc']
                    ],
                    "columns": [{
                            "data": "tanggal"
                        },
                        {
                            "data": "estate"
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (type === 'display') {
                                    var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                    data = '<a href="detail_pemupukan/' + row.estate + '/' + row.afdeling + '/' + formattedDate + '">' + data + '</a>';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-01' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-02' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-03') {
                                    if (type === 'display') {
                                        var sm = 'SM1'
                                        var rot = 'R1'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-04' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-05' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-06') {
                                    if (type === 'display') {
                                        var sm = 'SM1'
                                        var rot = 'R2'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-07' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-08' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-09') {
                                    if (type === 'display') {
                                        var sm = 'SM2'
                                        var rot = 'R3'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-10' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-11' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-12') {
                                    if (type === 'display') {
                                        var sm = 'SM2'
                                        var rot = 'R4'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        }
                    ],
                    "bDestroy": true
                })
            }
        })
    }

    function getListAfd(id_est, date) {

        var value = id_est;
        var _token = $('input[name="_token"]').val();

        $.ajax({
            url: "{{ route('getNameAfdeling') }}",
            method: "POST",
            data: {
                id_estate: value,
                _token: _token,
                date: date
            },
            success: function(result) {
                $('#afdelingList').html(result)
            }
        })
    }

    function getDataPemupukan(afd, estate, date) {

        var value = afd;
        var _token = $('input[name="_token"]').val();

        $.ajax({
            url: "{{ route('getDataPemupukan') }}",
            method: "POST",
            data: {
                afd: value,
                _token: _token,
                date: date,
                id_est: estate
            },
            success: function(result) {

                var result = JSON.parse(result);

                $('#example').DataTable({
                    "scrollX": true,
                    "aaData": result,
                    "order": [
                        [0, 'desc']
                    ],
                    "columns": [{
                            "data": "tanggal"
                        },
                        {
                            "data": "estate"
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (type === 'display') {
                                    var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                    data = '<a href="detail_pemupukan/' + row.estate + '/' + row.afdeling + '/' + formattedDate + '">' + data + '</a>';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-01' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-02' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-03') {
                                    if (type === 'display') {
                                        var sm = 'SM1'
                                        var rot = 'R1'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-04' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-05' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-06') {
                                    if (type === 'display') {
                                        var sm = 'SM1'
                                        var rot = 'R2'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-07' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-08' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-09') {
                                    if (type === 'display') {
                                        var sm = 'SM2'
                                        var rot = 'R3'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": "afdeling",
                            "render": function(data, type, row, meta) {
                                if (dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-10' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-11' || dateFormat(row.waktu_upload, 'yyyy-MM') == new Date().toJSON().slice(0, 4) + '-12') {
                                    if (type === 'display') {
                                        var sm = 'SM2'
                                        var rot = 'R4'
                                        var formattedDate = dateFormat(row.waktu_upload, 'dd-MM-yyyy')
                                        data = '<a href="rekom_aplikasi/' + row.estate + '/' + row.afdeling + '/' + rot + '/' + sm + '/' + formattedDate + '" target="_blank">DETAIL</i></a>';
                                    }
                                } else {
                                    data = '-';
                                }

                                return data;
                            }
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        },
                        {
                            "data": null,
                            "defaultContent": "-"
                        }
                    ],
                    "bDestroy": true
                })
            }
        })
    }
</script>