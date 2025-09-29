# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Laravel Backend
- `php artisan serve` - Start the Laravel development server
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeding
- `php artisan tinker` - Interactive REPL for Laravel
- `vendor/bin/pint` - PHP code style fixer
- `vendor/bin/phpunit` - Run PHP tests
- `php artisan test` - Run tests with Laravel's test runner
- `php artisan test --filter=TestName` - Run specific test

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

## Technology Stack

### Backend Dependencies
- Laravel 11.41.3
- Inertia.js 2.0.0 for Laravel
- Ziggy 2.5.1 for route generation
- Firebase Laravel integration
- HTMLPurifier for content sanitization
- Laravel Socialite for Google OAuth
- Laravel Sanctum for API authentication

### Frontend Dependencies
- Vue.js 3.5.20
- Inertia.js Vue3 adapter
- TailwindCSS 3.4.17 with forms and typography plugins
- TipTap rich text editor with extensions
- Headless UI Vue components
- Heroicons for icons
- Firebase for additional services
- Axios for HTTP requests
- Lodash for utilities

### Development Tools
- Vite 5.4.8 for asset bundling
- Laravel Pint for code formatting
- PHPUnit for testing
- Concurrently for running multiple processes
- Laravel Boost for development tools

## File Organization

### Laravel Structure (Laravel 10 style maintained)
- Middleware in `app/Http/Middleware/`
- Service providers in `app/Providers/`
- Middleware registration in `app/Http/Kernel.php`
- Exception handling in `app/Exceptions/Handler.php`
- Console commands in `app/Console/Kernel.php`

### Vue.js Structure
- Pages in `resources/js/Pages/` (organized by feature)
- Reusable components in `resources/js/Components/`
- Layouts in `resources/js/Layouts/`

### Key Models
- Post (with soft deletes, metrics, hot scoring)
- User (Google OAuth, admin flags)
- Subfapp (communities with privacy settings)
- Comment (threaded with voting)
- PostVote/CommentVote (voting system)
- Visit/UserActivity (analytics)
- PostFlair (community-specific labels)
- Notification (user notifications)