<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 30.08.2017
 * Time: 21:15
 */

$loader = require(__DIR__ . '/../vendor/autoload.php');
Yii::$classMap = $loader->getClassMap();


use ForumHouse\SelectelStorageApi\Authentication\CredentialsAuthentication;
use ForumHouse\SelectelStorageApi\Container\Container;
use ForumHouse\SelectelStorageApi\Exception\UnexpectedHttpStatusException;
use ForumHouse\SelectelStorageApi\File\File;
use ForumHouse\SelectelStorageApi\Service\StorageService;
use ForumHouse\SelectelStorageApi\Utility\Http\HttpClient;
use ForumHouse\SelectelStorageApi\Utility\Response;


class SelectelStorage
{
    /**
     * @var $user
     */
    public $user;
    /**
     * @var $key
     */
    public $key;
    /**
     * @var $container
     */
    public $container;

    private $auth = null;


    /**
     *
     */
    public function init()
    {
        if (!$this->auth) {
            $this->auth = new CredentialsAuthentication($this->user, $this->key);
            $this->auth->authenticate();
        }
    }


    /**
     * @param array $localFilePath
     * @return bool
     */
    public function uploadFiles($localFilePath)
    {
        $files = [];
        $remoteFiles = $this->listFilesOnContainer();
        $container = new Container($this->container);
        $service = new StorageService($this->auth);
        foreach ($localFilePath as $file) {
            $currentFile = new File($this->checkExistsFileName(end(explode('/', $file)), $remoteFiles));
            $currentFile->setLocalName($file)
                ->setSize();
            $files[] = $currentFile;
        }
        return $service->uploadFiles($container, $files, false);
    }

    /**
     * @param $localFileName
     * @param $remoteFiles
     * @return string
     */
    private function checkExistsFileName($localFileName, $remoteFiles)
    {
        while (true) {
            if(in_array($localFileName, array_column($remoteFiles, 'name'))){
                $localFileName = uniqid() . '|' . $localFileName;
            }else{
                break;
            }
        }
        return $localFileName;
    }

    /**
     * @param $fileNames
     */
    public function deleteFiles($fileNames)
    {
        $files = [];
        foreach ($fileNames as $file) {
            $currentFile = new File($file);
            $files[] = $currentFile;
        }

        $container = new Container($this->container);
        $service = new StorageService($this->auth);
        $service->deleteFiles($container, $files);
    }

    /**
     * @return mixed
     * @throws UnexpectedHttpStatusException
     */
    public function listFilesOnContainer()
    {
        $container = new Container($this->container);
        $url = $this->auth->getStorageUrl() . '/' . $container->getName() . '/?format=json';
        $httpClient = new HttpClient();
        $request = $httpClient->createRequest('get', $url, ['exceptions' => false]);
        $request->addHeader('X-Auth-Token', $this->auth->getAuthToken());
        $response = $httpClient->send($request);
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case Response::HTTP_OK:
                return json_decode($response->getBody(), true);
            default:
                throw new UnexpectedHttpStatusException($statusCode, $response->getReasonPhrase());

        }
    }


    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $user
     * @internal param mixed $string
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}
