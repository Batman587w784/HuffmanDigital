<?php
/**
 * Huffman Digital — client site directory (password-gated).
 * Scans public_html for client folders; reads site.html <title> for display names.
 */

define('PASSWORD', 'CHANGE_ME_BEFORE_GO_LIVE');

$SCAN_DIR = realpath(__DIR__ . '/../../');
$HIDE = [
    'digital',
    '_template',
    '.git',
    '.well-known',
    'cgi-bin',
];

session_start();

function hd_slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function hd_get_title_from_file($path) {
    if (!is_readable($path)) {
        return null;
    }
    $html = file_get_contents($path);
    if ($html === false) {
        return null;
    }
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
        $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
        return $title !== '' ? $title : null;
    }
    return null;
}

function hd_collect_sites($scanDir, $hide) {
    $sites = [];
    if (!$scanDir || !is_dir($scanDir)) {
        return $sites;
    }

    $entries = scandir($scanDir);
    if ($entries === false) {
        return $sites;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $scanDir . DIRECTORY_SEPARATOR . $entry;
        if (!is_dir($full)) {
            continue;
        }
        if (in_array($entry, $hide, true)) {
            continue;
        }

        $title = hd_get_title_from_file($full . '/site.html');
        if ($title === null) {
            $title = hd_get_title_from_file($full . '/index.html');
        }
        if ($title === null) {
            $title = ucwords(str_replace('-', ' ', $entry));
        }

        $sites[] = [
            'slug' => $entry,
            'name' => $title,
            'path' => '/' . rawurlencode($entry) . '/',
        ];
    }

    usort($sites, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return $sites;
}

$error = '';
if (isset($_POST['password'])) {
    if ($_POST['password'] === PASSWORD) {
        $_SESSION['hd_auth'] = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    $error = 'Incorrect password.';
}

$authenticated = !empty($_SESSION['hd_auth']);
$sites = $authenticated ? hd_collect_sites($SCAN_DIR, $HIDE) : [];
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Huffman Digital — Client Sites</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
      background: #0d0d0d;
      color: #f5f5f5;
      min-height: 100vh;
      line-height: 1.5;
    }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 32px 20px 48px; }
    h1 { font-size: 1.5rem; font-weight: 600; margin-bottom: 8px; }
    .sub { color: #888; font-size: 0.9rem; margin-bottom: 24px; }
    .login {
      max-width: 360px;
      background: #161616;
      border: 1px solid #2a2a2a;
      border-radius: 8px;
      padding: 24px;
    }
    label { display: block; font-size: 0.85rem; color: #aaa; margin-bottom: 8px; }
    input[type="password"], input[type="search"] {
      width: 100%;
      padding: 10px 12px;
      border-radius: 6px;
      border: 1px solid #333;
      background: #111;
      color: #fff;
      font-size: 0.95rem;
    }
    button, .btn {
      display: inline-block;
      margin-top: 12px;
      padding: 10px 14px;
      border: 0;
      border-radius: 6px;
      background: #fff;
      color: #111;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .error { color: #ff6b6b; font-size: 0.85rem; margin-top: 10px; }
    .toolbar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .toolbar input[type="search"] { max-width: 320px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 16px;
    }
    .card {
      background: #161616;
      border: 1px solid #2a2a2a;
      border-radius: 8px;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .card h2 { font-size: 1rem; font-weight: 600; }
    .card .slug { color: #777; font-size: 0.8rem; word-break: break-all; }
    .card .url { color: #9ecbff; font-size: 0.82rem; word-break: break-all; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .actions a, .actions button {
      margin-top: 0;
      background: #222;
      color: #fff;
      border: 1px solid #333;
      font-weight: 500;
    }
    .empty { color: #888; padding: 24px 0; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Huffman Digital — Client Sites</h1>
    <p class="sub">Internal directory. Not indexed.</p>

<?php if (!$authenticated): ?>
    <form class="login" method="post">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
      <button type="submit">Enter</button>
      <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
    </form>
<?php else: ?>
    <div class="toolbar">
      <input type="search" id="search" placeholder="Search clients..." aria-label="Search clients">
      <span class="sub" style="margin:0;"><?php echo count($sites); ?> site(s)</span>
    </div>
    <div class="grid" id="grid">
<?php foreach ($sites as $site):
    $liveUrl = $baseUrl . $site['path'];
?>
      <article class="card" data-name="<?php echo htmlspecialchars(strtolower($site['name']), ENT_QUOTES, 'UTF-8'); ?>">
        <h2><?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="slug">/<?php echo htmlspecialchars($site['slug'], ENT_QUOTES, 'UTF-8'); ?>/</div>
        <a class="url" href="<?php echo htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8'); ?></a>
        <div class="actions">
          <a href="<?php echo htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Open</a>
          <button type="button" class="copy" data-url="<?php echo htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8'); ?>">Copy URL</button>
        </div>
      </article>
<?php endforeach; ?>
    </div>
    <?php if (count($sites) === 0): ?><p class="empty">No client folders found.</p><?php endif; ?>
    <script>
      const search = document.getElementById('search');
      const cards = Array.from(document.querySelectorAll('.card'));
      search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        cards.forEach(card => {
          card.style.display = !q || card.dataset.name.includes(q) ? '' : 'none';
        });
      });
      document.querySelectorAll('.copy').forEach(btn => {
        btn.addEventListener('click', async () => {
          const url = btn.dataset.url;
          try {
            await navigator.clipboard.writeText(url);
            btn.textContent = 'Copied';
            setTimeout(() => { btn.textContent = 'Copy URL'; }, 1200);
          } catch (e) {
            prompt('Copy URL:', url);
          }
        });
      });
    </script>
<?php endif; ?>
  </div>
</body>
</html>
