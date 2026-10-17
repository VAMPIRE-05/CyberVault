
<?php
$result = "";
$error = "";
$tool = $_POST["tool"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($tool === "caesar") {
        $text = $_POST["text"] ?? "";
        $shift = (int)($_POST["shift"] ?? 3);
        $mode = $_POST["mode"] ?? "encrypt";

        if ($mode === "decrypt") {
            $shift = -$shift;
        }

        $result = "";
        foreach (str_split($text) as $char) {
            if (ctype_alpha($char)) {
                $base = ctype_upper($char) ? 65 : 97;
                $result .= chr((ord($char) - $base + $shift + 2600) % 26 + $base);
            } else {
                $result .= $char;
            }
        }
    }

    if ($tool === "phishing") {
        $url = trim($_POST["url"] ?? "");
        if (!filter_var($url, FILTER_VALIDATE_URL) ||
            !in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ""), ["http", "https"])) {
            $error = "Please enter a valid HTTP or HTTPS URL.";
        } else {
            $host = strtolower(parse_url($url, PHP_URL_HOST) ?? "");
            $flags = [];
            if (filter_var($host, FILTER_VALIDATE_IP)) $flags[] = "Uses an IP address";
            if (preg_match('/@|xn--/', $url)) $flags[] = "Contains a suspicious URL pattern";
            if (preg_match('/(login|verify|secure|account|update|password)/i', $url)) $flags[] = "Contains a sensitive keyword";
            if (substr_count($host, ".") >= 3) $flags[] = "Has multiple subdomains";
            if (parse_url($url, PHP_URL_SCHEME) === "http") $flags[] = "Uses unencrypted HTTP";

            $result = $flags
                ? "Warning signs found:\n• " . implode("\n• ", $flags)
                : "No obvious warning signs found by these basic checks.";
            $result .= "\n\nThis is an educational heuristic, not a definitive safety verdict. Do not enter credentials on an unfamiliar site.";
        }
    }

    if ($tool === "hash") {
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            if ($_FILES["file"]["size"] > 5 * 1024 * 1024) {
                $error = "Maximum file size is 5 MB.";
            } else {
                $path = $_FILES["file"]["tmp_name"];
                $result = "SHA-256: " . hash_file("sha256", $path)
                    . "\nSHA-1: " . hash_file("sha1", $path)
                    . "\nMD5: " . hash_file("md5", $path);
            }
        } else {
            $error = "Please select a file to generate its hashes.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberVault | Security Toolkit</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><span>⬡</span> CyberVault</a>
    <span class="tag">CYBERSECURITY TOOLKIT</span>
</header>

<main class="container">
    <section class="hero">
        <p class="eyebrow">YOUR DIGITAL SECURITY LAB</p>
        <h1>Explore. Protect.<br><span>Secure.</span></h1>
        <p class="intro">A simple educational toolkit for learning essential cybersecurity and cryptography concepts.</p>
    </section>

    <?php if ($error !== ""): ?>
        <div class="notice error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="tools">
        <article class="panel">
            <div class="number">01 / PASSWORD SECURITY</div>
            <h2>Password Strength Checker</h2>
            <p>Evaluate a password's basic complexity on this device.</p>
            <label for="password">Enter a sample password</label>
            <input id="password" type="password" placeholder="Type a sample password" autocomplete="off">
            <div class="meter"><div id="meterFill"></div></div>
            <p id="strengthText" class="muted">Your password score will appear here.</p>
            <button type="button" id="togglePassword" class="secondary">Show / Hide</button>
            <button type="button" id="generatePassword">Generate sample</button>
            <p class="small">Educational estimate only. Don't enter a real password.</p>
        </article>

        <article class="panel">
            <div class="number">02 / URL SAFETY</div>
            <h2>Phishing URL Detector</h2>
            <p>Check a URL for a few common warning signs.</p>
            <form method="post">
                <input type="hidden" name="tool" value="phishing">
                <label for="url">Website URL</label>
                <input id="url" name="url" type="url" placeholder="https://example.com" required>
                <button type="submit">Analyze URL</button>
            </form>
            <p class="small">Does not visit the URL or check live threat databases.</p>
        </article>

        <article class="panel">
            <div class="number">03 / FILE INTEGRITY</div>
            <h2>File Hash Generator</h2>
            <p>Generate a digital fingerprint of a file.</p>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="tool" value="hash">
                <label for="file">Choose a file (max 5 MB)</label>
                <input id="file" name="file" type="file" required>
                <button type="submit">Generate Hashes</button>
            </form>
            <p class="small">SHA-256 is preferred for modern integrity checks; MD5 and SHA-1 are included for learning.</p>
        </article>

        <article class="panel">
            <div class="number">04 / CRYPTOGRAPHY</div>
            <h2>Caesar Cipher</h2>
            <p>Encrypt or decrypt text using a shift cipher.</p>
            <form method="post">
                <input type="hidden" name="tool" value="caesar">
                <label for="text">Message</label>
                <textarea id="text" name="text" rows="3" placeholder="Enter your message" required><?= htmlspecialchars($_POST["text"] ?? "") ?></textarea>
                <div class="field-row">
                    <div>
                        <label for="shift">Shift (0–25)</label>
                        <input id="shift" name="shift" type="number" min="0" max="25" value="<?= htmlspecialchars($_POST["shift"] ?? "3") ?>">
                    </div>
                    <div>
                        <label for="mode">Operation</label>
                        <select id="mode" name="mode">
                            <option value="encrypt">Encrypt</option>
                            <option value="decrypt" <?= ($_POST["mode"] ?? "") === "decrypt" ? "selected" : "" ?>>Decrypt</option>
                        </select>
                    </div>
                </div>
                <button type="submit">Run Cipher</button>
            </form>
        </article>
    </section>

    <?php if ($result !== ""): ?>
        <section class="result-panel">
            <h2>Analysis Result</h2>
            <pre><?= htmlspecialchars($result) ?></pre>
            <button type="button" id="copyResult" class="secondary">Copy Result</button>
        </section>
    <?php endif; ?>

    <footer>
        <p>CyberVault · Student Cybersecurity Project</p>
        <p class="small">For education only. This toolkit does not guarantee security or detect every threat.</p>
    </footer>
</main>
</body>
</html>
