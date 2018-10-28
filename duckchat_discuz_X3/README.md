#Duckchat 关于Discuz账号对接

## 产品说明
在现有的账户体系下，快速的接入使用duckchat源码搭建的站点。

## discuz(X3.1, X3.2, X3.3, X3.4) 文档接入说明

### disucz 文件添加
1. 下载的duckchatDiscuz文件夹, 并且把文件夹或者文件，移动到source相应的目录中，
		
2. 将正确的公钥写到plugin/duckchat/sitePubk.pem文件中
	
	* duckchat app端 首页 > 管理后台 > 站点设置 > 站点公钥 (如果不能复制，可以直接查库 select configValue  from siteConfig where configKey='sitePubkPem';)

``` 
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1U8ppAGhVyNbdTohR67k
t6967F5YKUTp7Oq7gEhbmwMB46BgtLuOW4AEnN89mk3bkIS/pDLoVr9LabRhn3jI
hJvOj2I3f18+ZzHOM3/fjJUSx0oQReQ+DGFW4yj4fh8BeZYROKhGbllhTxLWwHor
eFgL21VaqT/Li06MOfVFRI9ALN9cjvsan8S4ZAMedLlbsqbX/r+h/56K42gd9X0T
xooUeiIdesbTMTeMBkb2aBORLvZYhRPyhw/a7o+OUj/K2A86SdcqplpgM93gTfrN
fwGxOKQhHXy191tLuYZmx2SHFMTIswuwhC1XT2CRTSd5CD5+eNVUPlUaM7WLoXYV
WQIDAQAB
-----END PUBLIC KEY-----

```

3. 网页端 template/ 使用的模板 common文件夹中的header_common.htm 最下方添加

		<script type="text/javascript" src="{$_G[setting][jspath]}zalyjsNative.js?{VERHASH}"></script>

4. 手机端 template/ 使用的模板 touch/common文件夹中的header.htm 最下方添加

    <script type="text/javascript" src="{$_G[setting][jspath]}zalyjsNative.js?{VERHASH}"></script>

5. 清空discuz缓存

6. 在discuz 后台管理 》 应用 》 插件中，安装, 启动duckchat插件

### 站点修改(duckchat app端操作)

1. 管理后台 》 小程序管理 》 添加新的小程序
	* 落地URL (请换成自己的discuz url，必须带有参数from=duckchat)
		
			http://192.168.3.152:8072/member.php?mod=logging&action=login&from=duckchat

	* 小程序使用类别: 登录小程序
	* 如果有 『是否使用代理』选项， 选择【否】（如果开启了代理模式，可能会导致app不能正确响应登录事件）

2. 管理后台 》 小程序管理 》小程序序列表 》 选中自己添加的小程序，查看ID

3. 修改站点数据库siteConfig表中的loginPluginId的值为小程序ID
    * 106请换成自己的loginPluginId
   
   			update siteConfig set configValue=106 where configKey='loginPluginId';
   		
4. 小程序的登录校验地址
	* 在config.php中session_verify_102下面添加
	
	 		 'session_verify_106' => 'http://192.168.3.152:8072/plugin.php?id=duckchat&action=api.session.verify&body_format=base64pb',
	 
	* 106 替换成自己的loginPluginId, 
	* 地址替换成自己的真实地址

5. 测试discuz插件 duckchat是否生效, 在浏览器访问上述地址，是否有值打印出
	
		例如 http://192.168.3.152:8072/plugin.php?id=duckchat&action=api.session.verify&body_format=base64pb

		EuA/CjV0eXBlLmdvb2dsZWFwaXMuY29t...

6. 将discuz登录的用户，设置为站长
	
	* 手动执行下面语句， XXXXXX 为discuz的登录名
			
		update siteConfig set configValue = (select userId from siteUser where loginName='XXXXXX') where siteConfig.configKey='owner';

### 特别说明
* discuz论坛php版本不能低于5.5