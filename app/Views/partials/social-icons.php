<?php
/**
 * Render social media icon row.
 * Pass:
 *   $links = ['facebook'=>'https://...', 'instagram'=>'...']
 *   $size  = 'sm' | 'md' (default md)
 *   $tone  = 'light' | 'dark' (default light = white icons for dark bg)
 */
$links = $links ?? [];
$size  = $size  ?? 'md';
$tone  = $tone  ?? 'light';
if (empty(array_filter($links))) return;

$platforms = social_platforms();
$cls = $tone === 'dark' ? 'text-dark' : 'text-white';
$fontSize = $size === 'sm' ? '18px' : '22px';
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?php foreach ($platforms as $key => $meta): ?>
        <?php $url = $links[$key] ?? null; if (!$url) continue; ?>
        <a href="<?= e($url) ?>"
           target="_blank" rel="noopener noreferrer"
           class="d-inline-flex align-items-center justify-content-center rounded-circle <?= e($cls) ?>"
           style="width:<?= $size==='sm'?'32px':'40px' ?>;height:<?= $size==='sm'?'32px':'40px' ?>;background:rgba(255,255,255,.08);text-decoration:none;"
           aria-label="<?= e($meta['label']) ?>"
           title="<?= e($meta['label']) ?>">
            <i class="bi <?= e($meta['icon']) ?>" style="font-size:<?= e($fontSize) ?>;"></i>
        </a>
    <?php endforeach; ?>
</div>
