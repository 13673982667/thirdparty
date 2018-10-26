
#!/bin/bash
LANG="en_US.UTF-8"
LANG="zh_CN.UTF-8"

echo "\
+-------------------------------------------+
| Duckchat - 一个安全的私有聊天软件         |
+-------------------------------------------+
| Website: https://duckchat.akaxin.com      |
+-------------------------------------------+
| Github : https://github.com/duckchat/gaga |
+-------------------------------------------+
"

parentDirName=$(cd $(dirname ${BASH_SOURCE:-$0}); dirname $(pwd))
duckchatDirName=$(cd $(dirname ${BASH_SOURCE:-$0});pwd)
echo "[DuckChat] discuz 目录为 $parentDirName\n"
echo "[DuckChat] duckchat_discuz 目录为 $duckchatDirName\n"

echo "[DuckChat] 正在复制$duckchatDirName/class/class_member_duckchat.php 到$parentDirName/source/class/ \n"
cp $duckchatDirName/class/class_member_duckchat.php $parentDirName/source/class/


echo "[DuckChat] 正在复制$duckchatDirName/function/function_member_duckchat.php 到$parentDirName/source/function/ \n"
cp $duckchatDirName/function/function_member_duckchat.php $parentDirName/source/function/


echo "[DuckChat] 正在复制$duckchatDirName/module/duckchat_member  到$parentDirName/source/moudule/ \n"
cp -rf $duckchatDirName/module/duckchat_member  $parentDirName/source/module/


echo "[DuckChat] 正在复制$duckchatDirName/plugin/duckchat  到$parentDirName/source/plugin/ \n"
cp -rf $duckchatDirName/plugin/duckchat  $parentDirName/source/plugin/


echo "[DuckChat] 正在复制$duckchatDirName/static/js/zalyjsNative.js  到$parentDirName/static/js/ \n"
cp -rf $duckchatDirName/static/js/zalyjsNative.js  $parentDirName/static/js/

echo "[DuckChat] 正在复制$duckchatDirName/member.php  到$parentDirName \n"
cp  $duckchatDirName/member.php  $parentDirName/

echo "[DuckChat] --------------------------------------------------\n"
echo "[DuckChat] 复制文件成功\n"
echo "[DuckChat] --------------------------------------------------\n"

