<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
    <link
      rel="shortcut icon"
      href="https://static-index-4gtuqm3bfa95c963-1304825656.tcloudbaseapp.com/official-website/favicon.svg"
      mce_href="https://static-index-4gtuqm3bfa95c963-1304825656.tcloudbaseapp.com/official-website/favicon.svg"
      type="image/x-icon"
    />
    <meta name="viewport" content="width=650,user-scalable=no" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
      crossorigin="anonymous"
    />
    <title>欢迎使用微信云托管</title>
    <style>
      .title-logo {
        height: 80px;
      }
      .container {
        margin-top: 100px;
      }
      .count-button {
        width: 132px;
        box-sizing: border-box;
        margin: 16px 8px;
      }
      .count-number {
        font-size: 18px;
        font-weight: bolder;
        margin: 0 8px;
      }
      .count-text {
        width: 280px;
        display: flex;
        margin: 0 auto;
        text-align: left;
        height: 40px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        line-height: 40px;
        padding: 0 12px;
      }
      .quote {
        font-size: 12px;
      }
      .qrcode {
        height: 180px;
        display: block;
        margin: 0 auto;
      }
      .title {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <div class="title">
        <img
          class="title-logo"
          src="https://static-index-4gtuqm3bfa95c963-1304825656.tcloudbaseapp.com/official-website/favicon.svg"
        />

      </div>
      <div style="text-align: center">
        <p class="count-text">当前计数：<span class="count-number"></span></p>
        <div
          style="display: flex; justify-content: center; margin-bottom: 80px"
        >
          <a class="btn btn-success btn-lg count-button" style="background: #07C160; border: 0;" onclick="set('inc')"
            >计数+1</a
          >
          <a
            class="btn btn-outline-success btn-lg count-button"
            style="background: rgba(0,0,0,0.03); color: #07C160; border: 0;"
            onclick="set('clear')"
            >清零</a
          >
        </div>
      </div>
    </div>
  </body>
  <script src="https://mat1.gtimg.com/www/asset/lib/jquery/jquery/jquery-1.11.1.min.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
    crossorigin="anonymous"
  ></script>
  <script>
    wx.config({
      debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
      appId: '', // 必填，公众号的唯一标识
      timestamp: , // 必填，生成签名的时间戳
      nonceStr: '', // 必填，生成签名的随机串
      signature: '',// 必填，签名
      jsApiList: [] // 必填，需要使用的JS接口列表
    });
    init();
    function init() {
      $.ajax("/api/count", {
        method: "get",
      }).done(function (res) {
        if (res && res.data !== undefined) {
          $(".count-number").html(res.data);
        }
      });
    }
    function set(action) {
      $.ajax("/api/count", {
        method: "POST",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        data: JSON.stringify({
          action: action,
        }),
      }).done(function (res) {
        if (res && res.data !== undefined) {
          $(".count-number").html(res.data);
        }
      });
    }
  </script>
</html>
