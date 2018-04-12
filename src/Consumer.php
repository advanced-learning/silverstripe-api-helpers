<?php

namespace AdvancedLearning\ApiHelpers;

use function array_merge_recursive;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use function strtoupper;

trait Consumer
{
    /**
     * @var AccessToken
     */
    protected $token;

    /**
     * Make a call to the API.
     *
     * @param string $path    The path for the api call relative to the url base.
     * @param string $method  The HTTP method to use.
     * @param array $data     Array of data to send to the API.
     * @param array $options  Options for configuring the Guzzle Client.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function call(string $path, string $method = 'GET', array $data = [], array $options = [])
    {
        $client = $this->getClient();

        // get oauth token if required
        if ($this->isTokenRequired()) {
            $token = $this->getToken();

            $options = array_merge_recursive($options, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ]
            ]);
        }

        // add the data to the request
        if (in_array(strtoupper($method), ['PATCH', 'POST', 'PUT'])  && !empty($data)) {
            $options['json'] = $data;
        } else if (!empty($data)) {
            $options['query'] = $data;
        }

        $response = $client->request($method, $path, $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('There was an error connecting to the API', 500);
        }

        return $response;
    }

    /**
     * Get the access token, if we don't have one, will request from server.
     *
     * @return AccessToken
     */
    protected function getToken()
    {
        if (!$this->token) {
            $provider = $this->getOauthProvider();
            $this->token = $provider->getAccessToken($this->getGrantType(), $this->getOauthOptions());
        }

        return $this->token;
    }

    /**
     * Get a Guzzle client for the API.
     *
     * @return Client
     */
    protected function getClient()
    {
        return new Client([
            'base_uri' => $this->getBaseUri()
        ]);
    }

    /**
     * Get the oauth provider.
     *
     * @return GenericProvider
     */
    protected function getOauthProvider()
    {
        return new GenericProvider($this->getOauthProviderConfig());
    }

    /**
     * Get full url to the api.
     *
     * @param string $path
     * @return string
     */
    protected function getURL(string $path): string
    {
        return $this->getBaseUri() . $path;
    }

    /**
     * Get the default oauth provider config, override to customise.
     *
     * @return array
     */
    protected function getOauthProviderConfig()
    {
        return [
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'urlAuthorize' => $this->getUrl('/oauth2/authorise'),
            'urlAccessToken' => $this->getUrl('/oauth2/authorise'),
            'urlResourceOwnerDetails'=> $this->getUrl('/oauth2/resource')
        ];
    }

    /**
     * Get the default options for the oauth authentication, can include scope etc.
     *
     * @return array
     */
    protected function getOauthOptions(): array
    {
        return [];
    }

    /**
     * Is a token from an oauth service required for the API call. Default is true.
     *
     * @return bool
     */
    protected function isTokenRequired(): bool
    {
        return true;
    }

    /**
     * Get the type of the oauth grant type
     * @return string
     */
    protected function getGrantType(): string
    {
        return 'client_credentials';
    }

    /**
     * Get the base url to api.
     *
     * @return string
     */
    protected abstract function getBaseUri(): string;

    /**
     * Get the client id for oauth authentication.
     *
     * @return string
     */
    protected abstract function getClientId(): string;

    /**
     * Get the client secret for oauth authentication.
     *
     * @return string
     */
    protected abstract function getClientSecret(): string;
}