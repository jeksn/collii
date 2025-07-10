<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Feed;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create a test user if none exists
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Sample RSS feeds to add
        $sampleFeeds = [
            [
                'title' => 'Laravel News',
                'url' => 'https://laravel-news.com',
                'feed_url' => 'https://laravel-news.com/feed',
                'description' => 'The Latest Laravel Framework News',
            ],
            [
                'title' => 'TechCrunch',
                'url' => 'https://techcrunch.com',
                'feed_url' => 'https://techcrunch.com/feed/',
                'description' => 'Technology news and startup information',
            ],
            [
                'title' => 'The Verge',
                'url' => 'https://www.theverge.com',
                'feed_url' => 'https://www.theverge.com/rss/index.xml',
                'description' => 'Technology, science, art, and culture',
            ],
            [
                'title' => 'YouTube - Fireship',
                'url' => 'https://www.youtube.com/@Fireship',
                'feed_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCsBjURrPoezykLs9EqgamOA',
                'description' => 'Fireship - Programming tutorials and tech content',
            ],
        ];

        foreach ($sampleFeeds as $feedData) {
            // Check if feed already exists
            $existingFeed = $user->feeds()->where('feed_url', $feedData['feed_url'])->first();
            
            if (!$existingFeed) {
                $user->feeds()->create($feedData);
                $this->command->info("Created feed: {$feedData['title']}");
            } else {
                $this->command->info("Feed already exists: {$feedData['title']}");
            }
        }
    }
}
