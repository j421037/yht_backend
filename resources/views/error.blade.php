<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <title>系统信息</title>
    <style type="text/css">
	    * {
	    	box-sizing: border-box;
	    }
	    @font-face {
	    	font-family: 'Rubik';
			src: url(<?php echo asset('storage/Rubik.woff2')?>);
	    }
    	body,html{
    		padding:0;
    		margin: 0;
    		width: 100%;
    		height: 100%;
    		font-family: Rubik, Helvetica Neue,Helvetica,microsoft yahei,arial,STHeiTi,sans-serif;
    	}
    	.wapper {
    		width: 100%;
    		height: 100%;
    		position: relative;
    		overflow: hidden;
    		background: #fd7572;
    		text-align: center;
    	}
    	.box {
    		width: 300px;
    		height: 400px;
    		position: absolute;
    		top: 50%;
    		transform: translate(-50%, -50%);
    		left: 50%;
    		background: #fff;
    		border-radius: 5px;
    		box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2); 
    	}
    	.img {
    		width: 150px;
    		height: 150px;
    		border-radius: 50%;
    		overflow: hidden;
    		margin: 20px auto;
    	}
    	.img img {
    		width: 100%;
    		height: 100%
    	}
    	.content {
    		width: 100%;
    		font-size: 14px;
    	}
    	.status {
    		font-size: 16px;
    		color: #000;
    	}
    	.msg {
    		color: #666;
    	}
    	.redirect-index {
    		margin-top: 40px;
    		padding: 15px;
    		border-top: 1px solid #f0f0f0;
    	}
    	p {
    		padding: 0px 15px;

    	}
    	a {
    		text-decoration: none;
    		color: #666;
    	}
    </style>
  </head>
  <body>
  	<div class="wapper">
  		<div class="box">
  			<div class="img">
  				<img src="{{ $img }}">
  			</div>
  			<div class="conent">
  				<div class="status"><p>{{$status}}</p></div>
  				<div class="msg"><p>{{$content}}</p></div>
  			</div>
  			<div class="redirect-index">
  				<a href="#">返回首页</a>
  			</div>
  		</div>
  	</div>
  </body>
</html>