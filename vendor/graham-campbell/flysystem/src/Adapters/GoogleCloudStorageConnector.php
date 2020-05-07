<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Flysystem.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Flysystem\Adapters;

use Google\Cloud\Storage\StorageClient;
use GrahamCampbell\Manager\ConnectorInterface;
use InvalidArgumentException;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

/**
 * This is the gcs connector class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 * @author Nir Radian <nirradi@gmail.com>
 */
class GoogleCloudStorageConnector implements ConnectorInterface
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return \Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter
     */
    public function connect(array $config)
    {
        $auth = $this->getAuth($config);
        $client = $this->getClient($auth);
        $config = $this->getConfig($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * Get the authentication data.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('projectId', $config)) {
            throw new InvalidArgumentException('The gcs connector requires project id configuration.');
        }

        $auth = [
            'projectId' => $config['projectId'],
        ];

        if (array_key_exists('keyFile', $config)) {
            $auth['keyFilePath'] = $config['keyFile'];
        }

        return $auth;
    }

    /**
     * Get the gcs client.
     *
     * @param string[] $auth
     *
     * @return \Google\Cloud\Storage\StorageClient
     */
    protected function getClient(array $auth)
    {
        return new StorageClient($auth);
    }

    /**
     * Get the configuration.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('bucket', $config)) {
            throw new InvalidArgumentException('The gcs connector requires bucket configuration.');
        }

        return array_only($config, ['bucket', 'prefix', 'apiUri']);
    }

    /**
     * Get the gcs adapter.
     *
     * @param \Google\Cloud\Storage\StorageClient $client
     * @param string[]                            $config
     *
     * @return \Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter
     */
    protected function getAdapter(StorageClient $client, array $config)
    {
        $bucket = $client->bucket($config['bucket']);

        $adapter = new GoogleStorageAdapter($client, $bucket);

        if (array_key_exists('prefix', $config)) {
            $adapter->setPathPrefix($config['prefix']);
        }

        if (array_key_exists('apiUri', $config)) {
            $adapter->setStorageApiUri($config['apiUri']);
        }

        return $adapter;
    }
}
