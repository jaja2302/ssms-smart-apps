@include('layout.header')
<style>
    @media only screen and (min-width: 992px) {
        .piechart_div {
            height: 590px;
        }

    }



    @media only screen and (min-width: 1366px) {

        .piechart_div {
            height: 800px;
        }
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="col-12 col-lg-3">
                Pilih Tanggal
                <form class="" action="{{ route('dash_est') }}" method="get">
                    <input class="form-control" type="date" name="tgl" onchange="this.form.submit()">
                </form>
            </div>
            <br>
            <div class="row">

                <div class="col-md-12">
                    <!-- Curah Hujan -->
                    <div class="card">
                        <div class="card-header" style="background-color: #013C5E;color:white">
                            <div class=" card-title">
                                <i class="fas fa-chart-line pr-2"></i> Taksasi Estate
                            </div>

                            <div class="float-right">
                                <div class="list-inline">
                                    {{ csrf_field() }}
                                    <select id="tak_est_reg" class="form-control" data-dependent='estate'>

                                        @foreach($reg as $key => $value)
                                        <option value="{{$key}}" {{ $key==0 ? 'selected' : '' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div id="taksasiestate" style="height: 300px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">

                    <div class="card">
                        <div class="card-header" style="background-color: #013C5E;color:white">
                            <div class=" card-title">
                                <i class="fas fa-chart-line pr-2"></i> Taksasi Estate dalam 15 hari terakhir ()
                            </div>

                            <div class="float-right">
                                <div class="list-inline">
                                    {{ csrf_field() }}
                                    <select id="halfMonth" class="form-control">
                                        @foreach($reg as $key => $value)
                                        <option value="{{$key}}" {{ $key==0 ? 'selected' : '' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div id="takHalfMonth" style="height: 300px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <!-- Curah Hujan -->
                    <div class="card">
                        <div class="card-header" style="background-color: #013C5E;color:white">
                            <div class=" card-title">
                                <i class="fas fa-chart-line pr-2"></i> Kebutuhan Pemanen Estate
                            </div>
                            <div class="float-right">
                                <div class="list-inline">
                                    <select id="kab_pem_est" class="form-control">
                                        @foreach($reg as $key => $value)
                                        <option value="{{$key}}" {{ $key==0 ? 'selected' : '' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-12">

                                    <div id="kebutuhanestate" style="height: 400px" {{--
                                        style="width: 100%; height: 300px;" --}}>
                                    </div>

                                </div>



                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- Curah Hujan -->


                </div>
            </div>

    </section>

</div>
@include('layout.footer')

{{-- <script src="{{ asset('lottie/93121-no-data-preview.json') }}" type="text/javascript"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.4/lottie.min.js"
    integrity="sha512-ilxj730331yM7NbrJAICVJcRmPFErDqQhXJcn+PLbkXdE031JJbcK87Wt4VbAK+YY6/67L+N8p7KdzGoaRjsTg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- jQuery -->
<script src="{{ asset('/public/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('/public/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('/public/plugins/chart.js/Chart.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('/public/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('/public/js/demo.js') }}"></script>

<script src="{{ asset('/public/js/loader.js') }}"></script>



<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawtaksasiestate);
    google.charts.setOnLoadCallback(drawkebutuhanestate);
    google.charts.setOnLoadCallback(drawtakest15days);

    $(document).ready(function(){
        // getDataTakEst($('#tak_est_reg'))
        var defaultReg = 0;
        $("#tak_est_reg").val(defaultReg);
        $("#kab_pem_est").val(defaultReg);
        getDataTakEst(0)
        getDataKebutuhanEst(0)
    });

    $('#tak_est_reg').change(function(){
    if($(this).val() != '')
    {
        getDataTakEst($('#tak_est_reg'))
    }
    });

    $('#kab_pem_est').change(function(){
    if($(this).val() != '')
    {
        getDataKebutuhanEst($('#kab_pem_est'))
    }
    });

    $('#halfMonth').change(function(){
    if($(this).val() != '')
    {
        getDataTakEst15days($('#halfMonth'))
    }
    });

    function getDataTakEst(reg){
     var status = 0 // ketika ada klik id yang di fetch
    var value = ''
    try {
        value = reg.val();      
    }
    catch(err) {
        var status = 1 // ketika tidak ada klik dan nilai RDE
    } 

    if(status == 1){
        value = reg
    }
    var _token = $('input[name="_token"]').val();
    const params = new URLSearchParams(window.location.search)
    var paramArr = [];
    for (const param of  params) {
        paramArr = param
    }

    if(paramArr.length > 0){
        date = paramArr[1]
    }else{
        date = new Date().toISOString().slice(0, 10)
    }

    $.ajax({
    url:"{{ route('getLoadRegional') }}",
    method:"POST",
    data:{ id_reg:value, _token:_token, tgl:date, tak:1},
    success:function(result)
    {
        //split estate dan value taksasi
        sliceResult = result.slice(1, -1);
        const arrSlice = sliceResult.split(",");
        const arrResult = []
        for (let index = 0; index < arrSlice.length; index++) {
            const splitted = arrSlice[index].split(":")
            var estate = splitted[0].slice(1, -1);
            var valEst = splitted[1]
            arrResult.push([estate,valEst])
        }

        drawtaksasiestate(arrResult)
    }
    })
    }

    function getDataKebutuhanEst(reg){
    var status = 0 // ketika ada klik id yang di fetch
    var value = ''
    try {
        value = reg.val();      
    }
    catch(err) {
        var status = 1 // ketika tidak ada klik dan nilai RDE
    } 

    if(status == 1){
        value = reg
    }
    var _token = $('input[name="_token"]').val();
    const params = new URLSearchParams(window.location.search)
    var paramArr = [];
    for (const param of  params) {
        paramArr = param
    }

    if(paramArr.length > 0){
        date = paramArr[1]
    }else{
        date = new Date().toISOString().slice(0, 10)
    }

    $.ajax({
    url:"{{ route('getLoadRegional') }}",
    method:"POST",
    data:{ id_reg:value, _token:_token, tgl:date, tak:0},
    success:function(result)
    {
        //split estate dan value taksasi
        sliceResult = result.slice(1, -1);
        const arrSlice = sliceResult.split(",");
        const arrResult = []
        for (let index = 0; index < arrSlice.length; index++) {
            const splitted = arrSlice[index].split(":")
            var estate = splitted[0].slice(1, -1);
            var valEst = splitted[1]
            arrResult.push([estate,valEst])
        }
        drawkebutuhanestate(arrResult)
    }
    })
    }

    function getDataTakEst15days(reg){
    var status = 0 // ketika ada klik id yang di fetch
    var value = ''
    try {
        value = reg.val();      
    }
    catch(err) {
        var status = 1 // ketika tidak ada klik dan nilai RDE
    } 

    if(status == 1){
        value = reg
    }

    var _token = $('input[name="_token"]').val();
    const params = new URLSearchParams(window.location.search)
    var paramArr = [];
    for (const param of  params) {
        paramArr = param
    }

    if(paramArr.length > 0){
        date = paramArr[1]
    }else{
        date = new Date().toISOString().slice(0, 10)
    }


    // console.log(value)
    $.ajax({
    url:"{{ route('getDataTakEst15Days') }}",
    method:"POST",
    data:{ id_reg:value, _token:_token, tgl:date, tak:0},
    success:function(result)
    {
        $('#halfMonth').html(result)
        // console.log(result)
    }
    })
    }

    function drawtakest15days(chart_data) {
      
      var tak_est = new google.visualization.DataTable();
      tak_est.addColumn('string', 'Estate');
      tak_est.addColumn('number', 'Taksasi Estate');
  
      for(i = 0; i < chart_data.length; i++){
          tak_est.addRow([chart_data[i][0], parseFloat(chart_data[i][1])]);
      }
  
          var options = {
          chartArea: {},
          theme: 'material',
          // colors:[ ,'#FF9800','#4CAF50',  '#4CAF50','#4CAF50' ,'#4CAF50' ],
          // hAxis: {title: 'Priority', titleTextStyle: {color: 'black',fontSize:'15',fontName:'"Arial"'}},
          //   title: 'Company Performance',
            curveType: 'function',
            legend: { position: 'none' }
          };
  
          var chart = new google.visualization.ColumnChart(document.getElementById('takHalfMonth'));
  
          chart.draw(tak_est, options);
        }

    function drawtaksasiestate(chart_data) {
      
    var tak_est = new google.visualization.DataTable();
    tak_est.addColumn('string', 'Estate');
    tak_est.addColumn('number', 'Taksasi Estate');

    for(i = 0; i < chart_data.length; i++){
        tak_est.addRow([chart_data[i][0], parseFloat(chart_data[i][1])]);
    }

        var options = {
        chartArea: {},
        theme: 'material',
        // colors:[ ,'#FF9800','#4CAF50',  '#4CAF50','#4CAF50' ,'#4CAF50' ],
        // hAxis: {title: 'Priority', titleTextStyle: {color: 'black',fontSize:'15',fontName:'"Arial"'}},
        //   title: 'Company Performance',
          curveType: 'function',
          legend: { position: 'none' }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('taksasiestate'));

        chart.draw(tak_est, options);
      }

   

    function drawkebutuhanestate(chart_data) {
        // console.log(chart_data)
        var keb_pemanen_est = new google.visualization.DataTable();
    keb_pemanen_est.addColumn('string', 'Estate');
    keb_pemanen_est.addColumn('number', 'Kebutuhan Pemanen Estate');
    // keb_pemanen_est.addColumn({type: 'string', role: 'style'});
    for(i = 0; i < chart_data.length; i++){
        keb_pemanen_est.addRow([chart_data[i][0], parseFloat(chart_data[i][1])]);
    }

        var options = {
        chartArea: {},
        theme: 'material',
        // colors:[ ,'#FF9800','#4CAF50',  '#4CAF50','#4CAF50' ,'#4CAF50' ],
        // hAxis: {title: 'Priority', titleTextStyle: {color: 'black',fontSize:'15',fontName:'"Arial"'}},
        //   title: 'Company Performance',
          curveType: 'function',
          legend: { position: 'none' }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('kebutuhanestate'));

        chart.draw(keb_pemanen_est, options);
      }

    
</script>