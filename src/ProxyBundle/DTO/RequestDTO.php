<?php

namespace ProxyBundle\DTO;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vitaly Dergunov
 */
class RequestDTO
{
    /**
     * @var ParameterBag
     */
    private $get;

    /**
     * @var $_POST
     */
    private $post;

    /**
     * @var ParameterBag
     */
    private $request;

    /**
     * @var string
     */
    private $content;

    /**
     * @var HeaderBag
     */
    private $headers;

    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $pathInfo;

    /**
     * RequestDTO constructor.
     *
     * @param HeaderBag    $headers
     * @param ParameterBag $get
     * @param ParameterBag $post
     */
    public function __construct(HeaderBag $headers, ParameterBag $get, ParameterBag $post)
    {
        $this->headers = $headers;
        $this->post = $post;
        $this->get = $get;
    }

    /**
     * @param Request $request
     *
     * @return RequestDTO
     */
    public static function createFromRequest(Request $request)
    {
        $request->server->getHeaders();
        $instance = new self(
            new HeaderBag((array) $request->server->getHeaders()),
            new ParameterBag((array) $request->query),
            new ParameterBag((array) $request)
        );
        $instance->headers = $request->headers;
        $instance->get = $request->query;
        $instance->post = $request->request;
        $instance->content = $request->getContent();
        $instance->method = $request->getRealMethod();
        $instance->pathInfo = $request->getPathInfo();
        $instance->request = $request;
        return $instance;
    }

    /**
     * @param array $headersToRemove
     */
    public function removeHeaders(array $headersToRemove = [])
    {
        foreach ($headersToRemove as $removesKey) {
            if ($this->headers->has($removesKey)) {
                $this->headers->remove($removesKey);
            }
        }
    }

    /**
     * @param array $headersToRemove
     */
    public function setUpHost(string $url = null)
    {
        $urlArray = parse_url($url);
        if (isset($urlArray['host'])) {
            $this->headers->set('host', $urlArray['host']);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * @return array
     */
    public function post()
    {
        return $this->post->all();
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->get->all();
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getRequestByKey(string $key = null) : string
    {
        return $this->request->get($key);
    }

    /**
     * @return FileBag
     */
    public function files() : FileBag
    {
        return $this->request->files;
    }

    /**
     * @return string
     */
    public function pathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * @return bool
     */
    public function isMultipart() : bool
    {
        return (strpos($this->headers->get('Content-Type'), 'multipart/form-data') !== false);
    }

    /**
     * @return array
     */
    public function common()
    {
        return [
            'get' => $this->get->all(),
            'headers' => $this->headers->all(),
            'post' => $this->post->all(),
            'content' => $this->content,
        ];
    }
}
