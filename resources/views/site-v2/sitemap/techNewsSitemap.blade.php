<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($Posts as $post)
        <url>
            <loc>{{secure_url('tech-news/'.$post->slug)}}</loc>
            <lastmod>{{\Carbon\Carbon::parse($post->updated_at ?? $post->created_at)->toAtomString()}}</lastmod>
        </url>
    @endforeach

</urlset>
