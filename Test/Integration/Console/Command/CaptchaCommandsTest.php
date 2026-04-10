<?php

declare(strict_types=1);

namespace RJDS\DisableCaptcha\Test\Integration\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use RJDS\DisableCaptcha\Console\Command\DisableCommand;
use RJDS\DisableCaptcha\Console\Command\EnableCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CaptchaCommandsTest extends TestCase
{
    public function testDisableAndEnableCommandsUpdateConfig(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var DisableCommand $disableCommand */
        $disableCommand = $objectManager->get(DisableCommand::class);
        $disableTester = new CommandTester($disableCommand);

        $disableExitCode = $disableTester->execute(['--force' => true]);
        $this->assertSame(Command::SUCCESS, $disableExitCode);

        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->assertSame('0', (string)$scopeConfig->getValue('customer/captcha/enable'));
        $this->assertSame('0', (string)$scopeConfig->getValue('recaptcha_frontend/type_for/customer_login'));

        /** @var EnableCommand $enableCommand */
        $enableCommand = $objectManager->get(EnableCommand::class);
        $enableTester = new CommandTester($enableCommand);

        $enableExitCode = $enableTester->execute(['--force' => true]);
        $this->assertSame(Command::SUCCESS, $enableExitCode);

        $this->assertSame('1', (string)$scopeConfig->getValue('customer/captcha/enable'));
        $this->assertSame('recaptcha', (string)$scopeConfig->getValue('recaptcha_frontend/type_for/customer_login'));
    }
}

