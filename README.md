# upu-upload #
修改者：Coly
原作者：http://www.ugia.cn/?p=70
--------------------------------------
It's fixed version for UGiA PHP UPLOADER V0.21

### 主要修改说明：

#### 1.修改了upu.class.php文件 ####
  ①  修改了__construct方法，增加了自定义上传路径和临时文件路径(70-73行)
  ②  增加了248-252行代码，修复了php5.2环境下，上传失败的问题

--------------------------------------

2.修改了misc目录下，upu.js和progress.js，取消了原来的弹窗显示上传进度的效果，直接在本页显示（该修改主要是为了避免IE7及以下版本opener报错）

