<?php

declare(strict_types=1);

use League\CommonMark\CommonMarkConverter;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Runs phpDocumentor before applying the documentation post-processing step.
 */
function runPhpDocumentor(string $projectDir): void
{
    $configPath = $projectDir . '/.phpdoc-cache/phpdoc.build.xml';
    writeFileOrFail(
        $configPath,
        temporaryPhpDocumentorConfig($projectDir, $projectDir . '/phpdoc.xml'),
        'temporary phpDocumentor config'
    );

    $command = implode(' ', [
        escapeshellarg(PHP_BINARY),
        escapeshellarg($projectDir . '/vendor/bin/phpdoc'),
        '--force',
        '--config=' . escapeshellarg($configPath),
    ]);

    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fail("phpDocumentor failed with exit code {$exitCode}.");
    }
}

/**
 * Builds a temporary phpDocumentor config from phpdoc.xml with absolute paths.
 */
function temporaryPhpDocumentorConfig(string $projectDir, string $sourceConfigPath): string
{
    $document = new DOMDocument();
    $document->preserveWhiteSpace = false;
    $document->formatOutput = true;

    if (!$document->load($sourceConfigPath)) {
        fail("Unable to parse phpDocumentor config: {$sourceConfigPath}");
    }

    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('phpdoc', 'https://www.phpdoc.org');

    setSingleConfigNode($xpath, '//phpdoc:paths/phpdoc:output', fileUri($projectDir . '/.site'));
    setSingleConfigNode($xpath, '//phpdoc:paths/phpdoc:cache', absolutePath($projectDir . '/.phpdoc-cache'));

    foreach ($xpath->query('//phpdoc:version/phpdoc:api/phpdoc:source') ?: [] as $source) {
        if (!$source instanceof DOMElement) {
            continue;
        }

        $source->setAttribute('dsn', fileUri($projectDir));

        foreach ($xpath->query('phpdoc:path', $source) ?: [] as $path) {
            if (!$path instanceof DOMElement) {
                continue;
            }

            $path->nodeValue = relativeSourcePath($projectDir, $path->textContent);
        }
    }

    $xml = $document->saveXML();

    if ($xml === false) {
        fail('Unable to serialize temporary phpDocumentor config.');
    }

    return $xml;
}

/**
 * Returns an absolute path using URI-compatible separators.
 */
function absolutePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

/**
 * Returns a file URI for a local path.
 */
function fileUri(string $path): string
{
    $path = absolutePath($path);

    if (preg_match('~^[A-Za-z]:/~', $path) === 1) {
        return 'file:///' . $path;
    }

    return 'file://' . $path;
}

/**
 * Sets one required config node value.
 */
function setSingleConfigNode(DOMXPath $xpath, string $query, string $value): void
{
    $nodes = $xpath->query($query);

    if ($nodes === false || $nodes->length !== 1) {
        fail("Unable to update phpDocumentor config node: {$query}");
    }

    $nodes->item(0)->nodeValue = $value;
}

/**
 * Returns a source path relative to the project source DSN.
 */
function relativeSourcePath(string $projectDir, string $sourcePath): string
{
    $sourcePath = trim(str_replace('\\', '/', $sourcePath));

    if ($sourcePath === '' || $sourcePath === '.') {
        return '.';
    }

    if ($sourcePath[0] === '/') {
        $sourcePath = ltrim(relativePath($projectDir, $sourcePath), '/');
    }

    while (str_starts_with($sourcePath, './')) {
        $sourcePath = substr($sourcePath, 2);
    }

    return $sourcePath;
}

/**
 * Describes a rendered conceptual documentation page.
 */
final class DocumentationPage
{
    /**
     * @param list<Heading> $headings
     */
    public function __construct(
        public string $sourcePath,
        public string $outputPath,
        public string $sitePath,
        public string $title,
        public string $html,
        public array $headings,
    ) {
    }
}

/**
 * Describes a heading in a rendered documentation page.
 */
final class Heading
{
    public function __construct(
        public int $level,
        public string $id,
        public string $text,
    ) {
    }
}

/**
 * Writes an error message and exits the build script.
 */
function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");

    exit(1);
}

/**
 * Reads a file or fails with a contextual message.
 */
function readFileOrFail(string $path, string $description): string
{
    $content = file_get_contents($path);

    if ($content === false) {
        fail("Unable to read {$description}: {$path}");
    }

    return $content;
}

/**
 * Writes a file, creating its parent directory if needed.
 */
function writeFileOrFail(string $path, string $content, string $description): void
{
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        fail("Unable to create {$description} directory: {$directory}");
    }

    if (file_put_contents($path, $content) === false) {
        fail("Unable to write {$description}: {$path}");
    }
}

/**
 * Copies a file, creating its parent directory if needed.
 */
function copyFileOrFail(string $sourcePath, string $targetPath, string $description): void
{
    $directory = dirname($targetPath);

    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        fail("Unable to create {$description} directory: {$directory}");
    }

    if (!copy($sourcePath, $targetPath)) {
        fail("Unable to copy {$description}: {$sourcePath}");
    }
}

/**
 * Copies static documentation assets into the generated site.
 */
function copyStaticAssets(string $projectDir, string $siteDir): void
{
    $assetsDir = $projectDir . '/docs/assets';

    if (!is_dir($assetsDir)) {
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($assetsDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $relativePath = relativePath($assetsDir, $file->getPathname());
        $targetPath = $siteDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        copyFileOrFail($file->getPathname(), $targetPath, 'documentation asset');
    }
}

/**
 * Removes previously generated topic pages.
 */
function clearGeneratedTopics(string $siteDir): void
{
    $topicsDir = $siteDir . '/topics';

    if (!is_dir($topicsDir)) {
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($topicsDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }

        if ($file->isDir()) {
            if (!rmdir($file->getPathname())) {
                fail("Unable to remove generated documentation topic directory: {$file->getPathname()}");
            }

            continue;
        }

        if (!unlink($file->getPathname())) {
            fail("Unable to remove generated documentation topic file: {$file->getPathname()}");
        }
    }

    if (!rmdir($topicsDir)) {
        fail("Unable to remove generated documentation topic directory: {$topicsDir}");
    }
}

/**
 * Returns a normalized relative path from one path to another.
 */
function relativePath(string $rootPath, string $path): string
{
    $rootPath = rtrim(str_replace('\\', '/', $rootPath), '/') . '/';
    $path = str_replace('\\', '/', $path);

    return substr($path, strlen($rootPath));
}

/**
 * Returns Markdown topic paths sorted by filename.
 *
 * @return list<string>
 */
function topicPaths(string $topicsDir): array
{
    if (!is_dir($topicsDir)) {
        return [];
    }

    $paths = [];
    $files = new DirectoryIterator($topicsDir);

    foreach ($files as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'md') {
            continue;
        }

        $paths[] = $file->getPathname();
    }

    usort(
        $paths,
        static fn (string $left, string $right): int => strcmp(basename($left), basename($right))
    );

    return $paths;
}

/**
 * Renders a Markdown file into a documentation page.
 */
function renderMarkdownPage(
    CommonMarkConverter $converter,
    string $sourcePath,
    string $outputPath,
    string $sitePath,
): DocumentationPage {
    $markdown = readFileOrFail($sourcePath, 'documentation Markdown page');
    $html = $converter->convert($markdown)->getContent();
    $headings = [];
    $usedIds = [];

    $html = preg_replace_callback(
        '~<h([1-6])([^>]*)>(.*?)</h\1>~s',
        static function (array $matches) use (&$headings, &$usedIds): string {
            $level = (int) $matches[1];
            $attributes = $matches[2];
            $content = $matches[3];
            $text = trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            preg_match('~\sid=(["\'])(.*?)\1~i', $attributes, $idMatch);
            $id = $idMatch[2] ?? uniqueSlug($text, $usedIds);

            if ($text !== '') {
                $headings[] = new Heading($level, $id, $text);
            }

            if (isset($idMatch[0])) {
                return $matches[0];
            }

            return '<h' . $level . ' id="' . htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $attributes . '>' . $content . '</h' . $level . '>';
        },
        $html
    );

    if ($html === null) {
        fail("Unable to add heading anchors for documentation page: {$sourcePath}");
    }

    return new DocumentationPage(
        $sourcePath,
        $outputPath,
        $sitePath,
        pageTitle($sourcePath, $headings),
        $html,
        $headings,
    );
}

/**
 * Returns a page title from its first H1 or filename.
 *
 * @param list<Heading> $headings
 */
function pageTitle(string $sourcePath, array $headings): string
{
    foreach ($headings as $heading) {
        if ($heading->level === 1) {
            return $heading->text;
        }
    }

    $name = pathinfo($sourcePath, PATHINFO_FILENAME);
    $name = str_replace(['-', '_'], ' ', $name);

    return ucwords($name);
}

/**
 * Returns a unique slug for heading anchor links.
 *
 * @param array<string, true> $usedIds
 */
function uniqueSlug(string $text, array &$usedIds): string
{
    $slug = strtolower(trim(preg_replace('~[^a-z0-9]+~i', '-', $text) ?? '', '-'));

    if ($slug === '') {
        $slug = 'section';
    }

    $candidate = $slug;
    $suffix = 2;

    while (isset($usedIds[$candidate])) {
        $candidate = $slug . '-' . $suffix;
        $suffix++;
    }

    $usedIds[$candidate] = true;

    return $candidate;
}

/**
 * Renders a documentation page into the phpDocumentor page shell.
 */
function renderPageShell(string $shell, DocumentationPage $page): string
{
    $content = '<section class="phpdocumentor-description">' . "\n" . $page->html . "\n" . '</section>';
    $replacement = '<section>' . "\n" . $content . "\n" . '</section>' . "\n" . rightSidebar($page);

    $updated = preg_replace_callback(
        '~(<div class="phpdocumentor-column -nine phpdocumentor-content">\s*)(.*?)(\s*</div>\s*<section data-search-results)~s',
        static fn (array $matches): string => $matches[1] . $replacement . $matches[3],
        $shell,
        1,
        $count
    );

    if ($updated === null || $count !== 1) {
        fail("Unable to replace generated documentation page content: {$page->sourcePath}");
    }

    $updated = withBaseHref($updated, $page->outputPath);
    $updated = withTitle($updated, $page->title);

    return withCustomStylesheet($updated);
}

/**
 * Adds the custom stylesheet link when missing.
 */
function withCustomStylesheet(string $html): string
{
    $stylesheet = '<link rel="stylesheet" href="css/custom.css">';

    if (str_contains($html, $stylesheet)) {
        return $html;
    }

    $updated = preg_replace(
        '~(<link rel="stylesheet" href="css/template\.css">\s*)~',
        "$1        {$stylesheet}\n",
        $html,
        1,
        $count
    );

    if ($updated === null || $count !== 1) {
        fail('Unable to add custom documentation stylesheet.');
    }

    return $updated;
}

/**
 * Sets the HTML base href for a generated page.
 */
function withBaseHref(string $html, string $outputPath): string
{
    $depth = substr_count($outputPath, '/');
    $baseHref = $depth === 0 ? './' : str_repeat('../', $depth);
    $updated = preg_replace('~<base href="[^"]*">~', '<base href="' . $baseHref . '">', $html, 1, $count);

    if ($updated === null || $count !== 1) {
        fail('Unable to update generated documentation base href.');
    }

    return $updated;
}

/**
 * Sets the HTML title for a generated page.
 */
function withTitle(string $html, string $title): string
{
    $title = htmlspecialchars($title . ' - civil-date API', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $updated = preg_replace('~<title>.*?</title>~s', '<title>' . $title . '</title>', $html, 1, $count);

    if ($updated === null || $count !== 1) {
        fail('Unable to update generated documentation page title.');
    }

    return $updated;
}

/**
 * Builds the right sidebar for the current page.
 */
function rightSidebar(DocumentationPage $page): string
{
    $headings = array_values(array_filter(
        $page->headings,
        static fn (Heading $heading): bool => $heading->level > 1
    ));

    if ($headings === []) {
        return '<section class="phpdocumentor-on-this-page__sidebar"></section>';
    }

    $items = '';

    foreach ($headings as $heading) {
        $items .= sprintf(
            '            <li><a href="%s#%s">%s</a></li>' . "\n",
            htmlspecialchars($page->outputPath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($heading->id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($heading->text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }

    return <<<HTML
<section class="phpdocumentor-on-this-page__sidebar">
    <section class="phpdocumentor-on-this-page__content">
        <strong class="phpdocumentor-on-this-page__title">On this page</strong>
        <ul class="phpdocumentor-list -clean">
{$items}        </ul>
    </section>
</section>
HTML;
}

/**
 * Adds the Topics menu to all generated HTML files when topics exist.
 *
 * @param list<DocumentationPage> $topics
 */
function addTopicsMenuToSite(string $siteDir, array $topics): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($siteDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || strtolower($file->getExtension()) !== 'html') {
            continue;
        }

        $path = $file->getPathname();
        $html = readFileOrFail($path, 'generated documentation HTML page');
        $sitePath = relativePath($siteDir, $path);
        $html = withGeneratedSidebarSections($html, $sitePath);
        $html = withTopicsMenu($html, $topics, $sitePath);

        writeFileOrFail($path, $html, 'generated documentation HTML page');
    }
}

/**
 * Repairs generated right-sidebar entries for sections phpDocumentor rendered.
 */
function withGeneratedSidebarSections(string $html, string $sitePath): string
{
    if (!str_contains($html, 'id="namespaces"')) {
        return $html;
    }

    $href = htmlspecialchars($sitePath . '#namespaces', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $entry = '                    <li><a href="' . $href . '">Namespaces</a></li>' . "\n";

    if (str_contains($html, trim($entry))) {
        return $html;
    }

    $updated = preg_replace(
        '~(<li class="phpdocumentor-on-this-page-section__title">Table Of Contents</li>\s*<li>\s*<ul class="phpdocumentor-list -clean">\s*)~',
        '$1' . $entry,
        $html,
        1,
        $count
    );

    if ($updated === null || $count !== 1) {
        fail("Unable to add generated documentation sidebar section: {$sitePath}");
    }

    return $updated;
}

/**
 * Adds the Topics menu to one generated HTML page.
 *
 * @param list<DocumentationPage> $topics
 */
function withTopicsMenu(string $html, array $topics, string $currentSitePath): string
{
    $html = preg_replace(
        '~\s*<section class="phpdocumentor-sidebar__category -topics">.*?</section>~s',
        '',
        $html,
        1
    );

    if ($html === null) {
        fail('Unable to remove existing documentation topics menu.');
    }

    if ($topics === []) {
        return $html;
    }

    $menu = topicsMenu($topics, $currentSitePath);
    $updated = preg_replace(
        '~(<aside class="phpdocumentor-column -three phpdocumentor-sidebar">\s*)~',
        "$1{$menu}",
        $html,
        1,
        $count
    );

    if ($updated === null || $count !== 1) {
        fail("Unable to add documentation topics menu: {$currentSitePath}");
    }

    return $updated;
}

/**
 * Builds the Topics sidebar section.
 *
 * @param list<DocumentationPage> $topics
 */
function topicsMenu(array $topics, string $currentSitePath): string
{
    $items = '';

    foreach ($topics as $topic) {
        $active = $topic->sitePath === $currentSitePath ? 'active' : '';
        $items .= sprintf(
            '        <h3 class="phpdocumentor-sidebar__root-package"><a href="%s" class="%s">%s</a></h3>' . "\n",
            htmlspecialchars($topic->sitePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $active,
            htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }

    return <<<HTML
    <section class="phpdocumentor-sidebar__category -topics">
        <h2 class="phpdocumentor-sidebar__category-header">Topics</h2>
{$items}    </section>

HTML;
}

/**
 * Copies existing project files linked from rendered documentation into the site.
 */
function copyLinkedFiles(string $html, string $projectDir, string $siteDir): void
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

        copyFileOrFail($sourcePath, $targetPath, 'documentation linked file');
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
$siteDir = $projectDir . '/.site';
$indexPath = $siteDir . '/index.html';
$topicsDir = $projectDir . '/docs/topics';

if (in_array('--build', $argv, true)) {
    runPhpDocumentor($projectDir);
}

$shell = withCustomStylesheet(readFileOrFail($indexPath, 'generated documentation index'));
$converter = new CommonMarkConverter([
    'html_input' => 'allow',
    'allow_unsafe_links' => false,
]);

copyStaticAssets($projectDir, $siteDir);
clearGeneratedTopics($siteDir);

$homePage = renderMarkdownPage($converter, $projectDir . '/docs/index.md', 'index.html', 'index.html');
$topicPages = [];
$topicSitePaths = [];

foreach (topicPaths($topicsDir) as $topicPath) {
    $filename = pathinfo($topicPath, PATHINFO_FILENAME) . '.html';
    $sitePath = 'topics/' . $filename;

    if (isset($topicSitePaths[$sitePath])) {
        fail("Duplicate documentation topic output path: {$sitePath}");
    }

    $topicSitePaths[$sitePath] = true;
    $topicPages[] = renderMarkdownPage(
        $converter,
        $topicPath,
        $sitePath,
        $sitePath
    );
}

writeFileOrFail($indexPath, renderPageShell($shell, $homePage), 'generated documentation homepage');
copyLinkedFiles($homePage->html, $projectDir, $siteDir);

foreach ($topicPages as $topicPage) {
    writeFileOrFail(
        $siteDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $topicPage->sitePath),
        renderPageShell($shell, $topicPage),
        'generated documentation topic'
    );
    copyLinkedFiles($topicPage->html, $projectDir, $siteDir);
}

addTopicsMenuToSite($siteDir, $topicPages);
