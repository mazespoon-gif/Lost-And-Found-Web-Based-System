<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$pageTitle = '500 - Server Error';
require_once __DIR__ . '/includes/header.php';

$pixelFont = [
    '5' => ['#####', '#....', '#....', '####.', '....#', '....#', '####.'],
    '0' => ['.###.', '#...#', '#...#', '#...#', '#...#', '#...#', '.###.'],
];
$glass = ['.#####.', '.#...#.', '.#...#.', '.#####.', '...#...', '....#..', '.....#.'];
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<style>
    .fancy-404-bg {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 50%, #dbeafe 100%);
    }
    .fancy-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: 0.55;
        animation: blobDrift 14s ease-in-out infinite alternate;
        pointer-events: none;
    }
    .fancy-blob-1 { width: 340px; height: 340px; background: #93c5fd; top: -80px; left: -80px; }
    .fancy-blob-2 { width: 280px; height: 280px; background: #c7d2fe; bottom: -60px; right: -60px; animation-delay: -7s; }
    .fancy-blob-3 { width: 200px; height: 200px; background: #bfdbfe; top: 40%; right: 12%; animation-delay: -3s; }
    @keyframes blobDrift {
        from { transform: translate(0, 0) scale(1); }
        to { transform: translate(40px, 28px) scale(1.12); }
    }
    .fancy-card {
        position: relative;
        z-index: 2;
        max-width: 32rem;
        width: 100%;
        text-align: center;
        padding: 3rem 2.25rem 2.5rem;
        border-radius: 1.75rem;
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 25px 60px rgba(30, 58, 138, 0.18);
        animation: cardRise 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    @keyframes cardRise {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .pixel-glass {
        display: grid;
        grid-template-columns: repeat(7, 9px);
        grid-auto-rows: 9px;
        gap: 2px;
        justify-content: center;
        margin-bottom: 1.75rem;
        animation: pixelBob 2.6s ease-in-out infinite;
    }
    @keyframes pixelBob {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .pixel-glass .px.on { background: #1e3a8a; }

    .pixel-404 {
        display: flex;
        gap: 22px;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    .pixel-digit {
        display: grid;
        grid-template-columns: repeat(5, 14px);
        grid-auto-rows: 14px;
        gap: 3px;
    }
    .px {
        width: 100%;
        height: 100%;
        border-radius: 2px;
        background: rgba(30, 58, 138, 0.07);
    }
    .px.on {
        background: #1e3a8a;
        box-shadow: inset 0 -4px 0 rgba(0, 0, 0, 0.18);
        opacity: 0;
        transform: scale(0);
        animation: pixelIn 0.28s steps(3, end) forwards;
    }
    @keyframes pixelIn {
        to { opacity: 1; transform: scale(1); }
    }

    .pixel-title {
        font-family: 'Press Start 2P', monospace;
        font-size: 0.8rem;
        color: #1e3a8a;
        letter-spacing: 0.06em;
        line-height: 1.9;
        margin-bottom: 0.9rem;
    }
    .pixel-sub {
        font-family: 'Press Start 2P', monospace;
        font-size: 0.5rem;
        color: #64748b;
        letter-spacing: 0.08em;
        line-height: 2;
        margin-bottom: 2rem;
    }
    .pixel-cursor {
        display: inline-block;
        width: 0.55em;
        height: 0.9em;
        background: #1e3a8a;
        vertical-align: -0.1em;
        margin-left: 2px;
        animation: blink 0.9s steps(1) infinite;
    }
    @keyframes blink {
        50% { opacity: 0; }
    }

    .pixel-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        font-family: 'Press Start 2P', monospace;
        font-size: 0.5rem;
        padding: 0.95rem 1.4rem;
        border: 0;
        border-radius: 0;
        transition: transform 0.12s steps(2, end), box-shadow 0.12s steps(2, end);
    }
    .pixel-btn svg { width: 13px; height: 13px; }
    .pixel-btn-primary {
        color: #fff;
        background: #1e3a8a;
        box-shadow: 5px 5px 0 #93c5fd;
    }
    .pixel-btn-primary:hover {
        transform: translate(2px, 2px);
        box-shadow: 3px 3px 0 #93c5fd;
    }
    .pixel-btn-ghost {
        color: #1e3a8a;
        background: #fff;
        box-shadow: 5px 5px 0 #c7d2fe;
    }
    .pixel-btn-ghost:hover {
        transform: translate(2px, 2px);
        box-shadow: 3px 3px 0 #c7d2fe;
    }

    .pixel-hint {
        margin-top: 2rem;
        font-family: 'Press Start 2P', monospace;
        font-size: 0.42rem;
        color: #94a3b8;
        letter-spacing: 0.1em;
    }
    .pixel-hint strong { color: #1e3a8a; font-weight: 400; }
</style>

<div class="fancy-404-bg">
    <div class="fancy-blob fancy-blob-1"></div>
    <div class="fancy-blob fancy-blob-2"></div>
    <div class="fancy-blob fancy-blob-3"></div>

    <div class="fancy-card">
        <div class="pixel-glass">
            <?php foreach ($glass as $row): foreach (str_split($row) as $c): ?>
                <span class="px <?= $c === '#' ? 'on' : '' ?>"></span>
            <?php endforeach; endforeach; ?>
        </div>

        <div class="pixel-404">
            <?php foreach (str_split('500') as $d): ?>
                <div class="pixel-digit">
                    <?php foreach ($pixelFont[$d] as $row): foreach (str_split($row) as $c): ?>
                        <span class="px <?= $c === '#' ? 'on' : '' ?>"></span>
                    <?php endforeach; endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pixel-title">SERVER ERROR</div>
        <div class="pixel-sub">// ERROR: THE ITEM WAS FOUND BUT OUR SERVER DROPPED IT<span class="pixel-cursor"></span></div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= SITE_URL ?>/home" class="pixel-btn pixel-btn-primary w-full sm:w-auto">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                GO HOME
            </a>
            <a href="<?= SITE_URL ?>/lost_and_found" class="pixel-btn pixel-btn-ghost w-full sm:w-auto">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                BROWSE ITEMS
            </a>
        </div>

        <div class="pixel-hint">DON'T PANIC &mdash; <strong>LOOKFORIT</strong> &bull; GROUP 3 GENEROSITY</div>
    </div>
</div>

<script>
(function () {
    var on = document.querySelectorAll('.pixel-404 .px.on');
    on.forEach(function (p, i) {
        p.style.animationDelay = (600 + i * 22) + 'ms';
    });
    var glass = document.querySelectorAll('.pixel-glass .px.on');
    glass.forEach(function (p, i) {
        p.style.animationDelay = (150 + i * 30) + 'ms';
    });
})();
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
</body>
</html>
