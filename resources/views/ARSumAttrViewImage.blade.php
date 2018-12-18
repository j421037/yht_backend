<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{--<script src="https://cdn.jsdelivr.net/npm/vue"></script>--}}
    <title>合同附件预览</title>
    <style>
        *{
            padding: 0;
            margin: 0;
        }
        body,html, #app {
            width: 100%;
            height: 100%;
            position: relative;
        }
        #app {
            text-align: center;display: flex;align-items: center;justify-content: center;
        }
        img {
           max-width: 90%;max-height: 90%;padding: 0;margin:0;
        }
    </style>
</head>
<body>
    <div id="app"><img src="{{$url}}" alt=""></div>
</body>

</html>