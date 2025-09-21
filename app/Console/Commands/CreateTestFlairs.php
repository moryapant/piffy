<?php

namespace App\Console\Commands;

use App\Models\PostFlair;
use App\Models\Subfapp;
use Illuminate\Console\Command;

class CreateTestFlairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-flairs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test flairs for communities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subfapps = Subfapp::all();

        if ($subfapps->isEmpty()) {
            $this->error('No communities found. Please create a community first.');

            return 1;
        }

        $flairTemplates = [
            ['name' => 'Discussion', 'color' => '#3b82f6', 'background_color' => '#dbeafe', 'description' => 'General discussion posts'],
            ['name' => 'Question', 'color' => '#f59e0b', 'background_color' => '#fef3c7', 'description' => 'Questions and help requests'],
            ['name' => 'News', 'color' => '#ef4444', 'background_color' => '#fecaca', 'description' => 'News and announcements'],
            ['name' => 'Meme', 'color' => '#8b5cf6', 'background_color' => '#e9d5ff', 'description' => 'Funny content and memes'],
            ['name' => 'Serious', 'color' => '#374151', 'background_color' => '#f3f4f6', 'description' => 'Serious discussions only'],
            ['name' => 'Meta', 'color' => '#10b981', 'background_color' => '#d1fae5', 'description' => 'Community meta discussions'],
        ];

        foreach ($subfapps as $subfapp) {
            $this->info("Creating flairs for community: {$subfapp->name}");

            foreach ($flairTemplates as $index => $template) {
                PostFlair::create([
                    'subfapp_id' => $subfapp->id,
                    'name' => $template['name'],
                    'color' => $template['color'],
                    'background_color' => $template['background_color'],
                    'description' => $template['description'],
                    'is_active' => true,
                    'order' => $index,
                ]);
                $this->line('  ✓ Created flair: '.$template['name']);
            }
        }

        $this->info('Test flairs created successfully!');

        return 0;
    }
}
