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

namespace App\Security;

use Override;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * It grants or denies permissions for actions related to blog posts (such as
 * showing, editing and deleting posts).
 *
 * See https://symfony.com/doc/current/security/voters.html
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class PostVoter extends Voter
{
    // Defining these constants is overkill for this simple application, but for real
    // applications, it's a recommended practice to avoid relying on "magic strings"
    public const DELETE = 'delete';

    public const EDIT = 'edit';

    public const SHOW = 'show';

    /**
     * @param mixed $subject
     *///@phpstan-param object $subject
    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        // this voter is only executed on Post objects and for three specific permissions
        return $subject instanceof Post && \in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    /**
     * @param mixed $post
     *///Post $post//TSubject $post
    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $post, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // the user must be logged in; if not, deny permission
        if (!$user instanceof User) {
            return false;
        }

        // the logic of this voter is pretty simple: if the logged user is the
        // author of the given blog post, grant permission; otherwise, deny it.
        // (the supports() method guarantees that $post is a Post object)
        /** @var Post $post */
        return $user === $post->getAuthor();
    }
}
