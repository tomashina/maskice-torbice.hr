#!/usr/bin/env php
<?php

/**
 * Transactional OpenCart 3.0.3.8 OCMOD package deployer.
 *
 * PHP 7.3 compatible. The package argument points to an extracted OCMOD
 * directory containing install.xml and upload/. No credentials are logged.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This installer may only be run from the command line.\n");
    exit(64);
}

if (PHP_VERSION_ID < 70300) {
    fwrite(STDERR, "PHP 7.3 or newer is required.\n");
    exit(64);
}

final class OcmodDeployException extends RuntimeException
{
}

final class OcmodBuilder
{
    private $root;
    private $overlay;
    private $log = array();
    private $operationCount = 0;
    private $matchedOperationCount = 0;

    public function __construct($root, array $overlay)
    {
        $this->root = rtrim($root, '/');
        $this->overlay = $overlay;
    }

    public function build(array $xmlSources)
    {
        $modified = array();
        $original = array();
        $abortAll = false;

        foreach ($xmlSources as $xmlSource) {
            if ($xmlSource['xml'] === '') {
                continue;
            }

            $dom = $this->loadXml($xmlSource['xml'], $xmlSource['label']);
            $rootNode = $dom->documentElement;

            if (!$rootNode || $rootNode->nodeName !== 'modification') {
                throw new OcmodDeployException('Invalid OCMOD root element in ' . $xmlSource['label'] . '.');
            }

            $nameNode = $rootNode->getElementsByTagName('name')->item(0);
            $modName = $nameNode ? $this->safeLabel($nameNode->textContent) : $xmlSource['label'];
            $this->log[] = 'MOD: ' . $modName;
            $recovery = $modified;

            $fileNodes = $rootNode->getElementsByTagName('file');

            foreach ($fileNodes as $fileNode) {
                $operationNodes = $fileNode->getElementsByTagName('operation');
                $patterns = explode('|', str_replace('\\', '/', $fileNode->getAttribute('path')));

                foreach ($patterns as $pattern) {
                    $relativePattern = $this->validateTargetPattern(trim($pattern));

                    if ($relativePattern === null) {
                        continue;
                    }

                    $matches = $this->findMatches($relativePattern);

                    foreach ($matches as $relativeFile) {
                        if (!array_key_exists($relativeFile, $modified)) {
                            $content = $this->readSource($relativeFile);
                            $content = preg_replace('~\r?\n~', "\n", $content);
                            $modified[$relativeFile] = $content;
                            $original[$relativeFile] = $content;
                            $this->log[] = 'FILE: ' . $relativeFile;
                        } else {
                            $this->log[] = 'FILE: (sub modification) ' . $relativeFile;
                        }

                        $result = $this->applyOperations($modified[$relativeFile], $operationNodes, $relativeFile);

                        if ($result === 'abort') {
                            $modified = $recovery;
                            $this->log[] = 'NOT FOUND - ABORTING MODIFICATION BUILD';
                            $abortAll = true;
                            break;
                        }
                    }

                    if ($abortAll) {
                        break;
                    }
                }

                if ($abortAll) {
                    break;
                }
            }

            $this->log[] = str_repeat('-', 64);

            if ($abortAll) {
                break;
            }
        }

        $changed = array();

        foreach ($modified as $relativeFile => $content) {
            if (isset($original[$relativeFile]) && $original[$relativeFile] !== $content) {
                $changed[$relativeFile] = $content;
            }
        }

        ksort($changed);

        return array(
            'files' => $changed,
            'log' => $this->log,
            'operations' => $this->operationCount,
            'matched_operations' => $this->matchedOperationCount,
            'aborted' => $abortAll
        );
    }

    private function loadXml($xml, $label)
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $loaded = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            throw new OcmodDeployException('Malformed OCMOD XML in ' . $label . '.');
        }

        return $dom;
    }

    private function validateTargetPattern($pattern)
    {
        if ($pattern === '') {
            return null;
        }

        if (strpos($pattern, "\0") !== false || substr($pattern, 0, 1) === '/') {
            throw new OcmodDeployException('Unsafe OCMOD file path.');
        }

        foreach (explode('/', $pattern) as $part) {
            if ($part === '..') {
                throw new OcmodDeployException('OCMOD file path may not leave the OpenCart root.');
            }
        }

        if (strpos($pattern, 'catalog/') === 0 || strpos($pattern, 'admin/') === 0 || strpos($pattern, 'system/') === 0) {
            return $pattern;
        }

        return null;
    }

    private function findMatches($relativePattern)
    {
        $matches = array();
        $diskMatches = glob($this->root . '/' . $relativePattern, GLOB_BRACE);

        if ($diskMatches !== false) {
            foreach ($diskMatches as $diskPath) {
                if (!is_file($diskPath) || is_link($diskPath)) {
                    continue;
                }

                $real = realpath($diskPath);

                if ($real === false || !$this->isWithin($real, $this->root)) {
                    throw new OcmodDeployException('OCMOD matched a file outside the OpenCart root.');
                }

                $relative = ltrim(substr(str_replace('\\', '/', $real), strlen(str_replace('\\', '/', $this->root))), '/');
                $matches[$relative] = true;
            }
        }

        $expandedPatterns = $this->expandBraces($relativePattern);

        foreach ($this->overlay as $relative => $source) {
            foreach ($expandedPatterns as $expandedPattern) {
                if (fnmatch($expandedPattern, $relative, FNM_PATHNAME)) {
                    $matches[$relative] = true;
                    break;
                }
            }
        }

        $result = array_keys($matches);
        sort($result, SORT_STRING);

        return $result;
    }

    private function expandBraces($pattern)
    {
        if (!preg_match('/^(.*?)\{([^{}]+)\}(.*)$/', $pattern, $match)) {
            return array($pattern);
        }

        $expanded = array();

        foreach (explode(',', $match[2]) as $choice) {
            foreach ($this->expandBraces($match[1] . $choice . $match[3]) as $item) {
                $expanded[] = $item;
            }
        }

        return $expanded;
    }

    private function readSource($relativeFile)
    {
        if (isset($this->overlay[$relativeFile])) {
            $content = file_get_contents($this->overlay[$relativeFile]);
        } else {
            $content = file_get_contents($this->root . '/' . $relativeFile);
        }

        if ($content === false) {
            throw new OcmodDeployException('Unable to read an OCMOD source file.');
        }

        return $content;
    }

    private function applyOperations(&$content, DOMNodeList $operations, $relativeFile)
    {
        foreach ($operations as $operation) {
            $this->operationCount++;
            $errorMode = strtolower(trim($operation->getAttribute('error')));
            $ignoreIf = $operation->getElementsByTagName('ignoreif')->item(0);

            if ($ignoreIf) {
                if ($ignoreIf->getAttribute('regex') !== 'true') {
                    if (strpos($content, $ignoreIf->textContent) !== false) {
                        $this->log[] = 'IGNORED: ' . $relativeFile;
                        continue;
                    }
                } else {
                    $ignored = @preg_match($ignoreIf->textContent, $content);

                    if ($ignored === false) {
                        throw new OcmodDeployException('Invalid ignoreif regular expression.');
                    }

                    if ($ignored === 1) {
                        $this->log[] = 'IGNORED: ' . $relativeFile;
                        continue;
                    }
                }
            }

            $searchNode = $operation->getElementsByTagName('search')->item(0);
            $addNode = $operation->getElementsByTagName('add')->item(0);

            if (!$searchNode || !$addNode) {
                throw new OcmodDeployException('OCMOD operation is missing search or add.');
            }

            if ($searchNode->getAttribute('regex') === 'true') {
                $matched = $this->applyRegexOperation($content, $searchNode, $addNode, $relativeFile);
            } else {
                $matched = $this->applyLiteralOperation($content, $searchNode, $addNode, $relativeFile);
            }

            if ($matched) {
                $this->matchedOperationCount++;
                continue;
            }

            if ($errorMode === 'abort') {
                return 'abort';
            }

            if ($errorMode === 'skip') {
                $this->log[] = 'NOT FOUND - OPERATION SKIPPED: ' . $relativeFile;
                continue;
            }

            $this->log[] = 'NOT FOUND - OPERATIONS STOPPED: ' . $relativeFile;
            break;
        }

        return 'continue';
    }

    private function applyLiteralOperation(&$content, DOMElement $searchNode, DOMElement $addNode, $relativeFile)
    {
        $search = $searchNode->textContent;
        $searchTrim = $searchNode->getAttribute('trim');
        $index = $searchNode->getAttribute('index');

        if ($searchTrim === '' || $searchTrim === 'true') {
            $search = trim($search);
        }

        $add = $addNode->textContent;

        if ($addNode->getAttribute('trim') === 'true') {
            $add = trim($add);
        }

        $position = $addNode->getAttribute('position');
        $offset = $addNode->getAttribute('offset');
        $offset = ($offset === '') ? 0 : (int)$offset;
        $indexes = ($index === '') ? array() : explode(',', $index);
        $lines = explode("\n", $content);
        $matchIndex = 0;
        $status = false;

        for ($lineId = 0; $lineId < count($lines); $lineId++) {
            $line = $lines[$lineId];
            $match = false;

            if (stripos($line, $search) !== false) {
                if (!$indexes || in_array($matchIndex, $indexes, false)) {
                    $match = true;
                }

                $matchIndex++;
            }

            if (!$match) {
                continue;
            }

            if ($position === 'before') {
                $newLines = explode("\n", $add);
                array_splice($lines, $lineId - $offset, 0, $newLines);
                $lineId += count($newLines);
            } elseif ($position === 'after') {
                $newLines = explode("\n", $add);
                array_splice($lines, ($lineId + 1) + $offset, 0, $newLines);
                $lineId += count($newLines);
            } else {
                if ($offset < 0) {
                    array_splice($lines, $lineId + $offset, abs($offset) + 1, array(str_replace($search, $add, $line)));
                    $lineId -= $offset;
                } else {
                    array_splice($lines, $lineId, $offset + 1, array(str_replace($search, $add, $line)));
                }
            }

            $this->log[] = 'MATCH: ' . $relativeFile . ':' . ($lineId + 1);
            $status = true;
        }

        $content = implode("\n", $lines);

        return $status;
    }

    private function applyRegexOperation(&$content, DOMElement $searchNode, DOMElement $addNode, $relativeFile)
    {
        $search = trim($searchNode->textContent);
        $replace = trim($addNode->textContent);
        $limitAttribute = $searchNode->getAttribute('limit');
        $limit = ($limitAttribute === '' || (int)$limitAttribute === 0) ? -1 : (int)$limitAttribute;
        $matches = array();
        $matchCount = @preg_match_all($search, $content, $matches, PREG_OFFSET_CAPTURE);

        if ($matchCount === false) {
            throw new OcmodDeployException('Invalid OCMOD search regular expression.');
        }

        if ($limit > 0 && isset($matches[0])) {
            $matches[0] = array_slice($matches[0], 0, $limit);
        }

        $status = !empty($matches[0]);

        if ($status) {
            foreach ($matches[0] as $match) {
                $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                $this->log[] = 'REGEX MATCH: ' . $relativeFile . ':' . $line;
            }
        }

        $replaced = @preg_replace($search, $replace, $content, $limit);

        if ($replaced === null) {
            throw new OcmodDeployException('OCMOD regular expression replacement failed.');
        }

        $content = $replaced;

        return $status;
    }

    private function safeLabel($value)
    {
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$value);
        return substr(trim($value), 0, 160);
    }

    private function isWithin($path, $root)
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        return $path === $root || strpos($path . '/', $root . '/') === 0;
    }
}

function ocmod_usage()
{
    $script = basename($_SERVER['argv'][0]);
    $text = <<<TXT
Usage:
  php {$script} --root=/absolute/opencart-root --package=/absolute/package-dir [--backup-base=/absolute/path] [--dry-run]

The package directory must contain install.xml and upload/.
TXT;
    fwrite(STDERR, $text . "\n");
}

function ocmod_parse_arguments(array $argv)
{
    $options = array(
        'root' => null,
        'package' => null,
        'backup-base' => null,
        'dry-run' => false
    );

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--dry-run') {
            $options['dry-run'] = true;
        } elseif ($argument === '--help' || $argument === '-h') {
            ocmod_usage();
            exit(0);
        } elseif (strpos($argument, '--root=') === 0) {
            $options['root'] = substr($argument, 7);
        } elseif (strpos($argument, '--package=') === 0) {
            $options['package'] = substr($argument, 10);
        } elseif (strpos($argument, '--backup-base=') === 0) {
            $options['backup-base'] = substr($argument, 14);
        } else {
            throw new OcmodDeployException('Unknown or malformed argument.');
        }
    }

    if (!$options['root'] || !$options['package']) {
        throw new OcmodDeployException('Both --root and --package are required.');
    }

    return $options;
}

function ocmod_real_directory($path, $label)
{
    if (!is_string($path) || $path === '' || substr($path, 0, 1) !== '/') {
        throw new OcmodDeployException($label . ' must be an absolute path.');
    }

    $real = realpath($path);

    if ($real === false || !is_dir($real) || is_link($path)) {
        throw new OcmodDeployException($label . ' is not a valid directory.');
    }

    return rtrim(str_replace('\\', '/', $real), '/');
}

function ocmod_normalize_new_path($path, $label)
{
    if (!is_string($path) || $path === '' || substr($path, 0, 1) !== '/') {
        throw new OcmodDeployException($label . ' must be an absolute path.');
    }

    $parts = array();

    foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }

        if ($part === '..') {
            if (!$parts) {
                throw new OcmodDeployException($label . ' is unsafe.');
            }

            array_pop($parts);
        } else {
            $parts[] = $part;
        }
    }

    return '/' . implode('/', $parts);
}

function ocmod_resolve_future_path($path, $label)
{
    $path = ocmod_normalize_new_path($path, $label);
    $cursor = $path;
    $tail = array();

    while (!file_exists($cursor) && !is_link($cursor)) {
        $parent = dirname($cursor);

        if ($parent === $cursor) {
            throw new OcmodDeployException($label . ' has no valid parent directory.');
        }

        array_unshift($tail, basename($cursor));
        $cursor = $parent;
    }

    $real = realpath($cursor);

    if ($real === false || !is_dir($real)) {
        throw new OcmodDeployException($label . ' has an invalid parent directory.');
    }

    $resolved = rtrim(str_replace('\\', '/', $real), '/');

    foreach ($tail as $part) {
        $resolved .= '/' . $part;
    }

    return $resolved;
}

function ocmod_is_within($path, $root)
{
    $path = rtrim(str_replace('\\', '/', $path), '/');
    $root = rtrim(str_replace('\\', '/', $root), '/');
    return $path === $root || strpos($path . '/', $root . '/') === 0;
}

function ocmod_validate_root($root)
{
    $requiredFiles = array('config.php', 'admin/config.php', 'index.php', 'system/modification.xml');
    $requiredDirectories = array('admin', 'catalog', 'system');

    foreach ($requiredFiles as $relative) {
        if (!is_file($root . '/' . $relative) || is_link($root . '/' . $relative)) {
            throw new OcmodDeployException('OpenCart root is missing ' . $relative . '.');
        }
    }

    foreach ($requiredDirectories as $relative) {
        if (!is_dir($root . '/' . $relative) || is_link($root . '/' . $relative)) {
            throw new OcmodDeployException('OpenCart root is missing ' . $relative . '/.');
        }
    }
}

function ocmod_load_config($root)
{
    $previousHandler = set_error_handler(function () {
        throw new OcmodDeployException('Unable to load the OpenCart configuration.');
    });

    ob_start();

    try {
        require $root . '/config.php';
    } finally {
        ob_end_clean();
        restore_error_handler();
    }

    $required = array(
        'DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PREFIX',
        'DIR_MODIFICATION', 'DIR_CACHE'
    );

    foreach ($required as $constant) {
        if (!defined($constant)) {
            throw new OcmodDeployException('OpenCart configuration is missing a required constant.');
        }
    }

    if (!preg_match('/^[A-Za-z0-9_]*$/', DB_PREFIX)) {
        throw new OcmodDeployException('The configured database prefix is unsafe.');
    }

    $modification = ocmod_normalize_new_path(DIR_MODIFICATION, 'DIR_MODIFICATION');
    $cache = ocmod_normalize_new_path(DIR_CACHE, 'DIR_CACHE');

    if ($modification === '/' || $cache === '/' || $modification === $cache) {
        throw new OcmodDeployException('OpenCart cache paths are unsafe.');
    }

    return array(
        'modification' => $modification,
        'cache' => $cache,
        'twig' => rtrim($cache, '/') . '/template'
    );
}

function ocmod_load_package($package)
{
    $installXmlPath = $package . '/install.xml';
    $uploadPath = $package . '/upload';

    if (!is_file($installXmlPath) || is_link($installXmlPath)) {
        throw new OcmodDeployException('Package install.xml is missing or unsafe.');
    }

    if (!is_dir($uploadPath) || is_link($uploadPath)) {
        throw new OcmodDeployException('Package upload/ directory is missing or unsafe.');
    }

    $xml = file_get_contents($installXmlPath);

    if ($xml === false || $xml === '') {
        throw new OcmodDeployException('Package install.xml cannot be read.');
    }

    $previous = libxml_use_internal_errors(true);
    libxml_clear_errors();
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $loaded = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$dom->documentElement || $dom->documentElement->nodeName !== 'modification') {
        throw new OcmodDeployException('Package install.xml is not valid OCMOD XML.');
    }

    $metadata = array();

    foreach (array('name', 'code', 'author', 'version', 'link') as $field) {
        $node = $dom->documentElement->getElementsByTagName($field)->item(0);
        $metadata[$field] = $node ? trim($node->textContent) : '';
    }

    if ($metadata['name'] === '' || $metadata['code'] === '') {
        throw new OcmodDeployException('Package name and code are required.');
    }

    $limits = array('name' => 64, 'code' => 64, 'author' => 64, 'version' => 32, 'link' => 255);

    foreach ($limits as $field => $limit) {
        if (strlen($metadata[$field]) > $limit) {
            throw new OcmodDeployException('Package metadata exceeds the OpenCart database limit.');
        }
    }

    $files = ocmod_scan_upload($uploadPath);

    return array(
        'xml' => $xml,
        'metadata' => $metadata,
        'files' => $files,
        'upload' => $uploadPath
    );
}

function ocmod_scan_upload($uploadPath)
{
    $files = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $pathname = str_replace('\\', '/', $item->getPathname());
        $relative = ltrim(substr($pathname, strlen(str_replace('\\', '/', $uploadPath))), '/');

        if ($item->isLink()) {
            throw new OcmodDeployException('Package upload/ may not contain symbolic links.');
        }

        if ($item->isDir()) {
            continue;
        }

        if (!$item->isFile() || $relative === '') {
            throw new OcmodDeployException('Package upload/ contains an unsupported entry.');
        }

        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                throw new OcmodDeployException('Package upload/ contains an unsafe path.');
            }
        }

        if (ocmod_is_protected_upload_path($relative)) {
            throw new OcmodDeployException('Package upload/ contains a protected OpenCart path.');
        }

        $files[$relative] = $pathname;
    }

    ksort($files);

    return $files;
}

function ocmod_is_protected_upload_path($relative)
{
    if (in_array($relative, array('config.php', 'admin/config.php', 'php.ini', '.user.ini', '.htpasswd', '.env'), true)) {
        return true;
    }

    if (strpos($relative, '.env.') === 0 || strpos($relative, 'storage/') === 0 || $relative === 'storage') {
        return true;
    }

    $protectedPrefixes = array(
        'system/storage/cache/',
        'system/storage/download/',
        'system/storage/logs/',
        'system/storage/modification/',
        'system/storage/session/',
        'system/storage/upload/',
        'image/cache/'
    );

    foreach ($protectedPrefixes as $prefix) {
        if (strpos($relative, $prefix) === 0 || $relative === rtrim($prefix, '/')) {
            return true;
        }
    }

    return false;
}

function ocmod_connect_database()
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $port = defined('DB_PORT') && (int)DB_PORT > 0 ? (int)DB_PORT : 3306;
    $socket = defined('DB_SOCKET') && DB_SOCKET !== '' ? DB_SOCKET : null;
    $db = mysqli_init();

    if (!$db || !@$db->real_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port, $socket)) {
        throw new OcmodDeployException('Database connection failed.');
    }

    if (!$db->set_charset('utf8')) {
        $db->close();
        throw new OcmodDeployException('Unable to set the database character set.');
    }

    return $db;
}

function ocmod_fetch_modifications(mysqli $db)
{
    $table = '`' . DB_PREFIX . 'modification`';
    $result = $db->query('SELECT modification_id, extension_install_id, name, code, author, version, link, xml, status, date_added FROM ' . $table . ' ORDER BY name');

    if (!$result) {
        throw new OcmodDeployException('Unable to read OpenCart modifications.');
    }

    $rows = array();

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $result->free();

    return $rows;
}

function ocmod_simulate_upsert(array $rows, array $package)
{
    $found = false;

    foreach ($rows as &$row) {
        if ($row['code'] === $package['metadata']['code']) {
            if ($found) {
                throw new OcmodDeployException('Multiple database modifications use the package code.');
            }

            $found = true;
            $row['name'] = $package['metadata']['name'];
            $row['author'] = $package['metadata']['author'];
            $row['version'] = $package['metadata']['version'];
            $row['link'] = $package['metadata']['link'];
            $row['xml'] = $package['xml'];
            $row['status'] = 1;
        }
    }
    unset($row);

    if (!$found) {
        $rows[] = array(
            'modification_id' => 0,
            'extension_install_id' => 0,
            'name' => $package['metadata']['name'],
            'code' => $package['metadata']['code'],
            'author' => $package['metadata']['author'],
            'version' => $package['metadata']['version'],
            'link' => $package['metadata']['link'],
            'xml' => $package['xml'],
            'status' => 1,
            'date_added' => ''
        );
    }

    usort($rows, function ($left, $right) {
        $name = strcasecmp($left['name'], $right['name']);
        if ($name !== 0) {
            return $name;
        }
        return (int)$left['modification_id'] - (int)$right['modification_id'];
    });

    return $rows;
}

function ocmod_collect_xml_sources($root, array $rows)
{
    $sources = array();
    $defaultPath = $root . '/system/modification.xml';
    $defaultXml = file_get_contents($defaultPath);

    if ($defaultXml === false) {
        throw new OcmodDeployException('Unable to read system/modification.xml.');
    }

    $sources[] = array('label' => 'system/modification.xml', 'xml' => $defaultXml);
    $developerFiles = glob($root . '/system/*.ocmod.xml');

    if ($developerFiles !== false) {
        sort($developerFiles, SORT_STRING);

        foreach ($developerFiles as $file) {
            if (!is_file($file) || is_link($file)) {
                throw new OcmodDeployException('Unsafe system OCMOD XML file.');
            }

            $xml = file_get_contents($file);

            if ($xml === false) {
                throw new OcmodDeployException('Unable to read a system OCMOD XML file.');
            }

            $sources[] = array('label' => basename($file), 'xml' => $xml);
        }
    }

    foreach ($rows as $row) {
        if ((int)$row['status'] === 1) {
            $label = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$row['name']);
            $sources[] = array('label' => substr(trim($label), 0, 160), 'xml' => (string)$row['xml']);
        }
    }

    return $sources;
}

function ocmod_plan_upload($root, array $files)
{
    $plan = array('new' => 0, 'replace' => 0, 'unchanged' => 0);

    foreach ($files as $relative => $source) {
        $destination = $root . '/' . $relative;
        ocmod_assert_safe_destination($root, $relative, false);

        if (is_link($destination) || (file_exists($destination) && !is_file($destination))) {
            throw new OcmodDeployException('Package destination is not a regular file.');
        }

        if (!file_exists($destination)) {
            $plan['new']++;
        } elseif (hash_file('sha256', $source) === hash_file('sha256', $destination)) {
            $plan['unchanged']++;
        } else {
            $plan['replace']++;
        }
    }

    return $plan;
}

function ocmod_assert_safe_destination($root, $relative, $create, ?array &$createdDirectories = null)
{
    $parts = explode('/', $relative);
    array_pop($parts);
    $cursor = $root;

    foreach ($parts as $part) {
        if ($part === '' || $part === '.' || $part === '..') {
            throw new OcmodDeployException('Unsafe package destination path.');
        }

        $cursor .= '/' . $part;

        if (is_link($cursor)) {
            throw new OcmodDeployException('Refusing to traverse a live symbolic link.');
        }

        if (file_exists($cursor)) {
            if (!is_dir($cursor)) {
                throw new OcmodDeployException('Package destination parent is not a directory.');
            }
        } elseif ($create) {
            if (!mkdir($cursor, 0755)) {
                throw new OcmodDeployException('Unable to create a package destination directory.');
            }
            $createdDirectories[] = $cursor;
        }
    }
}

function ocmod_make_directory($path, $mode)
{
    if (is_dir($path)) {
        if (is_link($path)) {
            throw new OcmodDeployException('Refusing a symbolic-link directory.');
        }
        return;
    }

    if (file_exists($path) || !mkdir($path, $mode, true)) {
        throw new OcmodDeployException('Unable to create a required directory.');
    }
}

function ocmod_copy_file_preserve($source, $destination)
{
    if (!is_file($source) || is_link($source)) {
        throw new OcmodDeployException('Copy source is not a safe regular file.');
    }

    ocmod_make_directory(dirname($destination), 0750);

    if (!copy($source, $destination)) {
        throw new OcmodDeployException('Unable to copy a backup file.');
    }

    @chmod($destination, fileperms($source) & 0777);
    @touch($destination, filemtime($source));
}

function ocmod_atomic_copy($source, $destination, $id)
{
    $parent = dirname($destination);
    ocmod_make_directory($parent, 0755);
    $temporary = $parent . '/.' . basename($destination) . '.ocmod-' . $id . '.tmp';

    if (file_exists($temporary) || is_link($temporary)) {
        throw new OcmodDeployException('Temporary deployment path already exists.');
    }

    if (!copy($source, $temporary)) {
        throw new OcmodDeployException('Unable to stage an upload file.');
    }

    @chmod($temporary, fileperms($source) & 0777);
    @touch($temporary, filemtime($source));

    if (!rename($temporary, $destination)) {
        @unlink($temporary);
        throw new OcmodDeployException('Unable to atomically install an upload file.');
    }
}

function ocmod_install_upload($root, array $files, $runBackup, $id, array &$state)
{
    foreach ($files as $relative => $source) {
        $destination = $root . '/' . $relative;
        ocmod_assert_safe_destination($root, $relative, true, $state['created_directories']);

        if (is_link($destination) || (file_exists($destination) && !is_file($destination))) {
            throw new OcmodDeployException('Package destination is not a safe regular file.');
        }

        if (is_file($destination) && hash_file('sha256', $source) === hash_file('sha256', $destination)) {
            continue;
        }

        $entry = array('destination' => $destination, 'backup' => null, 'existed' => is_file($destination));

        if ($entry['existed']) {
            $entry['backup'] = $runBackup . '/upload-before/' . $relative;
            ocmod_copy_file_preserve($destination, $entry['backup']);
        }

        $state['upload'][] = $entry;
        ocmod_atomic_copy($source, $destination, $id);
    }
}

function ocmod_upsert_modification(mysqli $db, array $package)
{
    $table = '`' . DB_PREFIX . 'modification`';
    $code = $package['metadata']['code'];
    $statement = $db->prepare('SELECT modification_id, extension_install_id, name, code, author, version, link, xml, status, date_added FROM ' . $table . ' WHERE code = ? FOR UPDATE');

    if (!$statement) {
        throw new OcmodDeployException('Unable to prepare the modification lookup.');
    }

    $statement->bind_param('s', $code);

    if (!$statement->execute()) {
        $statement->close();
        throw new OcmodDeployException('Unable to query the package modification.');
    }

    $statement->bind_result($id, $extensionInstallId, $name, $storedCode, $author, $version, $link, $xml, $status, $dateAdded);
    $rows = array();

    while ($statement->fetch()) {
        $rows[] = array(
            'modification_id' => $id,
            'extension_install_id' => $extensionInstallId,
            'name' => $name,
            'code' => $storedCode,
            'author' => $author,
            'version' => $version,
            'link' => $link,
            'xml' => $xml,
            'status' => $status,
            'date_added' => $dateAdded
        );
    }
    $statement->close();

    if (count($rows) > 1) {
        throw new OcmodDeployException('Multiple database modifications use the package code.');
    }

    $metadata = $package['metadata'];

    if ($rows) {
        $snapshot = array('existed' => true, 'row' => $rows[0], 'insert_id' => null);
        $statement = $db->prepare('UPDATE ' . $table . ' SET name = ?, code = ?, author = ?, version = ?, link = ?, xml = ?, status = 1 WHERE modification_id = ?');

        if (!$statement) {
            throw new OcmodDeployException('Unable to prepare the modification update.');
        }

        $statement->bind_param('ssssssi', $metadata['name'], $metadata['code'], $metadata['author'], $metadata['version'], $metadata['link'], $package['xml'], $rows[0]['modification_id']);
    } else {
        $snapshot = array('existed' => false, 'row' => null, 'insert_id' => null);
        $statement = $db->prepare('INSERT INTO ' . $table . ' (extension_install_id, name, code, author, version, link, xml, status, date_added) VALUES (0, ?, ?, ?, ?, ?, ?, 1, NOW())');

        if (!$statement) {
            throw new OcmodDeployException('Unable to prepare the modification insert.');
        }

        $statement->bind_param('ssssss', $metadata['name'], $metadata['code'], $metadata['author'], $metadata['version'], $metadata['link'], $package['xml']);
    }

    if (!$statement->execute()) {
        $statement->close();
        throw new OcmodDeployException('Unable to save the package modification.');
    }

    if (!$snapshot['existed']) {
        $snapshot['insert_id'] = $db->insert_id;
    }

    $statement->close();

    return $snapshot;
}

function ocmod_restore_database_snapshot(mysqli $db, array $snapshot)
{
    $table = '`' . DB_PREFIX . 'modification`';

    if (!$snapshot['existed']) {
        if (!$snapshot['insert_id']) {
            return;
        }

        $statement = $db->prepare('DELETE FROM ' . $table . ' WHERE modification_id = ?');
        $id = (int)$snapshot['insert_id'];
        if (!$statement) {
            throw new OcmodDeployException('Unable to prepare database rollback.');
        }
        $statement->bind_param('i', $id);
    } else {
        $row = $snapshot['row'];
        $statement = $db->prepare('REPLACE INTO ' . $table . ' (modification_id, extension_install_id, name, code, author, version, link, xml, status, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$statement) {
            throw new OcmodDeployException('Unable to prepare database rollback.');
        }
        $statement->bind_param(
            'iissssssis',
            $row['modification_id'],
            $row['extension_install_id'],
            $row['name'],
            $row['code'],
            $row['author'],
            $row['version'],
            $row['link'],
            $row['xml'],
            $row['status'],
            $row['date_added']
        );
    }

    if (!$statement->execute()) {
        $statement->close();
        throw new OcmodDeployException('Database rollback failed.');
    }

    $statement->close();
}

function ocmod_copy_tree($source, $destination)
{
    if (!is_dir($source) || is_link($source)) {
        throw new OcmodDeployException('Backup source directory is unsafe.');
    }

    ocmod_make_directory($destination, 0750);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isLink()) {
            throw new OcmodDeployException('Cache backup encountered a symbolic link.');
        }

        $relative = ltrim(substr(str_replace('\\', '/', $item->getPathname()), strlen(str_replace('\\', '/', $source))), '/');
        $target = $destination . '/' . $relative;

        if ($item->isDir()) {
            ocmod_make_directory($target, 0750);
        } elseif ($item->isFile()) {
            ocmod_copy_file_preserve($item->getPathname(), $target);
        } else {
            throw new OcmodDeployException('Cache backup encountered an unsupported entry.');
        }
    }
}

function ocmod_remove_tree($path, $allowedParent)
{
    $path = rtrim(str_replace('\\', '/', $path), '/');
    $allowedParent = rtrim(str_replace('\\', '/', $allowedParent), '/');

    if ($path === '' || $path === '/' || !ocmod_is_within($path, $allowedParent) || $path === $allowedParent) {
        throw new OcmodDeployException('Refusing to remove an unsafe path.');
    }

    if (is_link($path) || is_file($path)) {
        if (!unlink($path)) {
            throw new OcmodDeployException('Unable to remove a temporary file.');
        }
        return;
    }

    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir() && !$item->isLink()) {
            if (!rmdir($item->getPathname())) {
                throw new OcmodDeployException('Unable to remove a temporary directory.');
            }
        } else {
            if (!unlink($item->getPathname())) {
                throw new OcmodDeployException('Unable to remove a temporary cache file.');
            }
        }
    }

    if (!rmdir($path)) {
        throw new OcmodDeployException('Unable to remove a temporary directory.');
    }
}

function ocmod_write_cache_tree($path, array $build)
{
    ocmod_make_directory($path, 0755);
    $index = $path . '/index.html';

    if (file_put_contents($index, '<!doctype html><title></title>', LOCK_EX) === false) {
        throw new OcmodDeployException('Unable to create the modification cache index.');
    }
    @chmod($index, 0644);

    foreach ($build['files'] as $relative => $content) {
        if (!preg_match('~^(admin|catalog|system)/~', $relative) || strpos('/' . $relative . '/', '/../') !== false) {
            throw new OcmodDeployException('Unsafe generated modification path.');
        }

        $destination = $path . '/' . $relative;
        ocmod_make_directory(dirname($destination), 0755);

        if (file_put_contents($destination, $content, LOCK_EX) === false) {
            throw new OcmodDeployException('Unable to write a generated modification file.');
        }
        @chmod($destination, 0644);
    }
}

function ocmod_swap_directory($live, $prepared, $runBackup, $backupName, $id)
{
    $parent = dirname($live);
    ocmod_make_directory($parent, 0755);

    if (is_link($live) || (file_exists($live) && !is_dir($live))) {
        throw new OcmodDeployException('Live cache path is unsafe.');
    }

    $old = $parent . '/.' . basename($live) . '.ocmod-old-' . $id;

    if (file_exists($old) || is_link($old)) {
        throw new OcmodDeployException('Cache rollback path already exists.');
    }

    $hadLive = is_dir($live);

    if ($hadLive) {
        ocmod_copy_tree($live, $runBackup . '/' . $backupName);

        if (!rename($live, $old)) {
            throw new OcmodDeployException('Unable to stage the previous cache directory.');
        }
    }

    if (!rename($prepared, $live)) {
        if ($hadLive) {
            @rename($old, $live);
        }
        throw new OcmodDeployException('Unable to activate the prepared cache directory.');
    }

    return array('live' => $live, 'old' => $old, 'had_live' => $hadLive, 'parent' => $parent);
}

function ocmod_rollback_swap(array $swap)
{
    if (is_dir($swap['live']) && !is_link($swap['live'])) {
        ocmod_remove_tree($swap['live'], $swap['parent']);
    }

    if ($swap['had_live']) {
        if (!is_dir($swap['old']) || !rename($swap['old'], $swap['live'])) {
            throw new OcmodDeployException('Unable to restore the previous cache directory.');
        }
    }
}

function ocmod_rollback_upload(array $state, $id)
{
    foreach (array_reverse($state['upload']) as $entry) {
        if ($entry['existed']) {
            ocmod_atomic_copy($entry['backup'], $entry['destination'], $id . '-rollback');
        } elseif (is_file($entry['destination']) && !is_link($entry['destination'])) {
            if (!unlink($entry['destination'])) {
                throw new OcmodDeployException('Unable to remove a newly installed file during rollback.');
            }
        }
    }

    foreach (array_reverse($state['created_directories']) as $directory) {
        if (is_dir($directory) && !is_link($directory)) {
            @rmdir($directory);
        }
    }
}

function ocmod_write_report($runBackup, array $package, array $uploadPlan, array $build, array $state, $root)
{
    $manifest = array(
        'completed_at_utc' => gmdate('c'),
        'root' => $root,
        'package_code' => $package['metadata']['code'],
        'package_version' => $package['metadata']['version'],
        'upload_plan' => $uploadPlan,
        'upload_files_changed' => count($state['upload']),
        'generated_modification_files' => count($build['files']),
        'operations' => $build['operations'],
        'matched_operations' => $build['matched_operations'],
        'ocmod_aborted' => $build['aborted']
    );
    $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false || file_put_contents($runBackup . '/manifest.json', $json . "\n", LOCK_EX) === false) {
        throw new OcmodDeployException('Unable to write the deployment manifest.');
    }

    if (file_put_contents($runBackup . '/ocmod-build.log', implode("\n", $build['log']) . "\n", LOCK_EX) === false) {
        throw new OcmodDeployException('Unable to write the OCMOD build log.');
    }
}

function ocmod_run(array $argv)
{
    $options = ocmod_parse_arguments($argv);
    $root = ocmod_real_directory($options['root'], '--root');
    $packagePath = ocmod_real_directory($options['package'], '--package');
    ocmod_validate_root($root);
    $paths = ocmod_load_config($root);
    $package = ocmod_load_package($packagePath);

    $backupBase = $options['backup-base'];
    if ($backupBase === null) {
        $backupBase = dirname($root) . '/.ocmod-backups/' . basename($root);
    }
    $backupBase = ocmod_resolve_future_path($backupBase, '--backup-base');

    if (ocmod_is_within($backupBase, $root) || ocmod_is_within($root, $backupBase)) {
        throw new OcmodDeployException('Backup base must be outside the OpenCart web root.');
    }

    $uploadPlan = ocmod_plan_upload($root, $package['files']);
    $db = ocmod_connect_database();
    $lockHandle = null;
    $runBackup = null;
    $id = gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(4));

    if (!$options['dry-run']) {
        ocmod_make_directory($backupBase, 0750);
        $lockHandle = fopen($backupBase . '/.deploy_ocmod.lock', 'c');

        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            throw new OcmodDeployException('Another OCMOD deployment is already running.');
        }
    }

    try {
        $rows = ocmod_fetch_modifications($db);
        $simulatedRows = ocmod_simulate_upsert($rows, $package);
        $preflightSources = ocmod_collect_xml_sources($root, $simulatedRows);
        $preflightBuilder = new OcmodBuilder($root, $package['files']);
        $preflight = $preflightBuilder->build($preflightSources);

        printf("Package: %s (%s)\n", $package['metadata']['code'], $package['metadata']['version']);
        printf("Upload: %d new, %d replace, %d unchanged\n", $uploadPlan['new'], $uploadPlan['replace'], $uploadPlan['unchanged']);
        printf("OCMOD: %d generated files, %d/%d matched operations%s\n",
            count($preflight['files']),
            $preflight['matched_operations'],
            $preflight['operations'],
            $preflight['aborted'] ? ', build aborted by XML policy' : ''
        );

        if ($options['dry-run']) {
            echo "Dry run complete; no files or database rows were changed.\n";
            return 0;
        }

        $runBackup = $backupBase . '/' . $id . '-' . preg_replace('/[^A-Za-z0-9_.-]+/', '-', $package['metadata']['code']);
        ocmod_make_directory($runBackup, 0750);
        $state = array(
            'upload' => array(),
            'created_directories' => array(),
            'database_snapshot' => null,
            'database_changed' => false,
            'transaction' => false,
            'modification_swap' => null,
            'twig_swap' => null,
            'temporary_paths' => array()
        );

        try {
            ocmod_install_upload($root, $package['files'], $runBackup, $id, $state);

            if (!$db->begin_transaction()) {
                throw new OcmodDeployException('Unable to begin the database transaction.');
            }
            $state['transaction'] = true;
            $state['database_snapshot'] = ocmod_upsert_modification($db, $package);
            $state['database_changed'] = true;

            $actualRows = ocmod_fetch_modifications($db);
            $actualSources = ocmod_collect_xml_sources($root, $actualRows);
            $actualBuilder = new OcmodBuilder($root, array());
            $build = $actualBuilder->build($actualSources);

            $modificationParent = dirname($paths['modification']);
            ocmod_make_directory($modificationParent, 0755);
            $modificationTemp = $modificationParent . '/.' . basename($paths['modification']) . '.ocmod-new-' . $id;
            $state['temporary_paths'][] = array($modificationTemp, $modificationParent);
            ocmod_write_cache_tree($modificationTemp, $build);
            $state['modification_swap'] = ocmod_swap_directory(
                $paths['modification'],
                $modificationTemp,
                $runBackup,
                'modification-cache-before',
                $id
            );

            $twigParent = dirname($paths['twig']);
            ocmod_make_directory($twigParent, 0755);
            $twigTemp = $twigParent . '/.' . basename($paths['twig']) . '.ocmod-new-' . $id;
            $state['temporary_paths'][] = array($twigTemp, $twigParent);
            ocmod_make_directory($twigTemp, 0755);
            $twigIndex = $twigTemp . '/index.html';
            if (file_put_contents($twigIndex, '<!doctype html><title></title>', LOCK_EX) === false) {
                throw new OcmodDeployException('Unable to prepare an empty Twig cache.');
            }
            @chmod($twigIndex, 0644);
            $state['twig_swap'] = ocmod_swap_directory(
                $paths['twig'],
                $twigTemp,
                $runBackup,
                'twig-cache-before',
                $id
            );

            if (!$db->commit()) {
                throw new OcmodDeployException('Unable to commit the database transaction.');
            }
            $state['transaction'] = false;
            ocmod_write_report($runBackup, $package, $uploadPlan, $build, $state, $root);

            if ($state['modification_swap']['had_live'] && is_dir($state['modification_swap']['old'])) {
                ocmod_remove_tree($state['modification_swap']['old'], $state['modification_swap']['parent']);
            }
            if ($state['twig_swap']['had_live'] && is_dir($state['twig_swap']['old'])) {
                ocmod_remove_tree($state['twig_swap']['old'], $state['twig_swap']['parent']);
            }

            printf("Deployment complete. Backup: %s\n", $runBackup);
        } catch (Throwable $failure) {
            $rollbackErrors = array();

            if ($state['transaction']) {
                if (!$db->rollback()) {
                    $rollbackErrors[] = 'transaction';
                }
                $state['transaction'] = false;
            }

            if ($state['database_changed']) {
                try {
                    ocmod_restore_database_snapshot($db, $state['database_snapshot']);
                } catch (Throwable $ignored) {
                    $rollbackErrors[] = 'database';
                }
            }

            if ($state['twig_swap']) {
                try {
                    ocmod_rollback_swap($state['twig_swap']);
                } catch (Throwable $ignored) {
                    $rollbackErrors[] = 'twig cache';
                }
            }

            if ($state['modification_swap']) {
                try {
                    ocmod_rollback_swap($state['modification_swap']);
                } catch (Throwable $ignored) {
                    $rollbackErrors[] = 'modification cache';
                }
            }

            try {
                ocmod_rollback_upload($state, $id);
            } catch (Throwable $ignored) {
                $rollbackErrors[] = 'upload files';
            }

            foreach ($state['temporary_paths'] as $temporary) {
                if (is_dir($temporary[0]) && !is_link($temporary[0])) {
                    try {
                        ocmod_remove_tree($temporary[0], $temporary[1]);
                    } catch (Throwable $ignored) {
                        $rollbackErrors[] = 'temporary cache';
                    }
                }
            }

            if ($rollbackErrors) {
                throw new OcmodDeployException(
                    'Deployment failed and rollback needs manual review for: ' . implode(', ', array_unique($rollbackErrors)) . '. Backup: ' . $runBackup,
                    0,
                    $failure
                );
            }

            throw new OcmodDeployException('Deployment failed; rollback completed. Backup: ' . $runBackup, 0, $failure);
        }
    } finally {
        if ($lockHandle) {
            @flock($lockHandle, LOCK_UN);
            @fclose($lockHandle);
        }
        $db->close();
    }
}

try {
    exit(ocmod_run($_SERVER['argv']));
} catch (OcmodDeployException $error) {
    fwrite(STDERR, 'ERROR: ' . $error->getMessage() . "\n");
    exit(1);
} catch (Throwable $error) {
    fwrite(STDERR, "ERROR: Unexpected deployment failure.\n");
    exit(1);
}
