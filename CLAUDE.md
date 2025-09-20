# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Laravel Backend
- `php artisan serve` - Start the Laravel development server
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeding
- `php artisan tinker` - Interactive REPL for Laravel
- `php artisan pint` - PHP code style fixer
- `vendor/bin/phpunit` - Run PHP tests

### Frontend (Vue.js/Inertia.js)
- `yarn dev` - Start Vite development server with HMR
- `yarn build` - Build assets for production
- `npm run dev` - Alternative command for Vite development server
- `npm run build` - Alternative command for building assets

### Combined Development
- `composer run dev` - Run all development services concurrently (Laravel server, queue worker, logs, and Vite)

## Architecture Overview

This is a Laravel 11 application with an Inertia.js + Vue.js frontend, implementing a Reddit-like social platform called "fapp".

### Key Architecture Patterns

**MVC with Inertia.js**: Controllers return Inertia responses that render Vue.js components server-side with data.

**Models & Relationships**:
- `User` - Authentication with Google OAuth, has posts/comments/votes
- `Subfapp` - Communities (like subreddits) with posts and members
- `Post` - Main content with voting, comments, images, and metrics tracking
- `Comment` - Threaded comments with voting
- `Visit` & `UserActivity` - Analytics tracking system

**Frontend Structure**:
- `resources/js/Pages/` - Page components corresponding to routes
- `resources/js/Components/` - Reusable Vue components
- `resources/js/Layouts/` - Layout wrapper components

### Core Features
- **Content Management**: Posts with rich text editor (TipTap), image/video uploads
- **Community System**: Subfapps with join/leave functionality, cover images
- **Voting System**: Reddit-style upvote/downvote with score calculation
- **Analytics**: Visit tracking middleware and user activity logging
- **Admin Panel**: Content moderation, user management, bulk operations

### Database Structure
- Uses MySQL for the database
- Soft deletes on posts for content moderation
- Comprehensive analytics tables (`visits`, `user_activities`)
- Post metrics with hot scoring algorithm

### Authentication & Authorization
- Laravel Breeze with Inertia.js scaffolding
- Google OAuth integration via Socialite
- Policy-based authorization for posts, comments, subfapps
- Admin middleware for administrative functions

### File Storage
- Public disk for user uploads (avatars, post images, subfapp covers)
- Firebase integration for additional services
- Support for images and videos with type detection

### Middleware Pipeline
- `SimpleVisitMiddleware` - Tracks page visits for analytics
- `AdminMiddleware` - Restricts admin routes
- `ShareSubfapps` - Globally shares subfapp data with Inertia

### Frontend Tech Stack
- Vue.js 3 with Composition API
- Inertia.js for SPA-like navigation
- TailwindCSS for styling
- Headless UI for accessible components
- TipTap rich text editor
- Heroicons for iconography

## Common Development Patterns

### Creating New Features
1. Create migration: `php artisan make:migration create_feature_table`
2. Create model: `php artisan make:model Feature`
3. Create controller: `php artisan make:controller FeatureController --resource`
4. Add routes in `routes/web.php`
5. Create Vue.js pages in `resources/js/Pages/`
6. Add policies if needed: `php artisan make:policy FeaturePolicy`

### Testing
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- Authentication tests already implemented in `tests/Feature/Auth/`
- Run tests: `php artisan test` or `vendor/bin/phpunit`
- Run specific test: `php artisan test --filter=TestName`

### Performance & Analytics
- **Visit Throttling**: Home page post views throttled to once per IP per post per hour
- **Bulk Operations**: Admin panel supports bulk deletion and export functionality
- **Image/Video Support**: Automatic type detection for uploads (image vs video)
- **Post Metrics**: Real-time tracking of views, votes, comments, and trending scores

### Key Routes & Controllers
- Home: `/` → `PostController@index` (main feed)
- Posts: Resource routes with voting, comments, and visit tracking
- Subfapps: Community management with join/leave functionality
- Admin: `/admin/*` with bulk operations and content moderation
- Users: Profile pages and authentication routes
- Visit tracking: Middleware on specific routes for analytics

### Important Implementation Details
- **Visit Tracking**: Dual tracking system using `SimpleVisitMiddleware` (general visits) + `PostController::trackPostView()` (post-specific views)
- **Authentication**: Google OAuth + Laravel Breeze, admin users via middleware
- **File Uploads**: Uses public disk storage for avatars, covers, and post media
- **Voting System**: Separate `PostVote` model with score calculations and `PostVoteObserver` for automatic score updates
- **Comments**: Threaded comment system with reply functionality
- **Hot Algorithm**: Post scoring for feed ranking based on votes and time with trending status updates
- **Community Privacy**: Four visibility types (public, restricted, private, hidden) with dynamic access control
- **Content Sanitization**: HTML content sanitized using HTMLPurifier with allowed tags configuration

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.22
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- tightenco/ziggy (ZIGGY) - v2
- laravel/pint (PINT) - v1
- @inertiajs/vue3 (INERTIA) - v2
- tailwindcss (TAILWINDCSS) - v3
- vue (VUE) - v3


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `yarn build`, `yarn dev`, `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>


=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `yarn build`, `npm run build` or ask the user to run `yarn dev`, `npm run dev`, or `composer run dev`.


=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel 11 file structure.
- This is **perfectly fine** and recommended by Laravel. Follow the existing structure from Laravel 10. We do not to need migrate to the Laravel 11 structure unless the user explicitly requests that.

### Laravel 10 Structure
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet lang="vue" name="Inertia Client Navigation">
    import { Link } from '@inertiajs/vue3'

    <Link href="/">Home</Link>
</code-snippet>

- For form handling, use `router.post` and related methods. Do not use regular forms.


<code-snippet lang="vue" name="Inertia Vue Form Example">
    <script setup>
    import { reactive } from 'vue'
    import { router } from '@inertiajs/vue3'
    import { usePage } from '@inertiajs/vue3'

    const page = usePage()

    const form = reactive({
      first_name: null,
      last_name: null,
      email: null,
    })

    function submit() {
      router.post('/users', form)
    }
    </script>

    <template>
        <h1>Create {{ page.modelName }}</h1>
        <form @submit.prevent="submit">
            <label for="first_name">First name:</label>
            <input id="first_name" v-model="form.first_name" />
            <label for="last_name">Last name:</label>
            <input id="last_name" v-model="form.last_name" />
            <label for="email">Email:</label>
            <input id="email" v-model="form.email" />
            <button type="submit">Submit</button>
        </form>
    </template>
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>