<?php

namespace AdvancedLearning\ApiHelpers;

use SS_HTTPRequest;
use SS_HTTPResponse;
use SS_HTTPResponse_Exception;

trait Producer
{
    /**
     * Checks if the json is valid. Returns array representation if it is.
     *
     * @param SS_HTTPRequest $request
     *
     * @return array
     * @throws SS_HTTPResponse_Exception
     */
    protected function getJson(SS_HTTPRequest $request)
    {
        $json = $request->getBody();

        // no data provided
        if (empty($json)) {
            $this->httpError(400,'No data provided');
        }

        $data = json_decode($json, true);

        // json couldn't be decoded
        if (empty($data)) {
            $this->httpError(400, 'Data not correctly formatted');
        }

        return $data;
    }

    /**
     * Creates an HTTPResponse for json
     *
     * @param array $data Array to be converted to json
     * @param int $responseCode HTTP response code
     *
     * @return SS_HTTPResponse
     */
    public function json(array $data, $responseCode = 200): SS_HTTPResponse
    {
        $response = new SS_HTTPResponse(json_encode($data), $responseCode);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Throws a HTTP error response encased in a {@link HTTPResponse_Exception}, which is later caught in
     * {@link RequestHandler::handleAction()} and returned to the user.
     *
     * @param int    $errorCode    The error code to return.
     * @param string $errorMessage Plaintext error message.
     *
     * @uses SS_HTTPResponse_Exception
     * @throws SS_HTTPResponse_Exception
     */
    public function httpError($errorCode, $errorMessage = null)
    {
        throw new SS_HTTPResponse_Exception($this->json([
            'errorCode' => $errorCode,
            'message' => $errorMessage
        ], $errorCode));
    }
}