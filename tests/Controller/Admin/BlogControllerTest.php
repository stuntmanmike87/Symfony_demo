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

namespace App\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use Override;
use Generator;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the BlogController used
 * for managing the blog in the backend.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Whenever you test resources protected by a firewall, consider using the
 * technique explained in:
 * https://symfony.com/doc/current/testing/http_authentication.html
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class BlogControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('jane_admin');
        $this->client->loginUser($user);
    }

    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $this->client->getCookieJar()->clear();

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('john_user');
        $this->client->loginUser($user);

        $this->client->request($httpMethod, $url);

        /* $this-> */self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public static function getUrlsForRegularUsers(): Generator
    {
        yield ['GET', '/en/admin/post/'];
        yield ['GET', '/en/admin/post/1'];
        yield ['GET', '/en/admin/post/1/edit'];
        yield ['POST', '/en/admin/post/1/delete'];
    }

    public function testAdminBackendHomePage(): void
    {
        $this->client->request('GET', '/en/admin/post/');

        /* $this-> */self::assertResponseIsSuccessful();
        /* $this-> */self::assertSelectorExists(
            'body#admin_post_index #main tbody tr',
            'The backend homepage displays all the available posts.'
        );
    }

    /**
     * This test changes the database contents by creating a new blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminNewPost(): void
    {
        $postTitle = 'Blog Post Title '.random_int(0, mt_getrandmax());
        $postSummary = $this->generateRandomString(255);
        $postContent = $this->generateRandomString(1024);

        $this->client->request('GET', '/en/admin/post/new');
        $this->client->submitForm('Create post', [
            'post[title]' => $postTitle,
            'post[summary]' => $postSummary,
            'post[content]' => $postContent,
        ]);

        /* $this-> */self::assertResponseRedirects('/en/admin/post/', Response::HTTP_SEE_OTHER);

        /** @var PostRepository $postRepository */
        $postRepository = static::getContainer()->get(PostRepository::class);

        $post = $postRepository->findOneByTitle($postTitle);

        /* $this-> */self::assertNotNull($post);
        /* $this-> */self::assertSame($postSummary, $post->getSummary());
        /* $this-> */self::assertSame($postContent, $post->getContent());
    }

    public function testAdminNewDuplicatedPost(): void
    {
        $postTitle = 'Blog Post Title '.random_int(0, mt_getrandmax());
        $postSummary = $this->generateRandomString(255);
        $postContent = $this->generateRandomString(1024);

        $crawler = $this->client->request('GET', '/en/admin/post/new');
        $form = $crawler->selectButton('Create post')->form([
            'post[title]' => $postTitle,
            'post[summary]' => $postSummary,
            'post[content]' => $postContent,
        ]);
        $this->client->submit($form);

        // post titles must be unique, so trying to create the same post twice should result in an error
        $this->client->submit($form);

        /* $this-> */self::assertSelectorTextContains('form .invalid-feedback .form-error-message', 'This title was already used in another blog post, but they must be unique.');
        /* $this-> */self::assertSelectorExists('form #post_title.is-invalid');
    }

    public function testAdminShowPost(): void
    {
        $this->client->request('GET', '/en/admin/post/1');

        /* $this-> */self::assertResponseIsSuccessful();
    }

    /**
     * This test changes the database contents by editing a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminEditPost(): void
    {
        $newBlogPostTitle = 'Blog Post Title '.random_int(0, mt_getrandmax());

        $this->client->request('GET', '/en/admin/post/1/edit');
        $this->client->submitForm('Save changes', [
            'post[title]' => $newBlogPostTitle,
        ]);

        /* $this-> */self::assertResponseRedirects('/en/admin/post/1/edit', Response::HTTP_SEE_OTHER);

        /** @var PostRepository $postRepository */
        $postRepository = static::getContainer()->get(PostRepository::class);

        /** @var Post $post */
        $post = $postRepository->find(1);

        /* $this-> */self::assertSame($newBlogPostTitle, $post->getTitle());
    }

    /**
     * This test changes the database contents by deleting a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminDeletePost(): void
    {
        $crawler = $this->client->request('GET', '/en/admin/post/1');
        $this->client->submit($crawler->filter('#delete-form')->form());

        /* $this-> */self::assertResponseRedirects('/en/admin/post/', Response::HTTP_SEE_OTHER);

        /** @var PostRepository $postRepository */
        $postRepository = static::getContainer()->get(PostRepository::class);

        /* $this-> */self::assertNull($postRepository->find(1));
    }

    private function generateRandomString(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return mb_substr(str_shuffle(str_repeat($chars, (int) ceil($length / mb_strlen($chars)))), 1, $length);
    }
}
