/**
 * UGiA PHP UPLOADER V0.2
 *
 * @link        http://www.ugia.cn/
 * @copyright   Copyright: 2004-2005 UGiA.CN.
 * @author      legend <legendsky@hotmail.com>
 * @package     UPU
 * @version     $Id: progress.js,v 1.1.1.1 2005/09/21 23:48:33 legend9 Exp $
 */

var step       = 1;
var httplength = 0;
var fileinfo   = new Array();
var uploaded   = 0;
var usetime    = 0;
var filename   = "";
var tr;
var oSrvHTTP   = null;

var formdata   = new Array();

var curDocument = parent;

// <httprequset>
if (window.XMLHttpRequest)
{
    var oSrvHTTP = new XMLHttpRequest();
    
    if (oSrvHTTP.readyState == null)
    {
        oSrvHTTP.readyState = 1;

        oSrvHTTP.addEventListener("load", function () {
            oSrvHTTP.readyState = 4;
            
            if (typeof oSrvHTTP.onreadystatechange == "function")
            {
                oSrvHTTP.onreadystatechange();
            }
        }, false);
    }
}
else
{
    //var objSrvHTTP = new ActiveXObject("Microsoft.XMLHTTP");
    var MSXML = ['MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP'];
    for(var n = 0; n < MSXML.length; n ++)
    {
        try
        {
            var oSrvHTTP = new ActiveXObject(MSXML[n]);        
            break;
        }
        catch(e)
        {
        }
    }
}

function sendReq(strUrl)
{
    with(oSrvHTTP)
    {
        //setTimeouts(30*1000,30*1000,30*1000,30*60*1000);
        try
        {
            open("GET",strUrl, true);
            send(null);
            onreadystatechange = getReady
        }
            catch(e){
        }
    }
}

function getReady()
{
    if(oSrvHTTP.readyState == 4)
    {
        var s = oSrvHTTP.responseText;
        if(s != "")
        {
            eval(s);
        }
    }
}

//</httprequset>

function coversize(bytes)
{
    var result = 0;
    var unit   = "bytes";
    switch(true)
    {
        case bytes >= 1024 * 1024 * 1024 :
              result = Math.round(bytes / 1024 / 1024 / 1024 * 100) / 100;
              unit   = "GB";
              break;
        case bytes >= 1024 * 1024 :
              result = Math.round(bytes / 1024 / 1024 * 100) / 100;
              unit   = "MB";
              break;
        case bytes >= 1024 :
              result = Math.round(bytes / 1024 * 100) / 100;
              unit   = "KB";
              break;
        default:
              result = bytes;
    }
    
    result += " " + unit;
    return result;
}

function covertime(sec)
{
    var result = "";
    switch(true)
    {
        case sec >= 3600 :
              hours    = Math.ceil(sec / 3600) + " " + lang_hour;
              minutes  = Math.ceil(sec % 3600 / 60) + " " + lang_minute + " ";
              secs     = Math.ceil(sec % 60) + " " + lang_second;
              result  += hours + minutes + secs;
              break;
        case sec >= 60 :
              minutes  = Math.ceil(sec / 60) + " " + lang_minute + " ";
              secs     = Math.ceil(sec % 60) + " " + lang_second;
              result  += minutes + secs;
              break;
        default:
              result = sec + " " + lang_second
    }
    
    return result;
}

function setMsg(msg) {
    bytes.innerHTML = msg;
}

function send()
{
    var url = "getinfo.php?processID=" + processID + "&step=" + step + "&tmp="+ Math.random( );
   
    sendReq(url);
}

function setSrv(srv)
{
    step = 2;
    curDocument.srvAddr = srv;
    curDocument.upload();
    document.getElementById("filename").innerHTML = lang_initialize;
    document.title = lang_initialize;
}

function setHttpLength()
{
    document.getElementById("httplength").innerHTML = coversize(httplength);
    document.getElementById("filename").innerHTML = lang_prepare;
    document.title = lang_prepare;
    tr = setInterval("updateTime()", 500);
    step = 3;
}

function setFileInfo()
{   
    filename = fileinfo[1].length > 48 ? fileinfo[1].substr(0, 45) + "..." : fileinfo[1];
    document.getElementById("filename").innerHTML = lang_uploading + " : " + filename;
    step = 4;
}

function setProgress()
{
    filename = fileinfo[1].length > 48 ? fileinfo[1].substr(0, 45) + "..." : fileinfo[1];
    document.getElementById("filename").innerHTML = lang_uploading + " : " +  filename;

    document.getElementById("bytes").innerHTML = coversize(uploaded);
    progress = 345 * uploaded / httplength;
    if (progress > 345) progress = 345;
    document.getElementById("progressbar").style.width = progress;
    
    percent = Math.round(uploaded * 100 / httplength);
    if (percent > 100) percent = 100;
    
    document.title = percent + "% " + lang_uploaded + "(" + filename +")";
    
    var transpeed = Math.round(uploaded / usetime);
    document.getElementById("speed").innerHTML = coversize(transpeed) + " /" + lang_second;
    
    var remain = Math.round((httplength - uploaded) / transpeed);
    document.getElementById("remaintime").innerHTML = covertime(remain);
    
    if (uploaded >= httplength || uploaded == 0)
    {
        clearInterval(tr);
        //clearInterval(getre);
        document.getElementById("dynamicimg").src = "images/finish.gif";
        document.getElementById("filename").innerHTML = " " + lang_done;
        document.getElementById("httplength").innerHTML = coversize(uploaded);
        document.getElementById("remaintime").innerHTML = "0 " + lang_second;
        
        step = 5;
        //oSrvHTTP = null;
    }
}

function postData()
{
    clearInterval(getre);
    oSrvHTTP = null;
    document.getElementById("cancel").disabled = true;
    document.getElementById("ok").disabled = false;
        
    curDocument.formData = formdata;
    curDocument.post();

    window.close();
}

function updateTime()
{
    usetime += 0.5;
}

function cancel()
{
	clearInterval(getre);
    curDocument.document.getElementById("maskDiv").style.display="none";
   curDocument.document.getElementById("statusDivWrap").style.display="none";
}

curDocument.processID = processID;
curDocument.setUploadServer();
var getre = setInterval("send()", 1000);
