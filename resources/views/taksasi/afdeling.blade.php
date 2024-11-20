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
                <form class="" action="{{ route('dash_afd') }}" method="get">
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
                                <i class="fas fa-chart-line pr-2"></i> Taksasi Afdeling <span id="selectTakEst"></span>
                            </div>

                            <div class="float-right ml-2">
                                <div class="list-inline">
                                    <select id="tak_est" class="form-control" style="width:180px">
                                        <option selected disabled>Pilih Estate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="float-right">
                                <div class="list-inline">
                                    {{csrf_field()}}
                                    <select id="tak_reg" class="form-control" style="width:180px">
                                        <option selected disabled>Pilih Regional</option>
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

                                    <div id="taksasiafdeling" style="height: 300px" {{--
                                        style="width: 100%; height: 300px;" --}}>
                                    </div>

                                </div>



                            </div>

                        </div>
                    </div>


                </div>
            </div>





            {{-- afdeling --}}
            <div class="row">

                <div class="col-md-12">

                    <div class="card">
                        <div class="card-header" style="background-color: #013C5E;color:white">
                            <div class=" card-title">
                                <i class="fas fa-chart-line pr-2"></i> Kebutuhan Pemanen Afdeling <span
                                    id="selectKebEst"></span>
                            </div>

                            <div class="float-right ml-2">
                                <div class="list-inline">
                                    <select id="keb_est" class="form-control" style="width:180px">
                                        <option selected disabled>Pilih Estate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="float-right">
                                <div class="list-inline">
                                    {{csrf_field()}}
                                    <select id="keb_reg" class="form-control" style="width:180px">
                                        <option selected disabled>Pilih Regional</option>
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

                                    <div id="pemanenafdeling" style="height: 300px" {{--
                                        style="width: 100%; height: 300px;" --}}>
                                    </div>

                                </div>



                            </div>
                        </div><!-- /.card-body -->
                    </div><!-- Curah Hujan -->


                </div>
            </div>
        </div>
    </section>

</div>
@include('layout.footer')

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
    google.charts.setOnLoadCallback(drawtaksasiafdeling);
    google.charts.setOnLoadCallback(drawkepemanenafdeling);

    $(document).ready(function(){
        
        //set default regional 1
        var defaultReg = 0;
        $("#tak_reg").val(defaultReg);
        var _token = $('input[name="_token"]').val();

        $.ajax({
        url:"{{ route('getNameEstate') }}",
        method:"POST",
        data:{ id_reg:defaultReg, _token:_token,},
        success:function(result)
        {
            $('#tak_est').html(result)
            $('#keb_est').html(result)
            $('#tak_est').val("RDE")
            $('#keb_est').val("RDE")
            getDataTakAfd(0, 'RDE')
            getDataKebAfd(0, 'RDE')
        }
        })

    
        
    });

    $('#tak_reg').change(function(){
    if($(this).val() != '')
    {
    var value = $(this).val();
    var _token = $('input[name="_token"]').val();

    $.ajax({
    url:"{{ route('getNameEstate') }}",
    method:"POST",
    data:{ id_reg:value, _token:_token,},
    success:function(result)
    {
        $('#tak_est').html(result)
        var select = document.getElementById('tak_est');
        var firstIndexList = select.options[select.selectedIndex].value;
        $('#tak_est').val(firstIndexList)
        getDataTakAfd(0, firstIndexList)
    }
    })
    }
    });  

    $('#keb_reg').change(function(){
    if($(this).val() != '')
    {
    var value = $(this).val();
    var _token = $('input[name="_token"]').val();

    $.ajax({
    url:"{{ route('getNameEstate') }}",
    method:"POST",
    data:{ id_reg:value, _token:_token,},
    success:function(result)
    {
        $('#keb_est').html(result)
        var select = document.getElementById('keb_est');
        var firstIndexList = select.options[select.selectedIndex].value;
        $('#keb_est').val(firstIndexList)
        getDataKebAfd(0, firstIndexList)
    }
    })
    }
    });  
    
    $('#tak_est').change(function(){
        valRegTak = document.getElementById("tak_reg").value
        getDataTakAfd(valRegTak, $('#tak_est'))
        
    });

    $('#keb_est').change(function(){
        valRegKeb = document.getElementById("keb_reg").value
        getDataKebAfd(valRegKeb, $('#keb_est'))
    });

    function getDataTakAfd(reg, est){
    var status = 0 // ketika ada klik id yang di fetch
    var value = ''
    try {
        value = est.val();      
    }
    catch(err) {
        var status = 1 // ketika tidak ada klik dan nilai RDE
    } 

    if(status == 1){
        value = est
    }

    document.querySelector(
              '#selectTakEst').textContent = value;
    var valReg = reg;

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
    url:"{{ route('getDataAfdeling') }}",
    method:"POST",
    data:{ id_reg:valReg,id_est:value, _token:_token, tgl:date, tak:1},
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

        
        drawtaksasiafdeling(arrResult)
    }
    })
    }

    function getDataKebAfd(reg, est){
        var status = 0 // ketika ada klik id yang di fetch
    var value = ''
    try {
        value = est.val();      
    }
    catch(err) {
        var status = 1 // ketika tidak ada klik dan nilai RDE
    } 

    if(status == 1){
        value = est
    }
    
    document.querySelector(
              '#selectKebEst').textContent = value;
    var valReg = reg;
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
    url:"{{ route('getDataAfdeling') }}",
    method:"POST",
    data:{  id_reg:valReg,id_est:value,_token:_token, tgl:date, tak:0},
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

        drawkepemanenafdeling(arrResult)
    }
    })
    }

    function drawtaksasiafdeling(chart_data) {

    var tak_afd = new google.visualization.DataTable();
    tak_afd.addColumn('string', 'Estate');
    tak_afd.addColumn('number', 'Taksasi Afdeling');
    tak_afd.addColumn({type:'string', role:'annotation'});
    // tak_afd.addColumn({type: 'string', role: 'style'});
    for(i = 0; i < chart_data.length; i++){
        tak_afd.addRow([chart_data[i][0], parseFloat(chart_data[i][1]),  parseFloat(chart_data[i][1]).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") +' Kg']);
    }

    // var view = new google.visualization.DataView(tak_afd);
    //   view.setColumns([0, 1,
    //                    { calc: "stringify",
    //                      sourceColumn: 1,
    //                      type: "string",
    //                      role: "annotation" },
    //                 ]);
        var options = {
        chartArea: {
        },
        theme: 'material',
        colors:['#4CAF50' ],
//         hAxis: {
//     textStyle:{color: '#FFFFFF'}
// },
        // hAxis: {title: 'Priority', titleTextStyle: {color: 'black',fontSize:'15',fontName:'"Arial"'}},
        //   title: 'Company Performance',
        annotations: {
     textStyle: {
         color: 'black',
         fontSize: 13,
     },
     alwaysOutside: true
},
          curveType: 'function',
          legend: { position: 'none' }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('taksasiafdeling'));

        chart.draw(tak_afd, options);
      }


    function drawkepemanenafdeling(chart_data) {
    var pemanen_afd = new google.visualization.DataTable();
    pemanen_afd.addColumn('string', 'Estate');
    pemanen_afd.addColumn('number', 'Kebutuhan Pemanen Afdeling');
    // pemanen_afd.addColumn({type: 'string', role: 'style'});
    pemanen_afd.addColumn({type:'string', role:'annotation'});
    for(i = 0; i < chart_data.length; i++){
        pemanen_afd.addRow([chart_data[i][0], parseFloat(chart_data[i][1]), parseFloat(chart_data[i][1]).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") +' Orang']);
    }

    var options = {
        chartArea: {
        },
        theme: 'material',
        colors:['#4CAF50' ],
//         hAxis: {
//     textStyle:{color: '#FFFFFF'}
// },
        // hAxis: {title: 'Priority', titleTextStyle: {color: 'black',fontSize:'15',fontName:'"Arial"'}},
        //   title: 'Company Performance',
        annotations: {
     textStyle: {
         color: 'black',
         fontSize: 13,
     },
     alwaysOutside: true
},
          curveType: 'function',
          legend: { position: 'none' }
        }

        var chart = new google.visualization.ColumnChart(document.getElementById('pemanenafdeling'));

        chart.draw(pemanen_afd, options);
      }

    
</script>