function getDHT11()
    local dht11_pin = 2
    local h,t,r = sensor.dht1x(dht11_pin, true)
    log.info("dht11", "湿度", h/100, "温度", t/100,r)
    return {h/100,t/100};
end
function split(input, delimiter)
    input = tostring(input)
    delimiter = tostring(delimiter)
    if (delimiter == "") then return false end
    local pos, arr = 0, {}
    for st, sp in function() return string.find(input, delimiter, pos, true) end do
        table.insert(arr, string.sub(input, pos, st - 1))
        pos = sp + 1
    end
    table.insert(arr, string.sub(input, pos))
    return arr
end
function Srv(uri)
    --按'/'分割URI
    local pathArr = split(uri, '/')
    if pathArr[2]==nil then
        return json.encode({code=0,msg="参数错误"})
    else
        local ordertype=pathArr[2]
        if ordertype=="dth" then
            return json.encode({code=1,msg="success",data=getDHT11()})
        elseif ordertype=='gpio' then
            if pathArr[3]==nil or pathArr[4] == nil then
                return json.encode({code=0,msg="参数错误-GPIOPINSET"})
            else
                local pinid=tonumber(pathArr[3])
                local pinvalue=tonumber(pathArr[4])
                if pinid<3 or pinid>13 then
                    return json.encode({code=0,msg="参数错误-GPIOPINERROR"})
                end
                if pinvalue==0 or pinvalue==1 then
                    gpio.set(pinid,pinvalue)
                    gpiocondition[pinid]=pinvalue
                    return json.encode({code=1,msg="success",data=gpiocondition})
                end
                return json.encode({code=0,msg="参数错误-GPIOPINVALUE"})
            end
        elseif ordertype=="gpiocondition" then
            return json.encode({code=1,msg="success",data=gpiocondition})
        elseif ordertype=='status' then
            return json.encode({code=1,msg="success",data={gpio=gpiocondition,dth=getDHT11()}})
        else
            return json.encode({code=0,msg="操作类型错误"})
        end
    end
end