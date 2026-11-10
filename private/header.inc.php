<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Undefined'; ?> | Bricktaria</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msvalidate.01" content="8005E515BA9B0FDC650DAD07F6B49654" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <?php if (isset($extra_head_content)) echo $extra_head_content; ?>
    
    <!-- ✅ SECURITY: Load FontAwesome over HTTPS with integrity hash (SRI) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- ✅ SECURITY: Use the securely generated CSRF token -->
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['CSRF-TOKEN'], ENT_QUOTES, 'UTF-8'); ?>">
    
    <meta name="keywords" content="Bricktaria, Bricktaria site, Bricktaria game">
    <link rel="icon" href="/favicon.ico">
    
    <?php
    // ✅ INTEGRATED & UPDATED: CSS Dependencies with HTTPS and SRI where possible
    $css_map = [
        'animate' => ['/assets/css/animate.css', 'https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css'],
        'hover' => ['/assets/css/hover.css', 'https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css'],
        'bulma-tooltip' => ['/assets/css/bulma-tooltip.css', 'https://cdn.jsdelivr.net/npm/bulma-tooltip@3.0.2/dist/css/bulma-tooltip.min.css'],

        'app23' => ['/assets/css/app23.css', 'https://your-secure-cdn.com/app23.css'], // Replace with your actual CSS CDN
        'bulma' => ['/assets/css/bulma.min.css', 'https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css'],
        'bulma-extensions' => ['/assets/css/bulma-extensions.min.css', 'https://cdn.jsdelivr.net/npm/bulma-extensions@6.2.7/dist/css/bulma-extensions.min.css'],
        'mdi' => ['/assets/css/materialdesignicons.min.css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.0.96/css/materialdesignicons.min.css'],
        'fontawesome' => ['/assets/css/fontawesome-all.min.css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'],
        // Global Style - Loads last to override defaults
        'style' => ['/assets/css/style.css', '']
    ];
    
    foreach ($css_map as $k => $arr) {
        list($local, $cdn) = $arr;
        // Always use HTTPS for CDN fallback
        $fallbackUrl = $cdn;
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $local;
        
        if (file_exists($localPath)) {
            echo '<link rel="stylesheet" href="' . $local . '?v=' . filemtime($localPath) . '" />' . "\n";
        } elseif (!empty($cdn)) {
            // Fallback to HTTPS CDN
            echo '<link rel="stylesheet" href="' . htmlspecialchars($fallbackUrl, ENT_QUOTES, 'UTF-8') . '" />' . "\n";
        }
    }
    ?>
    
    <style>
        /* Thin, unobtrusive scrollbar for desktop browsers */
        html { scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.25) transparent; }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.22);
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.02);
        }
        ::-webkit-scrollbar-thumb:hover { background-color: rgba(0,0,0,0.34); }

        /* Prevent horizontal overflow caused by large elements */
        html, body { overflow-x: hidden; }

        /* Reduce overscroll glow on some browsers */
        body { -webkit-overflow-scrolling: touch; }
    </style>

    <?php perf_mark('before_scripts'); ?>
    
    <?php
    // ✅ INTEGRATED & UPDATED: JavaScript Dependencies from your code snippet
    // UPDATED CDN URLs to working, current versions over HTTPS
    $js_map = [
        // Core Libraries
        'jquery' => ['/assets/vendor/jquery.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'],
        'jquery_cookie' => ['/assets/vendor/jquery.cookie.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js'],
        
        // Vue.js Framework (Vue 2, as required by Buefy 0.9.4)
        'vue' => ['/assets/vendor/vue.min.js', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js'],
        
        // 3D Rendering Dependencies (REQUIRED FOR AVATARS)
        'three' => ['/assets/vendor/three.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js'],
        'gltf_loader' => ['/assets/vendor/GLTFLoader.js', 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js'],
        'obj_loader' => ['/assets/vendor/OBJLoader.js', 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js'],
        
        // Security
        'hcaptcha' => ['/assets/vendor/hcaptcha-api.js', 'https://js.hcaptcha.com/1/api.js']
    ];
    
    foreach ($js_map as $k => $arr) {
        list($local, $cdn) = $arr;
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $local;
        
        $asyncDefer = ($k === 'hcaptcha') ? ' async defer' : '';
        
        if (file_exists($localPath)) {
            echo '<script src="' . $local . '"' . $asyncDefer . '></script>' . "\n";
        } else {
            // Fallback to HTTPS CDN
            echo '<script src="' . htmlspecialchars($cdn, ENT_QUOTES, 'UTF-8') . '"' . $asyncDefer . '></script>' . "\n";
        }
    }
    
    // Load Buefy (Vue 2 compatible) - Integrated from your snippet
    echo '<script>' . "\n";
    echo '(function(){' . "\n";
    echo '    try {' . "\n";
    echo '        var want = "https://unpkg.com/buefy@0.9.4/dist/buefy.min.js";' . "\n";
    echo '        if (window.Vue && window.Vue.version && window.Vue.version.split(".")[0] === "2") {' . "\n";
    echo '            if (!(window.Buefy || window.buefy)) {' . "\n";
    echo '                var s = document.createElement("script"); s.src = want; document.head.appendChild(s);' . "\n";
    echo '                console.info("Loaded Buefy v0.9.4 (Vue 2 build) for compatibility.");' . "\n";
    echo '            } else {' . "\n";
    echo '                console.info("Buefy already loaded.");' . "\n";
    echo '            }' . "\n";
    echo '        } else {' . "\n";
    echo '            console.warn("Vue major version not detected as 2. Buefy (Vue2) may be incompatible. Detected Vue:", window.Vue && window.Vue.version);' . "\n";
    echo '        }' . "\n";
    echo '    } catch(e) { console.warn("Buefy loader error", e); }' . "\n";
    echo '})();' . "\n";
    echo '</script>' . "\n";
    ?>
    
    <script src="/assets/js/main.js?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>
    <script src="/assets/js/theme-toggle.js?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>
    <script src="/assets/js/forum.js?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>
    <script src="/assets/js/store.js?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>

    <?php if(isset($app) && $app->auth()) { ?>
        <script src="/assets/js/chat.js?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>
    <?php } ?>

    <?php if(isset($title) && isset($asset)) { ?>
        <script src="<?= htmlspecialchars($asset, ENT_QUOTES, 'UTF-8'); ?>?r=<?php echo bin2hex(random_bytes(16)); ?>" type="text/javascript"></script>
    <?php } ?>

    <?php perf_mark('after_scripts'); ?>
    
    <?php if ($__perf_enabled): ?>
    <script>
        (function(){
            try {
                console.groupCollapsed('Bricktaria — client-side perf (debug)');
                // Basic timings
                window.addEventListener('load', function() {
                    setTimeout(function(){
                        try {
                            var t = window.performance && window.performance.timing;
                            if (t) {
                                var pageLoad = (t.loadEventEnd || Date.now()) - t.navigationStart;
                                console.log('pageLoad (ms):', pageLoad);
                            }

                            if (performance.getEntriesByType) {
                                var resources = performance.getEntriesByType('resource');
                                console.log('resource count:', resources.length);
                                resources.slice(0,50).forEach(function(r){
                                    console.log(r.initiatorType, r.name, Math.round(r.duration)+'ms');
                                });
                            }

                            // Show server-side PERF markers if present in page source
                            var html = document.documentElement.innerHTML;
                            var match = html.match(/<!-- Server-Side Performance Marks:([\s\S]*?)-->/m);
                            if (match) console.log('server perf report:\n' + match[1].trim());
                        } catch(e) { console.warn('perf debug error', e); }
                        console.groupEnd();
                    }, 50);
                });
            } catch(e) { console.warn('perf init error', e); }
        })();
    </script>
    <?php endif; ?>

</head>
<body>
<?php
// Print a small server-side perf report in HTML source when enabled
perf_mark('header_output_done');
echo perf_report_html();
?>

<?php 
// This function call is assumed to be defined in your included files
if (function_exists('checkAdminLogin') || (isset($app) && method_exists($app, 'checkAdminLogin'))) {
    $app->checkAdminLogin();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/components/navbar.inc.php');
?>

<main>
    <section class="section">
        <div class="page container">
            <?php if(isset($SiteSettings) && $SiteSettings->BannerAlertMessage !== NULL) : ?>
                <div class="has-announcement">
                    <div class="container">
                        <div class="box has-background-info has-text-centered">
                            <p><b><?= htmlspecialchars($SiteSettings->BannerAlertMessage, ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </div>
                    </div>
                </div>
                <div class="push-5"></div>
            <?php endif; ?>

            <?php if(isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] == "false") : ?>
                <div id="cookie_banner" class="has-announcement">
                    <div class="container">
                        <div class="box has-background-info has-text-centered">
                            <p><b>Bricktaria uses cookies to provide you a better service and stability. By clicking 'I acknowledge' you agree to using cookies in our website.</b></p>
                            <div class="buttons are-normal is-left">
                                <a class="button is-link" id="accept_cookie_banner"><b>I acknowledge</b></a>
                                <a class="button is-info is-light" href="/legal/privacy/">Privacy Policy</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="push-5"></div>
            <?php endif; ?>

            <?php if(isset($app) && $app->auth()) { ?>
                <?php if(isset($my->verified) && $my->verified == 0) { ?>
                    <div class="has-announcement">
                        <div class="container">
                            <div class="box has-background-info has-text-centered">
                                <p><b>Looks like you have not verified your account yet on this website! Please click the button below to complete verification.</b></p>
                                <a class="button is-link" href="/verify">Verify now</a>
                            </div>
                        </div>
                    </div>
                    <div class="push-5"></div>
                <?php } ?>
            <?php } ?>