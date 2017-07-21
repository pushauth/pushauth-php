<?php
namespace PushAuth;
/**
 * Class PushAuth
 * https://pushauth.io/
 * @package PushAuth
 */
class PushAuth
{

    /**
     *
     */
    const SERVER_ADDRESS = 'https://api.pushauth.io/';

    /**
     * Public Key
     * @var PushAuth Public Key
     */
    private $publicKey;
    /**
     * @var
     * private key
     */
    private $privateKey;
    /**
     * @var
     * address to push
     */
    private $address;
    /**
     * @var
     * mode type
     */
    private $modeType;

    /**
     * @var
     */
    private $response;

    /**
     * @var
     */
    private $flashResponse;

    /**
     * @var
     */
    private $code;

    private $req_hash;

    private $qrConfig;

    /**
     * PushAuth constructor.
     * @param $publicKey
     * public key
     * @param $privateKey
     */
    public function __construct($publicKey, $privateKey)
    {

        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;

        $this->modeType = 'push';

        $this->qrConfig = ['margin' => '1',
            'size'                      => '128',
            'color'                     => '40,40,40',
            'backgroundColor'           => '255,255,255'];
        $this->flashResponse = true;

    }

    /**
     * @param $response
     * @return $this
     */
    public function response($response)
    {
        $this->flashResponse = $response;

        return $this;
    }

    /**
     * @param $address
     * @return $this
     */
    public function to($address)
    {
        $this->address = $address;

        return $this;
    }

    public function qrconfig($conf)
    {
        $this->qrConfig = $conf;

        return $this;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function mode($mode)
    {

        $this->modeType = $mode;

        return $this;
    }

    /**
     * @param $val
     * @return $this
     */
    public function code($val)
    {

        $this->code = $val;

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isAccept()
    {

        $req = $this->requestStatus($this->req_hash);

        $answer = null;

        if (array_key_exists('answer', $req)) {
            ($req['answer'] == 'true') ? $answer = true : $answer = false;
        }

        return $answer;

    }


    public function qr()
    {
        $data = json_encode([
            'image' => $this->qrConfig,
        ]);

        $hashStr = $this->encrypt($data);

        $request = $this->request(json_encode([
            'pk'   => $this->publicKey,
            'data' => $hashStr]), 'showqr');

        $dataRes = $this->parseResult($request);

        $this->req_hash = $dataRes['req_hash'];

        return $dataRes;

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function send()
    {

        $data = json_encode([
            'addr_to'        => $this->address,

            'mode'           => $this->modeType, //or key/code
            'code'           => $this->code,
            'flash_response' => $this->flashResponse,

        ]);

        $hashStr = $this->encrypt($data);

        $request = $this->request(json_encode([
            'pk'   => $this->publicKey,
            'data' => $hashStr]), 'send');

        return $this->showResult($request);

    }

    /**
     * @param $req_hash
     * @return mixed
     * @throws \Exception
     */
    public function requestStatus($req_hash)
    {

        $data = json_encode([
            'req_hash' => $req_hash,
        ]);

        $hashStr = $this->encrypt($data);

        $request = $this->request(json_encode([
            'pk'   => $this->publicKey,
            'data' => $hashStr]), 'status');

        return $this->showResultStatus($request);

    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    private function showResultStatus($data)
    {
        $data = $this->parseResult($data);

        return $data;
    }

    /**
     * @param $str
     * @return string
     */
    private function encrypt($str)
    {

        $hmac = hash_hmac('sha256', base64_encode($str), $this->privateKey, true);

        return base64_encode($str) . '.' . base64_encode($hmac);
    }

    /**
     * @param $str
     * @return mixed
     * @throws \Exception
     */
    private function decrypt($str)
    {

        $data = explode('.', $str);

        $body = $data[0];
        $sign = $data[1];

        if ($this->checkSignature($body, $sign) == false) {
            throw new \Exception('Error signature');
        }

        $dataNorm = base64_decode($body);

        return json_decode($dataNorm, true);
    }

    private function checkSignature($data, $clientSign)
    {
        $serverSign = base64_encode(hash_hmac('sha256', $data, $this->privateKey, true));

        if ($serverSign != $clientSign) {
            return false;
        }

        return true;
    }

    /**
     * @param $to
     * @return bool|string
     */
    private function pushUrl($to)
    {

        switch ($to) {
            case 'send':
                return '/push/send';
                break;
            case 'routes':
                return '/push/send/routes';
                break;
            case 'status':
                return '/push/status';
                break;
            case 'showqr':
                return '/qr/show';
                break;
        }

        return '/';
    }

    /**
     * @param $data
     * @param $to
     * @return mixed
     * @throws \Exception
     */
    private function request($data, $to)
    {
        $pushUrl = $this->pushUrl($to);

        $ch = curl_init(self::SERVER_ADDRESS . $pushUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        return $result;

    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    private function showResult($data)
    {
        $data           = $this->parseResult($data);
        $this->req_hash = $data['req_hash'];

        return $data['req_hash'];
    }

    private function parseError($data)
    {
        $errors = null;
        if (array_key_exists('errors', $data)) {
            $errors .= ' (';
            foreach ($data['errors'] as $field => $msgs) {
                $m = implode('.', $msgs);
                $errors .= 'Param [' . $field . ']: ' . $m;
            }
            $errors .= ' )';
        }

        throw new \Exception('Error! Code:' . $data['status_code'] . ' Message:' . $data['message'] . $errors);
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    private function parseResult($data)
    {

        if (!$data = json_decode($data, true)) {
            throw new \Exception('Can not parse JSON-format answer');
        }

        if (isset($data['status_code'])) {
            if ($data['status_code'] != 200) {
                $this->parseError($data);
            }
        }

        return $this->decrypt($data['data']);

    }

}