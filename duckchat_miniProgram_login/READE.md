
# discuz小程序，自动登录

### 站点

1. 管理后台 》 小程序管理 》 添加新的小程序
	* 落地URL (请换成自己的discuz url 必须是[api.php?mod=login])
		
			http://192.168.3.152:8034/api.php?mod=login

	* 小程序使用类别: 登录小程序
	* 如果有 『是否使用代理』选项， 选择【否】（如果开启了代理模式，可能会导致discuz不能正确响应登录事件）

2. 管理后台 》 小程序管理 》小程序序列表 》 选中自己添加的小程序，查看ID(即为miniProgramId)

3.  管理后台 》 小程序管理 》公用密钥(即为authKey)



### discuz

1. duckchat_miniProgram_login, 并且把文件夹或者文件，移动到source相应的目录中

2. 修改 api 》connect 》duckchatLogin 》config.php

    * miniProgramId 小程序ID
    * loginPluginId discuz登录的小程序ID
    * siteAddress 站点的Address
    * authKey 公用密钥
     
    