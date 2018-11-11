<?php
/**
 * Created by PhpStorm.
 * User: zhangjun
 * Date: 03/10/2018
 * Time: 3:06 PM
 */
//

define("IN_DISCUZ", true);

require_once "lib/helper/ZalyAes.php";
require_once "lib/helper/ZalyRsa.php";

class Duckchat
{
    private static  $pathList = [
        './source/plugin/duckchat/proto/',
        './source/plugin/duckchat/lib/',
    ];
    private $bodyFormatType;
    private $action;
    private static $registedClasses = [];
    private $bodyFormatArr = [
        "json",
        "pb",
        "base64pb"
    ];
    private $defaultBodyFormat = "pb";
    private $sitePemPath = './source/plugin/duckchat/sitePubk.pem';
    private $headers = [];
    public $requestMessage;
    public $requestTransportData;
    private static $defaultDirName = './source/plugin/duckchat/proto/';

    public function __construct()
    {
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

    function handleSessionVerify()
    {
        $tag = __CLASS__.'-'.__FUNCTION__;

        $this->action = $_GET['action'];
        $this->bodyFormatType = isset($_GET['body_format']) ? $_GET['body_format'] : "";
        $this->bodyFormatType = strtolower($this->bodyFormatType);
        if (!in_array($this->bodyFormatType, $this->bodyFormatArr)) {
            $this->bodyFormatType = $this->defaultBodyFormat;
        }
        $reqData = file_get_contents("php://input");
        // 将数据转为TransportData
        $usefulForProtobufAnyParse = new \Zaly\Proto\Platform\ApiSessionVerifyRequest();
        $this->requestTransportData = new \Zaly\Proto\Core\TransportData();
        try {
            if ("json" == $this->bodyFormatType) {
                if (empty($reqData)) {
                    $reqData = "{}";
                }
                $this->requestTransportData->mergeFromJsonString($reqData);
            } elseif ("pb" == $this->bodyFormatType) {
                $this->requestTransportData->mergeFromString($reqData);
            } elseif ("base64pb" == $this->bodyFormatType) {
                $realData = base64_decode($reqData);
                $this->requestTransportData->mergeFromString($realData);
            }

        } catch (Exception $e) {
            $error = sprintf("shaoye discuz parse proto error, format: %s, error: %s", $this->bodyFormatType, $e->getMessage());
            // disabled the rpcReturn online.
            $this->setRpcError("error.proto.parse", $error);
            $this->rpcReturn($this->action, null);
            die();
        }
        $this->requestMessage = $usefulForProtobufAnyParse;

        ////解析请求数据，
        ///
        if (null !== $this->requestTransportData->getBody()) {
            $this->requestMessage = $this->requestTransportData->getBody()->unpack();
        }
        $this->rpc($this->requestMessage, $this->requestTransportData);
    }

    /**
     * @param \Zaly\Proto\Platform\ApiSessionVerifyRequest $request
     * @param \Google\Protobuf\Internal\Message $transportData
     */
    public function rpc( \Google\Protobuf\Internal\Message $request, \Google\Protobuf\Internal\Message $transportData)
    {
        $preSessionId = $request->getPreSessionId();
        try {
            $sitePubkPem = file_get_contents($this->sitePemPath);
            $userInfo = $this->getUserInfo($preSessionId);
            $username = $userInfo['username'];
            $userId = $userInfo['uid'];
            $userId = sha1($userId . "@" . $sitePubkPem);
            $userProfile = new \Zaly\Proto\Platform\LoginUserProfile();
            $userProfile->setUserId($userId);
            $userProfile->setLoginName($username);
            $userProfile->setNickName($username);
            $loginUserProfileKey = $this->generateStrKey();
            $zalyRsa = new ZalyRsa();
            $key = $zalyRsa->encrypt($loginUserProfileKey, $sitePubkPem);
            $zalyAes = new ZalyAes();
            $aesStr = $zalyAes->encrypt(serialize($userProfile), $loginUserProfileKey);

            $response = new \Zaly\Proto\Platform\ApiSessionVerifyResponse();
            $response->setKey($key);
            $response->setEncryptedProfile($aesStr);

            $this->setRpcError("success", "");
            $this->rpcReturn($transportData->getAction(), $response);
        } catch (Exception $ex) {
            throw new Exception("get response failed");
        }
    }

    public function getUserInfo($preSessionId)
    {
        try{
            $preSessionId = daddslashes($preSessionId);
            $sql = "select uid, username from pre_common_session where sid='{$preSessionId}'";
            $userInfo = DB::fetch_first($sql);
        }catch (Exception $ex) {
            $userInfo = [
                'uid' => '',
                'username' => ''
            ];
        }
        return $userInfo;
    }

    public function setRpcError($errorCode, $errorInfo)
    {
        $this->setTransDataHeaders(\Zaly\Proto\Core\TransportDataHeaderKey::HeaderErrorCode, $errorCode);
        $this->setTransDataHeaders(\Zaly\Proto\Core\TransportDataHeaderKey::HeaderErrorInfo, $errorInfo);
    }

    public function setTransDataHeaders($key, $val)
    {
        $key = "_{$key}";
        $this->headers[$key] = $val;
    }

    public function rpcReturn($action, $response)
    {
        $transData = new \Zaly\Proto\Core\TransportData();
        $transData->setAction($action);

        if (null != $response) {
            $anyBody = new \Google\Protobuf\Any();
            $anyBody->pack($response);
            $transData->setBody($anyBody);
        }

        $transData->setHeader($this->headers);
        $transData->setPackageId($this->requestTransportData->getPackageId());
        $body = "";
        if ("json" == $this->bodyFormatType) {
            $body = $transData->serializeToJsonString();
            $body = trim($body);
        } elseif ("pb" == $this->bodyFormatType) {
            $body = $transData->serializeToString();
        } elseif ("base64pb" == $this->bodyFormatType) {
            $body = $transData->serializeToString();
            $body = base64_encode($body);
        } else {
            return;
        }

        echo $body;
        return;
    }

    private function generateStrKey($length = 16, $strParams = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if (!is_int($length) || $length < 0) {
            $length = 16;
        }

        $str = '';
        for ($i = $length; $i > 0; $i--) {
            $str .= $strParams[mt_rand(0, strlen($strParams) - 1)];
        }

        return $str;
    }
}

if(function_exists('spl_autoload_register')) {
    spl_autoload_register("Duckchat::load", true, true);
} else {
    function __autoload($class) {
        return Duckchat::load($class);
    }
}
$duckchat = new Duckchat();
$duckchat->handleSessionVerify();