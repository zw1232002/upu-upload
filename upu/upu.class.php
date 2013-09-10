<?php
/**
 * UGiA PHP UPLOADER V0.2
 *
 * Copyright 2005 legend <legendsky@hotmail.com>
 * 
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2.1 of the License, or (at
 * your option) any later version.

 * This library is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301,
 * USA.
 *
 * @link        http://www.ugia.cn, http://sourceforge.net/projects/upu
 * @copyright   Copyright: 2004-2005 UGiA.CN.
 * @author      legend <legendsky@hotmail.com>
 * @package     UPU
 * @version     $Id: upu.class.php,v 1.2 2005/09/22 08:03:22 legend9 Exp $
 */

define('UPU_CLIENT_CHARSET', 'utf-8');
define('UPU_TEMP_PATH', 'temp/');
define('UPU_SAVE_PATH', 'files/');

define('UPU_SOCKET_ENDTAG',           "\x2d\x2d\x0d\x0a");
define('UPU_CRLF',                    "\x0d\x0a");

define('UPU_SOCKET_CREATE_ERROR',     "Socket创建失败");
define('UPU_SOCKET_BIND_ERROR',       "端口绑定失败");
define('UPU_SOCKET_LISTEN_ERROR',     "端口监听失败");
define('UPU_SOKET_ACCEPT_ERROR',      "无法接受客户端请求");
define('UPU_CREATE_TEMP_FILE_ERROR',  "创建临时文件失败");
define('UPU_FILE_TO_LARGE',           "文件超过指定大小");
define('UPU_GET_TMP_FILE_SIZE_ERROR', "获取临时文件大小失败");
define('UPU_READ_TMP_FILE_ERROR',     "打开临时文件失败");
define('UPU_WRITE_NEW_FILE_ERRPR',    "创建新文件失败");

class UPU
{   
    var $sPort         = 1024;
    var $ePort         = 65536;
  
    var $savePath      = UPU_SAVE_PATH;
    var $tmpPath       = UPU_TEMP_PATH;
    
    var $allowExt      = "*";
    var $maxFileSize   = 0;
    
    var $srvPort;
    var $ipAddr;
    var $processID;
    var $bufferSize    = 1024;
    
    var $Boundary;
    var $ContentLength = 0;
    var $fileSize      = 0;
    var $fileInfo      = array();
    var $formData      = array();
    
    var $errorCode     = 0;
    
     function UPU ($param=array('savePath'=>'','tmpPath'=>''))
    {
    	$this->savePath = $param['savePath'] ? $param['savePath'] :UPU_SAVE_PATH;
    	$this->tmpPath = $param['tmpPath'] ? $param['tmpPath'] :UPU_TEMP_PATH;
    	$this->srvPort   = mt_rand($this->sPort, $this->ePort);
    	$this->ipAddr    = $_SERVER['SERVER_NAME'];
    }

    function processRequest()
    {        
        $uSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($uSocket < 0) return -101;
        
        $uBind = socket_bind($uSocket, $this->ipAddr, $this->srvPort);
        if ($uBind < 0) return -102;
        
        //socket_set_blocking($uSocket);
        
        $uListen = socket_listen($uSocket, 5);
        if ($uListen < 0) return -103;        
        
        $this->writeContents($this->tmpPath . $this->processID . ".srv", $this->ipAddr.":".$this->srvPort);
                
        $uRequest = socket_accept($uSocket);
        if ($uRequest < 0) return -104;
        
        $httpResponse = "HTTP/1.1 200 OK\r\n";
        $httpResponse .= "Content-Length: 2\r\n";
        $httpResponse .= "Content-Type: text/html\r\n";
        $httpResponse .= "Last-Modified: " . date("r") . "\r\n";
        $httpResponse .= "Accept-Ranges: bytes\r\n";
        $httpResponse .= "X-Powered-By: UPU\r\n";
        $httpResponse .= "Date: " . date("r") . "\r\n\r\n";

        socket_write($uRequest, $httpResponse);

        $neededbuffer = true;
        $BufferPond   = array();
        
        $tmpFileName  = $this->tmpPath . $this->processID . ".dat";
        
        if(!$fp = fopen ($tmpFileName, "wb")) return -105;
        
        $dataRead = 0;
        while ($flag = socket_recv($uRequest, $buffer, $this->bufferSize, 0))
        {
            
            fwrite($fp, $buffer);

            // buffer pond
            array_push ($BufferPond, $buffer);
            if (count($BufferPond) == 3)
            {
                $dataRead += strlen($BufferPond[0]);
                array_shift($BufferPond);
            }
            
            $Contents = join("", $BufferPond);

            // boundary
            if (!$this->Boundary)
            {
                if (preg_match("/Content-Type: multipart\/form-data; boundary=(\S+)/i", $Contents, $matchesB))
                $this->Boundary = $matchesB[1];
            }

            // content length
            if (!$this->ContentLength)
            {
                if (preg_match("/Content-Length: (\d+)\r\n/i", $Contents, $matchesL))
                {
                    $this->ContentLength = $matchesL[1];
                    
                    $this->writeContents($this->tmpPath . $this->processID . ".con", $this->ContentLength);
                    
                    if ($this->maxFileSize && $this->ContentLength > $$maxFileSize * 1024 * 1024)
                    {
                        return -106;
                        break;
                    }
                }
            }
            
            // parse
            $boundary = str_repeat("-", 2) . $this->Boundary;
            if (strpos($Contents, $boundary))
            {   
                preg_match_all("/$boundary\r\nContent-Disposition: form-data; name=\"([^\"]*)\"(; filename=\"([^\"]*)\"\r\nContent-Type: (\S+))?\r\n/i", $Contents, $matchesF, PREG_OFFSET_CAPTURE);
                
                //print_r($matchesF);

                if ($matchesF)
                {
                    for ($i = 0; $i < count($matchesF[0]); $i ++)
                    {
                        $formIndex = $this->checkFormName($matchesF[1][$i][0], $this->formData);
                        if ( $formIndex === -1)
                        {
                            $this->formData[] = array (
                                    'name'         => $matchesF[1][$i][0],
                                    'type'         => $matchesF[3][$i] ? "file" : "form",
                                    'filename'     => $matchesF[3][$i][0],
                                    'content-type' => $matchesF[4][$i][0],
                                    'offset'       => $dataRead + $matchesF[0][$i][1],
                                    'full'         => $matchesF[0][$i][0]
                                );
                            
                            if ($matchesF[3][$i])
                            {
                                $clientArray = "new Array(\"" . $matchesF[1][$i][0] . "\",\"" . $this->getBaseName($matchesF[3][$i][0]) . "\",\"" . $matchesF[4][$i][0] . "\")";
                                $this->writeContents($this->tmpPath . $this->processID . ".inf", $clientArray);
                            }
                        }
                    }
                }
                else
                {
                    break;
                }
            }

            if ($flag < 0)
            {
                echo socket_strerror(socket_last_error($uSocket));
                break;
            }
            elseif ($flag == 0)
            {
                echo "client disconnected";
                break;
            }
            
            // end of request ?
            $eof = substr($buffer, -4);
            $las = substr($buffer, $this->bufferSize - 4, 4);
            if ($eof == UPU_SOCKET_ENDTAG || (strlen($eof) < 4 && ($las{strlen($eof) -1} == "\x0a" || $las{strlen($eof) -1} == "\x00")))
            {
                break;
            }

            $loopTime ++;
        }
        
        fclose($fp);
        
        $msg = "ok";
        socket_write($uRequest, $msg, strlen($msg));
        socket_close($uRequest);
        socket_close($uSocket);
        
        //print_r($this->fileInfo);
        //print_r($this->formData);

        // temp file size
        if (!$this->fileSize = @filesize($tmpFileName))
        {
            return -108;
        }
        
        // temp file read handle
        if (!$readHandle  = fopen($tmpFileName, "rb"))
        {
            return -109;
        }
        
        foreach ($this->formData as $k => $v)
        {
            fseek($readHandle, $v['offset'] + strlen($v['full']) + 2);
            $eOffset = $k + 1 == count($this->formData)? $this->fileSize - 35 - strlen($this->Boundary) : $this->formData[$k + 1]['offset'] - 2;
            $this->formData[$k]['filesize'] = $eOffset - $v['offset'] - strlen($v['full']) - 2;
            
            if ($v['type'] == "file")
            {
                $extension = $this->getExtension($v['filename']);
                $savepath  = $this->savePath . md5($this->processID . "-" . $k) . "." . $extension;
                $writeHandle = fopen($savepath, "wb");
                fwrite($writeHandle, @fread($readHandle,$this->formData[$k]['filesize']));
                
                // ============ PHP5.2 不支持fread 读取大文件 ==================================
               while ( ! feof($readHandle)){
               	$__temp = @fread($readHandle, 1024);         
               	fwrite($writeHandle, $__temp);
               }
                // ===========================================================================
                
                fclose($writeHandle);
                $this->formData[$k]['ext']  = $extension;
                $this->formData[$k]['path'] = $savepath;
            }
            else
            {
                $this->formData[$k]['value'] = @fread($readHandle, $this->formData[$k]['filesize']);
            }
        }
        
        fclose($readHandle);
        
        $form = array();
        foreach ($this->formData as $k => $v)
        {
            if ($v['type'] == "file")
            {
                $form[$v['name']] = array(
                        'filename'   => $this->getBaseName($v['filename']),
                        'extension'  => $v['ext'],
                        'clientpath' => $v['filename'],
                        'savepath'   => realpath($v['path']),
                        'filetype'   => $v['content-type'], 
                        'filesize'   => $v['filesize'],
					    'extension'  => $v['ext']
                    );
            }
            else
            {
                $form[$v['name']] = $v['value'];
            }
        }

        $this->writeContents($this->tmpPath . $this->processID . ".frm", serialize($form));
        
        sleep(60);
        unlink($this->tmpPath . $this->processID . ".inf");
        unlink($this->tmpPath . $this->processID . ".dat");
        unlink($this->tmpPath . $this->processID . ".con");
        unlink($this->tmpPath . $this->processID . ".srv");
    }
    
    // get socket server ip address and port
    function getSrvAddr()
    {
        return $this->getContents($this->tmpPath . $this->processID . ".srv");
    }
        
    // get Content Length
    function getContentLength()
    {
        return $this->getContents($this->tmpPath . $this->processID . ".con");
    }

    // get file info
    function getFileInfo()
    {
        return $this->getContents($this->tmpPath . $this->processID . ".inf");
    }
    // get uploaded file length
    function getUploadedLength()
    {
        if (file_exists($this->tmpPath . $this->processID . ".dat"))
        {
            return filesize($this->tmpPath . $this->processID . ".dat");
        }
        
        return 0;
    }

    function getFormData()
    {
        if (file_exists($this->tmpPath . $this->processID . ".frm"))
        {
            return $this->getContents($this->tmpPath . $this->processID . ".frm");
        }
        
        return 0;
    }

    
    function writeContents($filename, $contents)
    {
        $fwriteHandle = @fopen($filename, "w");
        if (!is_resource($fwriteHandle))
        {
            return false;
        }
        
        fwrite($fwriteHandle, $contents);
        fclose($fwriteHandle);
        
        return true;
    }
    
    
    function getContents($filename)
    {
        if (file_exists($filename))
        {
            $freadHandle = @fopen($filename, "r");
            if (!is_resource($freadHandle))
            {
                return false;
            }
            
            $contents = fread($freadHandle, filesize($filename));
            fclose($freadHandle);
        
            return $contents;
        }
        
        return false;
    }
    
    
    function getBaseName($path)
    {
        $path = str_replace("\\", "/", $path);
        return substr($path, strrpos($path, "/") + 1);
    }

    function getExtension($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    function checkFormName($formName, $arr)
    {
        foreach ($arr as $k => $v)
        {
            if ($formName == $v['name'])
            {
                return $k;
            }
        }

        return -1;
    }
}
?>