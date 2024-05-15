<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Override;
use Generator;
use App\Command\ListUsersCommand;

final class ListUsersCommandTest extends AbstractCommandTest
{
    /**
     * @dataProvider maxResultsProvider
     *
     * This test verifies the amount of data is right according to the given parameter max results.
     */
    public function testListUsers(int $maxResults): void
    {
        $tester = $this->executeCommand(
            ['--max-results' => $maxResults]
        );

        $emptyDisplayLines = 5;
        /* $this-> */self::assertSame($emptyDisplayLines + $maxResults, mb_substr_count($tester->getDisplay(), "\n"));
    }

    public function maxResultsProvider(): Generator
    {
        yield [1];
        yield [2];
    }

    public function testItSendsNoEmailByDefault(): void
    {
        $this->executeCommand([]);

        /* $this-> */self::assertEmailCount(0);
    }

    public function testItSendsAnEmailIfOptionProvided(): void
    {
        $this->executeCommand(['--send-to' => 'john.doe@symfony.com']);

        /* $this-> */self::assertEmailCount(1);
    }

    #[Override]
    protected function getCommandFqcn(): string
    {
        return ListUsersCommand::class;
    }
}
