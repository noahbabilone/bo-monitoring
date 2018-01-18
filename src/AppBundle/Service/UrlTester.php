<?php

namespace AppBundle\Service;

//use Symfony\Component\DependencyInjection\ContainerAwareInterface;
//use Symfony\Component\DependencyInjection\ContainerAwareTrait;
//use Symfony\Component\DependencyInjection\ContainerInterface;

class UrlTester //implements ContainerAwareInterface
{
//    use ContainerAwareTrait;

    /** @var array */
    private $codes = [
        0 => 'Connection failed',
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

//    public function __construct(ContainerInterface $container)
//    {
//        $this->container = $container;
//    }

    private function init_curl($url, $host = null)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_VERBOSE, 0);
        curl_setopt($c, CURLOPT_HEADER, 1);
        curl_setopt($c, CURLOPT_TIMEOUT, 5);

        // SSL
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);

        if ($host != null && $host != '')
            curl_setopt($c, CURLOPT_HTTPHEADER, ['Host: ' . $host]);

        $retour = curl_exec($c);

        $response = [];

        $header_size = curl_getinfo($c, CURLINFO_HEADER_SIZE);
        curl_close($c);

        $response['header'] = substr($retour, 0, $header_size);
        $explode = explode(' ', $response['header']);
        $response['code'] = (is_array($explode) && count($explode) > 1) ? $explode[1] : '0';
        $response['body'] = substr($retour, $header_size);

        return $response;
    }

    public function testUrl($url, $host = null)
    {
        $error = null;
//
        $retour = $this->init_curl($url, $host);
//        dump($url, $host);
//        dump($retour);

        if ($retour['code'] != 200) {
            $error = 'Code : ' . $retour['code'] . ' : ' . $this->codes[$retour['code']] . "\n";
            $error .= 'Header retour curl : ' . $retour['header'] . "\n";
            $error .= 'Content retour curl : ' . $retour['body'] . "\n";
        }
        return ($error !== null) ? (['raw_code' => $retour['code'], 'code' => $retour['code'] . ' : ' . $this->codes[$retour['code']], 'message' => $error]) : null;
    }

    public function curlAPI($url, $host = null)
    {
        return $this->init_curl($url, $host);
    }
}