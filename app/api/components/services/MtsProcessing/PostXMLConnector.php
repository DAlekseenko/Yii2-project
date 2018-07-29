<?php
/**
 * User: buchatskiy
 * Date: 02.06.2017
 * Time: 12:22
 */

namespace api\components\services\MtsProcessing;


use PbrLibBelCommon\Exceptions\McProcessingValidationException;
use PbrLibBelCommon\Exceptions\MqConnectorException;
use PbrLibBelCommon\Protocol\MQ\MqMessage;
use yii\web\Request;
use yii\web\Response;

/**
 * Class PostXMLConnector
 * @package api\components\services\MtsProcessing
 */
class PostXMLConnector extends \PbrLibBelCommon\Protocol\MQ\PostXMLConnector
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @return MqMessage
     * @throws MqConnectorException
     * @throws McProcessingValidationException
     */
    public function get()
    {
        $this->validateRequest();
        $requestXmlString = $this->request->getRawBody();
        $this->logger->info('received request: ' . $requestXmlString);
        return MqMessage::createFromXmlString($requestXmlString);
    }

    /**
     * @param MqMessage $message
     * @return Response
     * @throws MqConnectorException
     * @throws \PbrLibBelCommon\Exceptions\McProcessingValidationException
     * @throws \yii\base\InvalidParamException
     */
    public function ack(MqMessage $message)
    {
        $this->response->setStatusCode(202);
        return $this->setResponseContents($message);
    }

    /**
     * @param MqMessage $message
     * @return Response
     * @throws MqConnectorException
     * @throws \PbrLibBelCommon\Exceptions\McProcessingValidationException
     * @throws \yii\base\InvalidParamException
     */
    public function nack(MqMessage $message)
    {
        $this->response->setStatusCode(500);
        return $this->setResponseContents($message);
    }

    /**
     * @param Request $request
     */
    public function setRequestResponse(Request $request)
    {
        $this->request = $request;
        $this->response = new Response();
    }

    /**
     * @param MqMessage $message
     * @return Response
     * @throws \PbrLibBelCommon\Exceptions\MqConnectorException
     * @throws \PbrLibBelCommon\Exceptions\McProcessingValidationException
     */
    protected function setResponseContents(MqMessage $message)
    {
        $this->validateResponse();
        $xmlString = $message->buildXmlString();
        $this->response->format = Response::FORMAT_XML;
        $this->response->content = $xmlString;
        $this->logger->info('sent response: ' . $xmlString);
        return $this->response;
    }

    /**
     * @throws \PbrLibBelCommon\Exceptions\MqConnectorException
     */
    protected function validateRequest()
    {
        if ($this->request === null || !($this->request instanceof Request)) {
            throw new MqConnectorException('Invalid request object given!');
        }
    }

    /**
     * @throws \PbrLibBelCommon\Exceptions\MqConnectorException
     */
    protected function validateResponse()
    {
        if ($this->response === null || !($this->response instanceof Response)) {
            throw new MqConnectorException('Invalid response object given!');
        }
    }

}