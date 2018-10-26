#Duckchat 关于Discuz账号对接

## 产品说明
在现有的账户体系下，快速的接入使用duckchat源码搭建的站点。

## discuz(X3.2 X3.3 X3.4)文档接入说明

### disucz 文件添加
1. 将下载的duckchatDiscuz文件夹放到discuz根目录中,并将文件移动到指定的位置

	#### 方法一, 执行duckchat.sh文件,将对应的文件自动复制到相应的位置
	
		sh ./duckchat.sh

	#### 方法二, 移动module, class, function,static, plugin 中关于duckchat的文件，放入./source/相同的目录中
	
		* class/class\_member_duckchat.php 复制到 discuz的 source/class/目录中
		* function/function\_member_duckchat.php 复制到 discuz的source/function/目录中
		* module/duckchat_member目录 复制到 discuz的source/module/目录中
		* plugin/duckchat目录 复制到 discuz的source/plugin/目录中
		* member.php 替换自己根目录的member.php
		
2. 将正确的公钥写到plugin/duckchat/sitePubk.pem文件中
	 
	 	-----BEGIN PUBLIC KEY-----
			MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApuB9DsqgRcPWvzF2M42V
			L5HD+dmrq8ye1KxFA7nfiLULvvLTGABOV2uxG9kZhDszLp/XsgNuEq12S3HF+AHd
			h71D4+WJyD0SEYEo42Dx5FC4K5hMawidVj9gRNkLrty3lEfWqLqvxdFwRppIAsiU
			jcatDQY4fEe2pfEzPH/AYYrFdmOvAwxR49JgwMstgC4JcG8xNpTclmWxcmNTJD+E
			77ckRNVf/Vet8PdjI5h6IEr8ZsT1SgbMk2lcyGzxs4LMEl6KmN3qyTribadf01zl
			JblPy6a7L/wqHEiXlptPowDuPitpqA7qwbdQjJW0MU4tdbjiccpxGjEdzwFWVxsi
			IwIDAQAB
			-----END PUBLIC KEY-----

4. template/ 使用的模板 common文件夹中的header_common.html 最下方添加

		<script type="text/javascript" src="{$_G[setting][jspath]}zalyjsNative.js?{VERHASH}"></script>

5. 在discuz 后台管理 》 应用 》 插件中，安装, 启动duckchat插件

### 站点修改

1. 添加新的小程序
	* 落地URL (请换成自己的url，必须带有参数from=duckchat)
		
			http://192.168.3.152:8072/member.php?mod=logging&action=login&from=duckchat
	* 小程序使用类别: 登录小程序
	* 是否使用代理， 否（如果开启了代理模式，可能会导致app不能正确响应登录事件）
 
2. 修改数据库siteConfig表中的configValue为小程序ID
   * 106请换成自己的loginPluginId

   			update siteConfig set configValue=106 where configKey='loginPluginId';
   		
3. 小程序的登录校验地址
	* 在config.php中session_verify_102下一行添加
	
	 		 'session_verify_106' => 'http://192.168.3.152:8072/plugin.php?id=duckchat&action=api.session.verify&body_format=base64pb',
	 
	* 106 替换成自己的loginPluginId, 
	* 地址替换成自己的真实地址

### 特别说明
* discuz 暂时不能开启手机模式，会导致注入js失败
