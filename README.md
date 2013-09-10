# upu-upload (PHP SOCKET 大文件上传)#
## 修改者：Coly ##
##### 原作者：http://www.ugia.cn/?p=70 #####
--------------------------------------
It's fixed version for UGiA PHP UPLOADER V0.21

### 主要修改说明：

#### 1.修改了upu.class.php文件 ####
>  ①  修改了__construct方法，增加了自定义上传路径和临时文件路径(70-73行)


>  ②  增加了248-252行代码，修复了php5.2环境下，上传失败的问题

--------------------------------------

#### 2.修改了misc目录下，upu.js和progress.js ####
>  取消了原来的弹窗显示上传进度的效果，直接在本页显示（该修改主要是为了避免IE7及以下版本opener报错）


### 使用说明：

#### 1.下载upub0.2.rar, 将里面的upu目录解压到网站某一目录下。将upu/temp/, upu/files的目录权限改为755 ####

#### 2.打开upu/misc/upu.js, 修改var basePath = "/upload/upu/"; 为upu相对于网站根目录>的路径 ####

#### 3.在包含上传表单的页面中加入<script type="text/javascript" src="upu/misc/upu.js"></script>,这里的upu/misc/upu.js为upu.js的路径，然后在<form>标签中加入onsubmit="return upuInit(this)" #### 
#### 4.在你文件上传后处理的页面中使用$_POST来获取表单数据 ####


### 注意事项：

#### 1.UPU需要php4.3.0及其以上版本，并打开socket扩展，因为上传过程中需要开临时端
口，请注意一下服务器的防火墙设置。 ####

#### 2. upu/temp为上传临时文件存放目录, upu/files为文件存放目录,这两个目录可以在upu.class.php中指定。####

#### 3. 你的<form>中要有enctype="multipart/form-data"这个属性，action为文件上传成功后的处理页面，也就是说你的<form>完全按照正常的思路来写就可以了，唯一不同的是需要加入onsubmit="return upuInit(this)" ####

#### 4. 上传成功后，可以使用$_POST来获取表单数据 #### 
