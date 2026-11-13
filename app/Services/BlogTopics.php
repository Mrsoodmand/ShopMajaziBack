<?php

namespace App\Services;

/**
 * The topics the storefront blog is filtered by. The admin can set one per post (blogs.topic); a post without one
 * gets a topic guessed from its slug and title. News posts are always "news".
 */
class BlogTopics
{
    /** Topic id → Persian label for the admin. The storefront has its own labels for the same ids. */
    const LABELS = [
        'guides' => 'راهنما و اشتراک‌ها',
        'numbers' => 'شماره مجازی',
        'apple' => 'اپل آیدی و اپل',
        'telegram' => 'تلگرام',
        'social' => 'شبکه‌های اجتماعی',
        'news' => 'اخبار تکنولوژی',
    ];

    /** [topic, words]: checked in order, the first match wins. Slugs are English, titles Persian. */
    const RULES = [
        ['numbers', ['virtual-number', 'شماره مجازی']],
        ['telegram', ['telegram', 'تلگرام']],
        ['apple', ['apple', 'iphone', 'icloud', 'ios-', 'face-id', 'airpod', 'اپل', 'آیفون', 'آیکلود']],
        ['numbers', ['google-voice', 'esim', 'number', 'گوگل ویس', 'شماره']],
        ['social', ['instagram', 'youtube', 'tiktok', 'tik-tok', 'twitter', 'اینستاگرام', 'یوتیوب', 'تیک تاک']],
    ];

    public static function ids(): array
    {
        return array_keys(self::LABELS);
    }

    /** The topic a post is shown under: the admin's choice, or a guess. */
    public static function of(object $post): string
    {
        if (($post->type ?? 'blog') === 'news') {
            return 'news';
        }
        $topic = $post->topic ?? null;
        return isset(self::LABELS[$topic]) ? $topic : self::guess((string) ($post->slug ?? ''), (string) ($post->title ?? ''));
    }

    public static function guess(string $slug, string $title = ''): string
    {
        $text = mb_strtolower($slug . ' ' . $title);
        foreach (self::RULES as [$topic, $words]) {
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    return $topic;
                }
            }
        }
        return 'guides';
    }
}
