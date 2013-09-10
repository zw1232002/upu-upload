<?php
/**
 * $Id: getinfo.php,v 1.2 2005/09/22 02:17:10 legend9 Exp $
 */

header('Content-Type: text/html; charset=utf-8');
include("upu.class.php");

$processID = $_GET['processID'];
$step      = intval($_GET['step']);

$myupload = new UPU();
$myupload->processID = $processID;

switch ($step)
{
    case 1:
        if ($contents = $myupload->getSrvAddr())
        {
            echo "setSrv(\"$contents\");";
        }
        
        break;
    case 2:
        if ($contents = $myupload->getContentLength())
        {
            echo "httplength = $contents; setHttpLength();";
        }
        
        break;
    case 3:
        if ($contents = $myupload->getFileInfo())
        {
            echo "fileinfo = $contents; setFileInfo();";
        }
       
        
        break;
    case 4:
        if ($contents = $myupload->getUploadedLength())
        {
            $fileinfo = $myupload->getFileInfo();
            echo "uploaded = $contents; fileinfo = $fileinfo; setProgress();";
        }
        
        break;
    case 5: 
        if ($contents = $myupload->getFormData())
        {
            $formdata = unserialize($contents);
            
            $clientarray = "";
            
            $i = 0;
            foreach ($formdata as $k => $v)
            {
                if (is_array($v))
                {
                    $clientarray .= "formdata[" . $i . "] = new Array();\n";
                    $clientarray .= "formdata[" . $i . "][0] = 'file';\n";
                    $clientarray .= "formdata[" . $i . "][1] = '" . str2js($k) . "';\n";
                    $clientarray .= "formdata[" . $i . "][2] = '" . str2js($v['filename']) . "';\n";
                    $clientarray .= "formdata[" . $i . "][3] = '" . str2js($v['clientpath']) . "';\n";
                    $clientarray .= "formdata[" . $i . "][4] = '" . str2js($v['savepath']) . "';\n";
                    $clientarray .= "formdata[" . $i . "][5] = '" . str2js($v['filetype']) . "';\n";
                    $clientarray .= "formdata[" . $i . "][6] = '" . str2js($v['filesize']) . "';\n";
					$clientarray .= "formdata[" . $i . "][7] = '" . str2js($v['extension']) . "';\n";
                }
                else
                {
                    $clientarray .= "formdata[" . $i . "] = new Array();\n";
                    $clientarray .= "formdata[" . $i . "][0] = 'form';\n";
                    $clientarray .= "formdata[" . $i . "][1] = '" . str2js($k) . "';\n";
                    $clientarray .= "formdata[" . $i . "][2] = '" . str2js($v) . "';\n";
                }
                
                $i ++;
            }
            
            $clientarray .= "postData()";
            
            //$contents = addslashes($contents);
            @unlink ($myupload->tmpPath . $myupload->processID . ".frm");
            //echo "formdata = '$contents'; postData();";
            
            echo $clientarray;
        }

        break;
}

function str2js($str)
{
    $str = str_replace("\\", "\\\\", $str);
    $str = str_replace("'", "\\'", $str);
    $str = str_replace("\r", '\\r', $str);
    $str = str_replace("\n", '\\n', $str);
    return $str;
}
?>
