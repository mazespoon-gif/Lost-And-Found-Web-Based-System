<style>
.snowflake-fixed { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; overflow: hidden; }
.snowflake { position: absolute; top: -20px; color: #fff; font-size: 1rem; animation: fall linear forwards; pointer-events: none; }
@keyframes fall { 0% { transform: translateY(0) rotate(0deg); opacity: 0.9; } 100% { transform: translateY(105vh) rotate(360deg); opacity: 0.1; } }
</style>
<div id="snowflakes" class="snowflake-fixed"></div>
<footer class="bg-blue-900 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <img src="image/high.webp" alt="LookForit" class="w-9 h-9">
                    <span class="text-xl font-bold">LookForit</span>
                </div>
                <p class="text-blue-200 text-sm leading-relaxed">Group 3 Generosity. LookForit helps the TCC community reunite with their belongings.</p>
                <div class="flex gap-4 mt-4">
                    <a href="https://www.facebook.com/TCCofficialfbpage" target="_blank" class="w-9 h-9 bg-blue-800 rounded-full flex items-center justify-center hover:bg-blue-700 transition"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                </div>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-blue-200">
                    <li><a href="<?= SITE_URL ?>/home" class="hover:text-white transition">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/lost_and_found" class="hover:text-white transition">Lost & Found</a></li>
                    <li><a href="<?= SITE_URL ?>/report_item" class="hover:text-white transition">Report Item</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-blue-200">
                    <li>TCC Talisay City Collage</li>
                    <li><a href="https://www.google.com/maps/place/Talisay+City+College/@10.2596608,123.8269952,14z/data=!4m6!3m5!1s0x33a99cddb41735bb:0xa92f8dac8c6be822!8m2!3d10.2429329!4d123.848309!16s%2Fg%2F1tyzwjdc?entry=ttu&g_ep=EgoyMDI2MDkwOS4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="hover:text-white transition">6RVX+58F, V.H.Garces St, Poblacion, Talisay, 6045 Cebu</a></li>
                    <li>Talisay City Collage</li>
                    <li class="pt-2"><a href="mailto:talisaycitycollege2004@gmail.com" class="hover:text-white transition">talisaycitycollege2004@gmail.com</a></li>
                    <li>(02) 8123-4567</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="border-t border-blue-800 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-blue-300">
            &copy; <?= date('Y') ?> LookForit. All rights reserved. | Group 3 Generosity.
        </div>
    </div>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=9"></script>
<script>
(function() {
    var container = document.getElementById('snowflakes');
    var symbols = ['❄', '❅', '❆', '✦', '•'];
    function createSnowflake() {
        var s = document.createElement('span');
        s.className = 'snowflake';
        s.textContent = symbols[Math.floor(Math.random() * symbols.length)];
        s.style.left = Math.random() * 100 + '%';
        s.style.fontSize = (Math.random() * 12 + 8) + 'px';
        s.style.animationDuration = (Math.random() * 5 + 5) + 's';
        s.style.opacity = Math.random() * 0.5 + 0.4;
        container.appendChild(s);
        setTimeout(function() { s.remove(); }, 10000);
    }
    setInterval(createSnowflake, 300);
})();
</script>
</body>
</html>
