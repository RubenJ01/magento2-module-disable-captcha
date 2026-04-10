<?php

declare(strict_types=1);

namespace RJDS\DisableCaptcha\Test\Integration\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use RJDS\DisableCaptcha\Console\Command\DisableCommand;
use RJDS\DisableCaptcha\Console\Command\EnableCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CaptchaCommandsTest extends TestCase
{
    public function testDisableAndEnableCommandsUpdateConfig(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var DisableCommand $disableCommand */
        $disableCommand = $objectManager->get(DisableCommand::class);
        $application = new Application();
        $application->add($disableCommand);
        $disableTester = new CommandTester($application->find('rjds:captcha:disable'));

        $disableExitCode = $disableTester->execute(['command' => 'rjds:captcha:disable', '--force' => true]);
        $this->assertSame(Command::SUCCESS, $disableExitCode);
        $this->assertStringNotContainsString('Aborted.', $disableTester->getDisplay());

        /** @var ResourceConnection $resource */
        $resource = $objectManager->get(ResourceConnection::class);
        $this->assertSame('0', $this->getDefaultConfigValue($resource, 'customer/captcha/enable'));
        $this->assertSame('0', $this->getDefaultConfigValue($resource, 'recaptcha_frontend/type_for/customer_login'));

        /** @var EnableCommand $enableCommand */
        $enableCommand = $objectManager->get(EnableCommand::class);
        $application->add($enableCommand);
        $enableTester = new CommandTester($application->find('rjds:captcha:enable'));

        $enableExitCode = $enableTester->execute(['command' => 'rjds:captcha:enable', '--force' => true]);
        $this->assertSame(Command::SUCCESS, $enableExitCode);
        $this->assertStringNotContainsString('Aborted.', $enableTester->getDisplay());

        $this->assertSame('1', $this->getDefaultConfigValue($resource, 'customer/captcha/enable'));
        $this->assertSame('recaptcha', $this->getDefaultConfigValue($resource, 'recaptcha_frontend/type_for/customer_login'));
    }

    private function getDefaultConfigValue(ResourceConnection $resource, string $path): string
    {
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('core_config_data');

        $select = $connection->select()
            ->from($tableName, ['value'])
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', $path)
            ->order('config_id DESC')
            ->limit(1);

        $value = $connection->fetchOne($select);
        if ($value === null) {
            return '';
        }

        return $value;
    }
}

