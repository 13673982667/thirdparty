
<script type="text/javascript">

    function getOsType() {
        var u = navigator.userAgent;
        if (u.indexOf('Android') > -1) {
            clientType = 'Android';
        } else if (u.indexOf('iPhone') > -1) {
            clientType = 'IOS';
        } else {
            clientType = "PC";
        }
    }
    getOsType();

    //-private

    //-public
    function zalyjsOpenPage(url) {
        location.href = url;
    }
</script>



<?php
/**
 * Created by PhpStorm.
 * User: zhangjun
 * Date: 29/10/2018
 * Time: 3:03 PM
 */

loadcore();

require_once('./source/function/function_home.php');
require_once('./source/function/function_member.php');

require_once('./config/config_ucenter.php');
require_once('./uc_client/client.php');

class DuckchatLogin {

    private static  $pathList = [
        './api/connect/duckchatLogin/proto/',
        './api/connect/duckchatLogin/lib/',
        './api/connect/duckchatLogin/',
        './source/class/table/',
        './source/function/',
    ];
    private $config;
    public function __construct($config)
    {
        $this->config = $config;
        if(function_exists('spl_autoload_register')) {
            spl_autoload_register("DuckchatLogin::load", false, false);
        } else {
            function __autoload($class) {
                return DuckchatLogin::load($class);
            }
        }
    }

    public static  function classNameToPath($className)
    {
        $path = '';
        $lastpos = strrpos($className, "_");
        if (false !== $lastpos) {
            $path = '/' . str_replace('_', '/', substr($className, 0, $lastpos));
        }
        $lastpos = strrpos($className, "\\");
        if (false !== $lastpos) {
            $classNameArr = explode("\\", $className);
            $className = array_pop($classNameArr);
            $path = join("/", $classNameArr);
        }
        return "{$path}/{$className}.php";
    }

    public static function load($className)
    {
        $classNamePath = "";
        if (isset(self::$registedClasses[$className])) {
            $classNamePath = self::$defaultDirName .self::$registedClasses[$className];
        } else {
            $classNamePath = self::classNameToPath($className);
            foreach (self::$pathList as $dir) {
                $tmppath = $dir . $classNamePath;
                if (file_exists($tmppath)) {
                    $classNamePath = $tmppath;
                    break;
                }
            }
        }
        require_once($classNamePath);
    }

    public function getUserProfile()
    {
        $duckchatUserProfile = new DuckchatUserProfile($this->config);
        $miniProgramId = $this->config['miniProgramId'];
        $duckchatSessionId = $_COOKIE["duckchat_sessionid"];

        $userProfile = $duckchatUserProfile->getDuckChatUserProfileFromSessionId($duckchatSessionId, $miniProgramId);
        $loginName = $userProfile->getLoginName();
        $loginPluginId = $userProfile->getLoginPluginId();
        if($loginPluginId == $this->config['loginPluginId']) {
            $uid = C::t('common_member')->fetch_uid_by_username($loginName);
            $member = getuserbyuid($uid);
            if($member){
                setloginstatus($member, 0);
            }
        }

    }
}
$config = require ("./api/connect/duckchatLogin/config.php");

try{
    $duckchatLogin = new DuckchatLogin($config);

    $duckchatLogin->getUserProfile();

}catch (Exception $ex) {
    error_log($ex);
}

echo "<script type='text/javascript'>zalyjsOpenPage('./')</script>";
?>


