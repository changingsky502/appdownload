appDownload
---
## 介绍
在移动端时代，本项目怀着开源让世界更美好的初心，利用其它开源类库，将移动端app分发的原理和逻辑以代码的形式表现出来，整个项目包含了app上传自动识别安卓苹果客户端，自动生成分发链接，分发二维码。这其中苹果端app更是包含了业内常见的企业签名和超级签名功能。
## 声明
本项目遵循 [MIT](https://opensource.org/licenses/MIT) 开源协议。
## 运行环境
+ Nginx
+ PHP 5.6+
## 安装
 * 直接下载zip文件
 * Composer安装
```
composer create-project changingsky/appdownload
```
## 初次登录
+ 登录地址
    + https://您的域名/index.php?c=index&a=login
+ 登录账号
    + 用户名：admin2020 
    + 密码：pass2020
 > 提醒：在正式使用之前，注意修改用户名和密码。
 ## 演示地址
 
+ https://demo.appdownload.me/index.php?c=index&a=login
 > 提醒：演示版本，部分功能已禁用。
## 部署指引
#### 1.项目文件权限
项目下载下来后，部署到服务器，需要赋予项目权限，归属到www用户组即可。【linux 常用命令 chown -R www:www appdownload】

#### 2.签包文件执行权限
如果用到超级签名功能，则需要开启签包脚本执行权限，文件目录位置：public/sign/sign，进入服务器项目/public/sign目录执行如下命令即可。
~~~
chmod +x sign
~~~
#### 3.wget功能权限
如果存储云用到第三方云存储，需要检测服务器wget功能扩展是否安装。如果没有，执行以下命令安装：

~~~
yum install wget
~~~
#### 4.exec函数权限
如果用到超级签名功能需要开启php的exec函数。需要前往php配置文件php.ini，找到disable_functions ，删掉其中的exec，并且重启nginx(服务器)。
#### 5.zip功能权限
如果用到超级签名功能需要检测服务器zip功能扩展是否安装。如果没有，执行以下命令安装：
~~~
yum install zip
~~~
#### 6.上传文件大小限制
如果上传文件过大并且使用本地服务器存储的话，请确认服务器配置项关于上传文件大小的限制：
1. client_max_body_size 【 nginx.conf配置文件中，用于限制客户端请求报文大小】
2. upload_max_filesize 【 php.ini配置文件中，用于限制用户上传单文件的大小】
3. post_max_size 【php.ini文件中，用于限制 POST 请求 body 的大小】
> 提醒：调整完参数后，需要先停止nginx服务，再启动nginx服务。直接重启可能无效。如果应用文件大于300M，需要关注服务器的脚本执行超时时间限制和PHP执行超时时间限制。
#### 7.HTTPS协议
域名强烈建议使用https协议，如果用到超级签名功能，必须要使用https协议。互联网上可以申请免费ssl证书很多，可以自行搜索申请，如果有条件的可以购买证书。
##### 免费证书推荐:
* 宝塔面板自带申请
* lnmp一键安装包可自动配置，但是需要提前做好域名解析。
* dnspod域名解析商，可免费申请。其他第三方例如，阿里云，腾讯云，七牛云等都有申请。
#### 8.版本迭代升级
版本迭代升级，除了App/Data目录【本地数据文件】不能覆盖，其他全部可以直接进行项目文件覆盖。
## 警告
请遵循你国家的法律下使用，仅供学习研究。