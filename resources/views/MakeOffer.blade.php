<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body,html,* {
            padding: 0px;
            margin: 0px;
        }
        .container {
            width: 640px;
            margin: 0 auto;
            position: relative;
        }
        .title {
            height: 55px;
        }
        .title .logo {
            float: left;
        }
        .title .title_text {
            float:left;
            line-height: 55px;
            margin-left: 30px;
        }
        .hr {
            height: 12px;
            width: 100%;
            margin-top: 15px;
            background: #00a0e6;
            clear: both;
        }
        p {
            font-size: 14px;
        }
        .price-table {
            width: 100%;
            padding: 0;
            border-collapse:collapse
        }
        .price-table tr td, .price-table tr th {
            border: 1px solid #00a0e6;
            text-align: center;
            height: 30px;
        }
        .strip {
            background: #ffe9da;
        }
        .text-align {text-align: center}
        .description {
            width: 100%;
            height: auto;
        }
        .qrcode {
            float: left;
            display: block;
            margin-top: 20px;
        }
        .our-category {
            float: left;
            display: block;
            margin-left: 15px;
            width: 530px;
        }
        .other-brand {
            margin-top: 15px;
        }
        .right-buttom {
            position: absolute;
            right: 15px;
            margin-top: 15px;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            <div class="logo">
                <image src="{{asset("storage/static/logo.png")}}" style="width:180px;height:50px"></image>
            </div>
            <div class="title_text">
                <h3>
                    {{$offers->brand_name}}牌{{$offers->category_name}}/{{$offers->created_at->format("Y-m-d")}}价格表
                </h3>
            </div>
        </div>
        <div class="hr"></div>
        <table class="price-table">
            <tr>
                <th>序号</th>
                @foreach($fields as $v)
                    <th>{{$v}}</th>
                @endforeach
                <th>单位</th>
                <th>数量</th>
                <th>单价</th>
                <th>金额</th>
                <th>备注</th>
            </tr>
            @foreach($offers->rows as $k => $v)
                <tr @if($k % 2 == 1) class="strip" @endif>
                    <td>{{$k+1}}</td>
                    @foreach($fields as $fk => $fv)
                        <td>{{$v->$fk}}</td>
                    @endforeach
                    <td>{{$offers->unit}}</td>
                    <td>1</td>
                    <td>{{$v->price}}</td>
                    <td>{{$v->price}}</td>
                    <td></td>
                </tr>
            @endforeach
        </table>
        <p class="text-align" style="margin-top: 15px;">近期市场价格波动较大，实际价格以出货当天为准</p>
        <div class="hr"></div>
        <table class="price-table">
            <tr>
                <th>询价单位/项目名称</th>
                <th>联系人/联系方式</th>
                <th>备注	</th>
                <th>服务人员</th>
            </tr>
            <tr >
                <td>{{$offers->customer}}</td>
                <td></td>
                <td></td>
                <td>{{$offers->serviceor}}</td>
            </tr>
        </table>
        <div class="hr" style="margin-top: 15px;"></div>
        <div class="description">
            <div class="qrcode">
                <image src="{{asset("storage/static/qrcode.png")}}" style="width:80px;height:80px;"></image>
            </div>
            <div class="our-category">
                <h4> 经营四大类产品: </h4>
                <div>
                    <p>消防：  镀锌钢管、无缝管、沟槽件、镀锌件、抗震支架、成品支架、阀门、消防设备、高压配件</p>
                    <p>给排水：衬塑钢管、涂塑钢管、不锈钢管、给水管件、不锈钢配件、通用阀门</p>
                    <p>电气：  三级线管（JDG/KBG)、四级线管、线管配件、桥架、套管</p>
                    <p>空调：  无缝管、螺旋管、焊管、冲压配件</p>
                </div>
            </div>
        </div>
        <div class="clear-flex" style="clear: both"></div>
        <div class="other-brand">
            <p>合作品牌：泰丰侨、劳动银河、广州华岐、广东一通、迈克、迈特、山东鼎梁、山西天和、塘沽TVT中阀、雅昌、力衡、申捷、振辉、华菱</p>
        </div>
        <div class="right-buttom">
            <h4>广东首家机电产品综合服务商   服务热线:400-692-5588</h4>
        </div>
    </div>
</body>
</html>