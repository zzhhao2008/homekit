<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ZZHHomeKit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.staticfile.net/twitter-bootstrap/5.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.staticfile.net/twitter-bootstrap/5.1.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
</head>

<body>
    <div class="row">
        <div class="col-sm-4 p-5" style="padding-top:1vh;text-align:center">
            <span id="time"></span>
            <span id="date"></span>

        </div>
        <div class="col p-3 switches">
            <div class="panel">
                <span class="text" id="switch-io3-text">小灯</span>
                <span class="open sw" id="switch-io3-control">开</span>
            </div>
            <div class="panel">
                <span class="text" id="switch-io4-text">大灯</span>
                <span class="open sw" id="switch-io4-control">开</span>
            </div>
            <div class="panel">
                <span class="close sw" onclick="update()">刷新</span>
                <span class="close sw" onclick="location.reload()">重启</span>
            </div>
        </div>
        <div class="col p-3 ">
            <img src="./icons/0@2x.png" width="60%" id="icon">
            <span id="temp"></span>
            <span id="area">淄博 天气</span>
            <div id="weather"></div>
        </div>
    </div>

</body>

</html>
<style>
    body{
        overflow: hidden;
        background: #111;
        height: 95vh;
        width: 100vw;
        color: #fff;
    }
    .panel {
        border-bottom: solid 2px #aaa;
        height: 85px;
        padding: 6px;
        font-weight: 600;
        font-size: 25px;
    }
    .panel>.text {

        line-height: 65px;
    }
    .switches {
        overflow: auto;
        max-height: 100vh;
    }

    .sw {
        border-radius: 45%;
        height: 60px;
        float: right;
        width: 60px;
        line-height: 60px;
        text-align: center;
    }

    .close {
        color: #eee;
        background: none;
        box-shadow: 0px 0px 4px 1.5px rgba(255, 255, 255);
    }

    .open {
        background: #eee;
        color: #333;
    }

    .clock {
        text-align: center;
        font-family: 'Arial', sans-serif;
        color: #333;
    }

    #time {
        font-size: 120px;
        display: block;
        line-height: 100px;
        text-shadow: 4px 4px #558ABB;
        font-weight: 300;
    }

    #date {
        font-size:25px;
        display: block;
        opacity: 0.8;
        text-shadow: 2px 2px #558ABB;
    }
    #area {
        font-size: 20px;
        display: block;
        opacity: 0.8;
        text-shadow: 1px 1px #8A55BB;
    }
    #temp {
        font-size: 30px;
        display: block;
        opacity: 0.8;
        text-shadow: 2px 2px #BB8A55;
    }
    #weather {
        font-size: 25px;
        display: block;
        opacity: 0.8;
        text-shadow: 2px 2px #BB558A;
    }
</style>
<script>
    gpiocondition = []
    ssd = "℃ ";
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        minutes = minutes < 10 ? '0' + minutes: minutes;
        hours = hours < 10 ? '0' + hours: hours;
        if(hours>=6&&hours<=21){
            $("body").css("background","#bbb")
        }
        var time = hours + '\n' + minutes;
        $('#time').text(time);
        days=["周日","周一","周二","周三","周四","周五","周六"]
        var date = /*now.getFullYear() + "-" +*/ (now.getMonth() + 1) + "月" + now.getDate()+"日 "+days[now.getDay()];
        $('#date').text(date);
    }

    function showtemp(data) {

        $("#temp").text(""+Math.floor(data[1])+ssd+data[0]+"%");
        //console.log('showtemp', data);
        return 0;
        document.getElementById("temp-value").innerHTML = "温度" + Math.floor(data[1]);
        document.getElementById("h-value").innerHTML = "湿度" + data[0];
        document.getElementById("temp-bar").style.width = Math.floor(data[1] / 50 * 100) + '%';
        document.getElementById("h-bar").style.width = Math.floor(data[0] / 100 * 100) + '%';
    }

    function update() {
        fetch('./api.php?uri=status')
        .then((response) => response.json())
        .then((data) => showtemp(data.data.dth) || gpioshow(data.data.gpio));
    }

    function gpioset(pin, value) {
        console.log('gpioset', pin, value);
        fetch('./api.php?uri=gpio/' + pin + '/' + value)
        .then((response) => response.json())
        .then((data) => gpioshow(data.data))
    }

    function gpioshow(data) {
        //console.log('gpioshow', data);
        gpiocondition = data
        for (var i = 3; i <= 13; i++) {
            thisValue = gpiocondition[i - 1]
            thisButton = document.getElementById("switch-io" + i+"-control");
            if (thisButton != null) {
                (function (index) {
                    // 这是一个IIFE，它接受当前的i值作为参数
                    if (thisValue == 1) {
                        thisButton.className = "open sw";
                        thisButton.innerHTML = "开"
                        thisButton.onclick = function () {
                            gpioset(index, 0); // 使用捕获的index值，而不是外部的i
                        };
                    } else {
                        thisButton.className = "close sw";
                        thisButton.innerHTML = "关"
                        thisButton.onclick = function () {
                            gpioset(index, 1); // 使用捕获的index值，而不是外部的i
                        };
                    }
                })(i); // 立即执行函数，并传递当前的i值
            }
        }
    }
    function wea() {
        fetch("https://api.seniverse.com/v3/weather/now.json?key=<YOUR KEY>&location=zibo&language=zh-Hans&unit=c")
        .then((response) => response.json())
        .then((data) => {
            $("#weather").text(data.results[0].now.text+" "+data.results[0].now.temperature+ssd);
            $("#icon").attr("src","./icons/"+data.results[0].now.code+"@2x.png")
        });
    }
    $(document).ready(function () {
        updateClock();
        update();
        wea();

        setInterval(function () {
            updateClock();
        }, 1000 * 60);
        setInterval(function () {
            update();
        }, 1000 * 5);
        setInterval(function() {
            wea();

        }, 1800*1000)
    })
</script>