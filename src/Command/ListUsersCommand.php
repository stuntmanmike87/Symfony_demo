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

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * A console command that lists all the existing users.
 *
 * To use this command, open a terminal window, enter into your project directory
 * and execute the following:
 *
 *     $ php bin/console app:list-users
 *
 * Check out the code of the src/Command/AddUserCommand.php file for
 * the full explanation about Symfony commands.
 *
 * See https://symfony.com/doc/current/console.html
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
#[AsCommand(
    name: 'app:list-users',
    description: 'Lists all the existing users',
    aliases: ['app:users'],
    help: <<<'HELP'
        The <info>%command.name%</info> command lists all the users registered in the application:

          <info>php %command.full_name%</info>

        By default the command only displays the 50 most recent users. Set the number of
        results to display with the <comment>--max-results</comment> option:

          <info>php %command.full_name%</info> <comment>--max-results=2000</comment>

        In addition to displaying the user list, you can also send this information to
        the email address specified in the <comment>--send-to</comment> option:

          <info>php %command.full_name%</info> <comment>--send-to=fabien@symfony.com</comment>
        HELP,
)]
final readonly class ListUsersCommand
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%app.notifications.email_sender%')]
        private string $emailSender,
        private UserRepository $users,
    ) {
    }

    /**
     * This method is executed after initialize(). It usually contains the logic
     * to execute to complete this command task.
     *
     * Commands can optionally define arguments and/or options (mandatory and optional)
     *
     * @see https://symfony.com/doc/current/console/input.html
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option('If set, the result is sent to the given email address', 'send-to')] ?string $email = null,
        #[Option('Limits the number of users listed')] int $maxResults = 50,
    ): int {
        // Use ->findBy() instead of ->findAll() to allow result sorting and limiting
        $allUsers = $this->users->findBy([], ['id' => 'DESC'], $maxResults);

        $createUserArray = (static fn(User $user): array => [
            $user->getId(),
            $user->getFullName(),
            $user->getUsername(),
            $user->getEmail(),
            implode(', ', $user->getRoles()),
        ]);

        // Doctrine query returns an array of objects, and we need an array of plain arrays
        $usersAsPlainArrays = array_map($createUserArray, $allUsers);

        // In your console commands you should always use the regular output type,
        // which outputs contents directly in the console window. However, this
        // command uses the BufferedOutput type instead, to be able to get the output
        // contents before displaying them. This is needed because the command allows
        // to send the list of users via email with the '--send-to' option
        $bufferedOutput = new BufferedOutput();
        $io = new SymfonyStyle($input, $bufferedOutput);
        $io->table(
            ['ID', 'Full Name', 'Username', 'Email', 'Roles'],
            $usersAsPlainArrays
        );

        // instead of just displaying the table of users, store its contents in a variable
        $usersAsATable = $bufferedOutput->fetch();
        $output->write($usersAsATable);

        if (null !== $email) {
            $this->sendReport($usersAsATable, $email);
        }

        return Command::SUCCESS;
    }

    /**
     * Sends the given $contents to the $recipient email address.
     */
    private function sendReport(string $contents, string $recipient): void
    {
        $email = (new Email())
            ->from($this->emailSender)
            ->to($recipient)
            ->subject(\sprintf('app:list-users report (%s)', date('Y-m-d H:i:s')))
            ->text($contents);

        $this->mailer->send($email);
    }
}
