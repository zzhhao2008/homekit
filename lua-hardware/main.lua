
-- LuaTools需要PROJECT和VERSION这两个信息
PROJECT = "HomeKit"
VERSION = "1.0.0"

-- sys库是标配
_G.sys = require("sys")
-- 初始化LED灯, 开发板上左右2个led分别是gpio12/gpio13
local LEDA= gpio.setup(12, 0, gpio.PULLUP)
local LEDB= gpio.setup(13, 0, gpio.PULLUP)
require("func")
gpiocondition = {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0}
allcondition=0
gpio.debounce(1, 100)
gpio.debounce(0, 100)
K1 = gpio.setup(1, function() -- 中断模式, 下降沿触发，开启上拉
    log.info("gpio1", "gpio1 button down",K1())
    if gpiocondition[4]==0 then
        gpio.set(4,1)
        gpiocondition[4]=1
    else
        gpio.set(4,0)
        gpiocondition[4]=0
    end
    log.info("gpioc",json.encode(gpiocondition))
end, gpio.PULLUP,gpio.FALLING)
K0 = gpio.setup(0, function() -- 中断模式, 下降沿触发，开启上拉
    log.info("gpio0", "gpio0button down",K0())
    if gpiocondition[3]==0 then
        gpio.set(3,1)
        gpiocondition[3]=1
    else
        gpio.set(3,0)
        gpiocondition[3]=0
    end
    log.info("gpioc",json.encode(gpiocondition))
end, gpio.PULLUP,gpio.FALLING)

sys.taskInit(function()
    local i=3
    while i<=13 do
        gpio.setup(i,0, gpio.PULLUP)
        i=i+1
    end
    sys.wait(1000)
    wlan.init()
    -- 修改成自己的ssid和password
    wlan.connect("ssid", "password")
    -- wlan.connect("uiot", "")
    log.info("wlan", "wait for IP_READY")
    
    while not wlan.ready() do
        local ret, ip = sys.waitUntil("IP_READY", 30000)
        -- wlan连上之后, 这里会打印ip地址
        log.info("ip", ret, ip)
        if ip then
            _G.wlan_ip = ip
        end
    end

    log.info("wlan", "ready !!", wlan.getMac())
    sys.wait(1000)
    httpsrv.start(80, function(fd, method, uri, headers, body)
        log.info("httpsrv", method, uri, json.encode(headers), body)
        -- meminfo()
        local result=Srv(uri)
        return 200, {}, result
    end)
    log.info("web", "pls open url http://" .. _G.wlan_ip .. "/")
end)

-- 用户代码已结束---------------------------------------------
-- 结尾总是这一句
sys.run()
-- sys.run()之后后面不要加任何语句!!!!!
