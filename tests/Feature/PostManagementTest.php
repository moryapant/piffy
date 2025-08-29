<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\SimpleVisitMiddleware::class,
        ]);
    }

    public function test_user_can_edit_own_post(): void
    {
        $user = User::factory()->create();
        $subfapp = Subfapp::factory()->create(['created_by' => $user->id]);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'subfapp_id' => $subfapp->id,
        ]);

        $response = $this->actingAs($user)->get(route('posts.edit', $post));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Post/Edit')
            ->has('post')
            ->where('post.id', $post->id)
        );
    }

    public function test_user_cannot_edit_others_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $subfapp = Subfapp::factory()->create(['created_by' => $otherUser->id]);
        $post = Post::factory()->create([
            'user_id' => $otherUser->id,
            'subfapp_id' => $subfapp->id,
        ]);

        $response = $this->actingAs($user)->get(route('posts.edit', $post));

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $subfapp = Subfapp::factory()->create(['created_by' => $user->id]);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'subfapp_id' => $subfapp->id,
        ]);

        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'subfapp_id' => $subfapp->id,
        ];

        $response = $this->actingAs($user)
                        ->from(route('posts.edit', $post))
                        ->put(route('posts.update', $post), $updatedData);

        $response->assertRedirect(route('posts.show', $post));
        
        $post->refresh();
        $this->assertEquals('Updated Title', $post->title);
        $this->assertEquals('Updated content', $post->content);
    }

    public function test_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $subfapp = Subfapp::factory()->create(['created_by' => $user->id]);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'subfapp_id' => $subfapp->id,
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $subfapp = Subfapp::factory()->create(['created_by' => $otherUser->id]);
        $post = Post::factory()->create([
            'user_id' => $otherUser->id,
            'subfapp_id' => $subfapp->id,
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
