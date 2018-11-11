
# discuz 帖子列表，自动发起聊天
* 暂时只支持手机版
* 该discuz论坛必须是，站点登录小程序所指向的论坛 

### disucz (X3.1, X3.2, X3.3, X3.4) 文件添加
1. 下载的duckchat_discuz_chat_X3文件夹, 并且把文件夹或者文件，移动到source相应的目录中，

2. 修改 touch 》 forum 》viewthread.html 
    * 添加在「```< header class="header">```」下面
	```
	<script type="text/javascript" src="{$_G[setting][jspath]}zalyjsNative.js?{VERHASH}"></script>
    ```

2. 修改 touch 》 forum 》viewthread.html
   * 添加在 『```  < span class="avatar">XXX</span>```』下面
    ``` 
        <!--{if (($_G['uid'] != $post['authorid']) && $post['siteUserId']) }-->
            <span style=' top: 50px;position: absolute; left: 10px; color: #0086CE; display: inline;'onclick="zalyjsGoto('u2Profile','{$post[siteUserId]}' ,'{$post[siteAddress]}')"> 发起聊天</span>
        <!--{/if}-->
    ``` 

3. 修改 touch 》 forum 》 viewthread_node.html
    
   * 添加在 『```  < span class="avatar">XXX</span>```』下面
   ``` 
    <!--{if (($_G['uid'] != $post['authorid']) && $post['siteUserId']) }-->
             <span style=' top: 50px;position: absolute; left: 10px; color: #0086CE; display: inline;' onclick="zalyjsGoto('u2Profile','{$post[siteUserId]}', '{$post[siteAddress]}')"> 发起聊天</span>
            <!--{/if}-->
    ```

4. 修改 default 》 forum 》 vierthread_node.html

* 添加在 『```              <ul class="xl xl2 o cl"></ul>```』下面


 ``` 
                <span style='cursor:pointer; margin-left: 10px; color: #0086CE; display: inline;' onclick="zalyjsGoto('u2Profile','{$post[siteUserId]}', '{$post[siteAddress]}')"> 发起聊天</span>

 ```