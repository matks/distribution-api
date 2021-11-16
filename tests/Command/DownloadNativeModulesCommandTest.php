<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Command\DownloadNativeModulesCommand;
use App\Util\ModuleUtils;

class DownloadNativeModulesCommandTest extends AbstractCommandTestCase
{
    private DownloadNativeModulesCommand $command;
    private ModuleUtils $moduleUtils;

    public function setUp(): void
    {
        parent::setUp();
        $this->moduleUtils = $this->createMock(ModuleUtils::class);
        $this->command = new DownloadNativeModulesCommand($this->moduleUtils);
    }

    /**
     * @dataProvider provider
     */
    public function testDownloadModules(array $modules, array $stubs, array $validVersions): void
    {
        $this->moduleUtils->method('getNativeModuleList')->willReturn($modules);
        $this->moduleUtils->method('getVersions')->willReturnOnConsecutiveCalls(...$stubs);

        $downloadWriteLn = [[sprintf('<info>%d modules found</info>', count($modules))]];

        foreach ($modules as $i => $module) {
            foreach ($validVersions[$i] as $validVersion) {
                $downloadWriteLn[] = [sprintf('<info>Downloading %s %s</info>', $module, $validVersion)];
            }
        }

        $this->output->expects($this->exactly(count($downloadWriteLn)))
            ->method('writeln')
            ->withConsecutive(...$downloadWriteLn);

        $this->command->execute($this->input, $this->output);
    }

    public function provider(): array
    {
        $mainMenuVersions = json_decode(
            file_get_contents(__DIR__ . '/../ressources/stubs/ps_mainmenu-ok.json'),
            true
        );
        $welcomeVersions = json_decode(
            file_get_contents(__DIR__ . '/../ressources/stubs/welcome-ok.json'),
            true
        );

        // native modules, versions stub, versions
        return [
            [
                ['ps_mainmenu'],
                [$mainMenuVersions],
                [['1.0.0', '1.1.0', '1.2.0']],
            ],
            [
                ['ps_mainmenu', 'welcome'],
                [$mainMenuVersions, $welcomeVersions],
                [['1.0.0', '1.1.0', '1.2.0'], ['1.0.1', '1.1.1']],
            ],
        ];
    }
}
