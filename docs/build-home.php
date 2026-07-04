<?php

declare(strict_types=1);

use League\CommonMark\CommonMarkConverter;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Writes an error message and exits the build script.
 */
function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");

    exit(1);
}

/**
 * Copies existing project files linked from the rendered documentation home into the site.
 */
function copyHomeLinkedFiles(string $html, string $projectDir, string $siteDir): void
{
    preg_match_all('~<a\b[^>]*\bhref=(["\'])(.*?)\1~i', $html, $matches);

    foreach (array_unique($matches[2]) as $href) {
        $relativePath = localFilePathFromHref($href);

        if ($relativePath === null) {
            continue;
        }

        $sourcePath = existingProjectFilePath($projectDir, $relativePath);

        if ($sourcePath === null) {
            continue;
        }

        $targetPath = $siteDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $targetDir = dirname($targetPath);

        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            fail("Unable to create documentation asset directory: {$targetDir}");
        }

        if (!copy($sourcePath, $targetPath)) {
            fail("Unable to copy documentation homepage linked file into documentation site: {$relativePath}");
        }
    }
}

/**
 * Returns a project-relative file path for a local href.
 */
function localFilePathFromHref(string $href): ?string
{
    $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $parts = parse_url($href);

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return null;
    }

    $path = $parts['path'] ?? '';

    if ($path === '' || $path[0] === '/' || $path[0] === '\\') {
        return null;
    }

    $path = str_replace('\\', '/', rawurldecode($path));

    while (str_starts_with($path, './')) {
        $path = substr($path, 2);
    }

    $segments = explode('/', $path);

    if (in_array('', $segments, true) || in_array('.', $segments, true) || in_array('..', $segments, true)) {
        return null;
    }

    return implode('/', $segments);
}

/**
 * Returns an existing project file path for a safe relative link target.
 */
function existingProjectFilePath(string $projectDir, string $relativePath): ?string
{
    $projectRoot = realpath($projectDir);
    $path = $projectDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $realPath = realpath($path);

    if ($projectRoot === false || $realPath === false || !is_file($realPath)) {
        return null;
    }

    if (!str_starts_with(strtolower($realPath), strtolower($projectRoot . DIRECTORY_SEPARATOR))) {
        return null;
    }

    return $realPath;
}

$projectDir = dirname(__DIR__);
$indexPath = $projectDir . '/.site/index.html';
$homePath = $projectDir . '/docs/index.md';

$index = file_get_contents($indexPath);
$home = file_get_contents($homePath);

if ($index === false) {
    fail("Unable to read generated documentation index: {$indexPath}");
}

if ($home === false) {
    fail("Unable to read documentation homepage: {$homePath}");
}

$converter = new CommonMarkConverter([
    'html_input' => 'allow',
    'allow_unsafe_links' => false,
]);

$homeHtml = $converter->convert($home)->getContent();
copyHomeLinkedFiles($homeHtml, $projectDir, dirname($indexPath));

$replacement = '<section class="phpdocumentor-description">' . "\n" . $homeHtml . "\n" . '</section>';

$updated = preg_replace_callback(
    '~(<div class="phpdocumentor-column -nine phpdocumentor-content">\s*)(.*?)(\s*</div>\s*<section data-search-results)~s',
    static fn (array $matches): string => $matches[1] . $replacement . $matches[3],
    $index,
    1,
    $count
);

if ($updated === null || $count !== 1) {
    fail('Unable to replace generated documentation homepage content.');
}

$stylesheet = '<link rel="stylesheet" href="css/custom.css">';

if (!str_contains($updated, $stylesheet)) {
    $updated = preg_replace(
        '~(<link rel="stylesheet" href="css/template\.css">\s*)~',
        "$1        {$stylesheet}\n",
        $updated,
        1,
        $count
    );

    if ($updated === null || $count !== 1) {
        fail('Unable to add custom documentation stylesheet.');
    }
}

if (file_put_contents($indexPath, $updated) === false) {
    fail("Unable to write generated documentation index: {$indexPath}");
}
