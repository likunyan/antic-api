<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create posts.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  User  $user
     * @param  Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        return $user->isAuthorOf($post);
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  User  $user
     * @param  Post  $post
     * @return bool
     */
    public function delete(User $user, Post $post)
    {
        return $user->isAuthorOf($post);
    }

    /**
     * Determine whether the user can restore the post.
     *
     * @param  User  $user
     * @param  Post  $post
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        return $user->isAuthorOf($post);
    }

    /**
     * Determine whether the user can permanently delete the post.
     *
     * @param  User  $user
     * @param  Post  $post
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        return $user->isAuthorOf($post);
    }
}
