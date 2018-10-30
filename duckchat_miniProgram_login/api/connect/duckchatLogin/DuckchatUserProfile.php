<?php
/**
 * Created by PhpStorm.
 * User: zhangjun
 * Date: 30/10/2018
 * Time: 12:01 PM
 */

class DuckchatUserProfile
{

    protected $zalyAes;
    protected $zalyCurl;
    protected $authKey;
    protected $siteAddress;

    public function __construct($config)
    {
        $this->zalyAes     = new ZalyAes();
        $this->zalyCurl    = new ZalyCurl();
        $this->authKey     = $config['authKey'];
        $this->siteAddress = $config['siteAddress'];
    }
    /**
     * @param $duckchatSessionId
     * @return \Zaly\Proto\Core\PublicUserProfile
     * @throws Exception
     */
    public function getDuckChatUserProfileFromSessionId($duckchatSessionId, $miniProgramId)
    {
        $tag = __CLASS__ . "-" . __FUNCTION__;
        try {

            $action = "duckchat.session.profile";

            $requestData = new Zaly\Proto\Plugin\DuckChatSessionProfileRequest();
            $requestData->setEncryptedSessionId($duckchatSessionId);

            $response = $this->requestDuckChatInnerApi($miniProgramId, $action, $requestData);

            if (empty($response)) {
                throw new Exception("get empty response by duckchat_sessionid error");
            }

            $userProfile = $response->getProfile();
            return $userProfile->getPublic();
        } catch (Exception $ex) {
            error_log($tag, "error msg =" . $ex);
            throw $ex;
        }
    }

    /**
     * 通用本地小程序，发送请求调用 DuckChat inner api
     * @param $miniProgramId
     * @param $action
     * @param $requestProtoData
     * @return \Google\Protobuf\unpacked
     * @throws Exception
     */
    public function requestDuckChatInnerApi($miniProgramId, $action, $requestProtoData)
    {


        $requestUrl = $this->siteAddress."/?action=" . $action . "&body_format=pb&miniProgramId=" . $miniProgramId;

        $requestTransportDataString = $this->buildTransportData($action, $requestProtoData);

        //加密发送
        $encryptedTransportData = $this->zalyAes->encrypt($requestTransportDataString, $this->authKey);
       
        $encryptedHttpTransportResponse = $this->zalyCurl->request($requestUrl, "POST", $encryptedTransportData);

        //解密结果
        $httpResponse = $this->zalyAes->decrypt($encryptedHttpTransportResponse, $this->authKey);



        return $this->buildResponseFromHttp($requestUrl, $httpResponse);
    }

    private function buildTransportData($action, $requestBody)
    {
        try{
            $anyBody = new \Google\Protobuf\Any();
            $anyBody->pack($requestBody);

            $transportData = new \Zaly\Proto\Core\TransportData();
            $transportData->setAction($action);
            $transportData->setBody($anyBody);
            $transportData->setTimeMillis(ZalyHelper::getMsectime());
            return $transportData->serializeToString();
        }catch (Exception $ex) {
            error_log($ex);
        }
    }

    private function buildResponseFromHttp($url, $httpResponse)
    {

        $urlParams = parse_url($url);
        $query = isset($urlParams['query']) ? $urlParams['query'] : [];
        $urlParams = $this->zalyCurl->convertUrlQuery($query);
        $requestAction = $urlParams['action'];
        $responseBodyFormat = $urlParams['body_format'];

        if (empty($requestAction)) {
            throw new Exception("request url with no action");
        }

        if (empty($responseBodyFormat)) {
            $responseBodyFormat = 'json';
        }

        $responseTransportData = new Zaly\Proto\Core\TransportData();
        switch ($responseBodyFormat) {
            case "json":
                $responseTransportData->mergeFromJsonString($httpResponse);
                break;
            case "pb":
                $responseTransportData->mergeFromString($httpResponse);
                break;
            case "base64pb":
                $realData = base64_decode($httpResponse);
                $responseTransportData->mergeFromString($realData);
                break;
        }

        if ($requestAction != $responseTransportData->getAction()) {
            throw new Exception("response with error action");
        }
        $responseHeader = $responseTransportData->getHeader();

        if (empty($responseHeader)) {
            throw new Exception("response with empty header");
        }

        $errCode = $this->getHeaderValue($responseHeader, \Zaly\Proto\Core\TransportDataHeaderKey::HeaderErrorCode);

        if ("success" == $errCode) {
            $responseMessage = $responseTransportData->getBody()->unpack();
            return $responseMessage;
        } else {
            $errInfo = $this->getHeaderValue($responseHeader, \Zaly\Proto\Core\TransportDataHeaderKey::HeaderErrorInfo);
            throw new Exception($errInfo);
        }
    }
    private function getHeaderValue($header, $key)
    {
        if (empty($header)) {
            return false;
        }
        return $header['_' . $key];
    }
}