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

</head>
<body>
<button id="scanQRCode">扫码</button>
<text id="code"></text>
</body>
<script>
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
            alert("出错了：" + res.errMsg);//这个地方的好处就是wx.config配置错误，会弹出窗口哪里错误，然后根据微信文档查询即可。
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
