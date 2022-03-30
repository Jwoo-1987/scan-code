<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>test</title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
    <style>a:hover{text-decoration: none;}</style>
    <script src="{{ mix('/js/app.js') }}"></script>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}" />
    <script src="{{asset('vendor/layui/layui.js')}}"></script>
    

</head>
<body>
<table class="layui-hide" id="test"></table>
              
        
 
<script>
layui.use('table', function(){
  var table = layui.table;
  
  table.render({
    elem: '#test'
    ,url: '/scan/list'
    ,cols: [[
      ,{field:'code', title: '编号'}
      ,{field:'creat_time', title: '添加时间'}
      ,{field:'control', title: '操作'}
    ]]
  });
});
</script>
<button type="button" class="layui-btn layui-btn-fluid" id='add'>添加</button>
<text id="code"></text>
</body>
<script>
    layui.$('#add').on('click', function () {
        var code = Math.floor((Math.random() * 1000000) + 1);
        $.ajax({   
                url:'/scan/save',       
                method:'post',       
                data:{code},        
                dataType:'JSON',         
                success:function(res){       
                     if(res.code='0'){       
                        parent.layui.table.reload('test');
                        }                
                    else            
                        alert(res.msg);   
                    },              
                    error:function (data) {undefined
                        
                    }           
        }) ;            
    });
    $(function(){
        
        wx.config({
             debug: false,
             appId: "{{$str['appId']}}",
             timestamp:"{{$str['timestamp']}}",
             nonceStr:"{{$str['nonceStr']}}",
             signature:"{{$str['signature']}}",
             jsApiList : [ 'checkJsApi', 'scanQRCode' ],
        });
        wx.error(function(res) {
            alert("出错了：" + res.errMsg);
        });

        wx.ready(function() {
            wx.checkJsApi({
                jsApiList : ['scanQRCode'],
                success : function(res) {
                }
            });

            //点击按钮扫描二维码
            document.querySelector('#scanQRCode').onclick = function() {
                wx.scanQRCode({
                    needResult : 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                    scanType : ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                    success : function(res) {
                        alert(res.resultStr);//二维码信息
                        var result=res.resultStr.split(",");//条形码信息
                        $('#code').html(result[1]);
                    }
                });
            };

        });
    })

</script>
</html>
