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

namespace App\Tests\Form\DataTransformer;

use App\Entity\Tag;
use App\Form\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use PHPUnit\Framework\TestCase;

/**
 * Tests that tags are transformed correctly using the data transformer.
 *
 * See https://symfony.com/doc/current/testing/database.html
 */
final class TagArrayToStringTransformerTest extends TestCase
{
    /**
     * Ensures that tags are created correctly.
     */
    public function testCreateTheRightAmountOfTags(): void
    {
        /** @var Tag[] $tags */
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Demo, How');

    /** @var Tag[] $tags */
    $this->assertCount(3, $tags);
        $this->assertSame('Hello', $tags[0]->getName());
    }

    /**
     * Ensures that empty tags and errors in the number of commas are
     * dealt correctly.
     */
    public function testCreateTheRightAmountOfTagsWithTooManyCommas(): void
    {
        ///** @var \Collection<int, Tag> $transformer */
        $transformer = $this->getMockedTransformer();

        $this->assertCount(3, (array) $transformer->reverseTransform('Hello, Demo,, How'));
        $this->assertCount(3, (array) $transformer->reverseTransform('Hello, Demo, How,'));
    }

    /**
     * Ensures that leading/trailing spaces are ignored for tag names.
     */
    public function testTrimNames(): void
    {
        /** @var Tag[] $tags */
        $tags = $this->getMockedTransformer()->reverseTransform('   Hello   ');

        $this->assertSame('Hello', $tags[0]->getName());
    }

    /**
     * Ensures that duplicated tag names are ignored.
     */
    public function testDuplicateNames(): void
    {
        /** @var Tag[] $tags */
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Hello, Hello');

        $this->assertCount(1, $tags);
    }

    /**
     * Ensures that the transformer uses tags already persisted in the database.
     */
    public function testUsesAlreadyDefinedTags(): void
    {
        $persistedTags = [
            new Tag('Hello'),
            new Tag('World'),
        ];
        
        /** @var Tag[] $tags */
        $tags = $this->getMockedTransformer($persistedTags)->reverseTransform('Hello, World, How, Are, You');

        $this->assertCount(5, $tags);
        $this->assertSame($persistedTags[0], $tags[0]);
        $this->assertSame($persistedTags[1], $tags[1]);
    }

    /**
     * Ensures that the transformation from Tag instances to a simple string
     * works as expected.
     */
    public function testTransform(): void
    {
        $persistedTags = [
            new Tag('Hello'),
            new Tag('World'),
        ];

        $transformed = $this->getMockedTransformer()->transform($persistedTags);

        $this->assertSame('Hello,World', $transformed);
    }

    /**
     * This helper method mocks the real TagArrayToStringTransformer class to
     * simplify the tests. See https://phpunit.de/manual/current/en/test-doubles.html.
     *
     * @param array<int, object> $findByReturnValues The values returned when calling to the findBy() method
     */
    private function getMockedTransformer(array $findByReturnValues = []): TagArrayToStringTransformer
    {
        /* $tag = new Tag('name');
        
        $tagRepository = $this->createMock(ObjectRepository::class);
        $tagRepository->expects($this->any())
            ->method('find')
            ->willReturn($tag);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($tagRepository); */

        /** @var \App\Repository\TagRepository $tagRepository */
        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tagRepository->expects($this->any())//Call to an undefined method App\Repository\TagRepository::expects().
            ->method('findBy')
            ->willReturn($findByReturnValues);

        return new TagArrayToStringTransformer($tagRepository);
    }
}
