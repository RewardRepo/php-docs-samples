<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Google\Cloud\Test\Grpc;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../testing/FileUtil.php';

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        // Access the modules app top page.
        try {
            $resp = $this->client->get('');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'Spanner',
            $resp->getBody()->getContents());
    }

    public static function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        // replace placeholder values with actual values
        if (($instanceId = getenv('SPANNER_INSTANCE_ID')) &&
            ($databaseId = getenv('SPANNER_DATABASE_ID'))) {
            $filePath = $tmpDir . '/spanner.php';
            file_put_contents(
                $filePath,
                str_replace(
                    ['your-instance-id', 'your-database-id'],
                    [$instanceId, $databaseId],
                    file_get_contents($filePath)
                )
            );
        }
    }

    public function testSpanner()
    {
        if (!getenv('SPANNER_INSTANCE_ID') || !getenv('SPANNER_DATABASE_ID')) {
            $this->markTestSkipped('Set the SPANNER_INSTANCE_ID and SPANNER_DATABASE_ID ' .
                'environment variables to run the Cloud Spanner tests.');
        }
        // Access the modules app top page.
        try {
            $resp = $this->client->get('/spanner');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'Hello World',
            $resp->getBody()->getContents());
    }

    public function testMonitoring()
    {
        // Access the modules app top page.
        try {
            $resp = $this->client->get('/monitoring');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'Successfully submitted a time series',
            $resp->getBody()->getContents());
    }
}
