<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

<div class="container">
    <h1 class="title">JavaScript Compatibility Test</h1>
    
    <div class="box">
        <h2 class="title is-4">jQuery Test</h2>
        <div id="jquery-test">Testing jQuery...</div>
    </div>
    
    <div class="box">
        <h2 class="title is-4">jQuery Cookie Test</h2>
        <div id="cookie-test">Testing jQuery Cookie...</div>
    </div>
    
    <div class="box">
        <h2 class="title is-4">Vue.js Test</h2>
        <div id="vue-test">Testing Vue.js...</div>
    </div>
    
    <div class="box">
        <h2 class="title is-4">Buefy Test</h2>
        <div id="buefy-test">Testing Buefy...</div>
    </div>
    
    <div class="box">
        <h2 class="title is-4">Color Function Test</h2>
        <button class="button is-primary" onclick="testColorFunction()">Test Color Function</button>
        <div id="color-test-result"></div>
    </div>
</div>

<script>
// Test jQuery
$(document).ready(function() {
    $('#jquery-test').html('✅ jQuery is working! Version: ' + $.fn.jquery);
    
    // Test jQuery Cookie
    if (typeof $.cookie === 'function') {
        $('#cookie-test').html('✅ jQuery Cookie is working!');
        $.cookie('test_cookie', 'test_value', { expires: 1 });
        const testValue = $.cookie('test_cookie');
        if (testValue === 'test_value') {
            $('#cookie-test').html('✅ jQuery Cookie is working! Test value: ' + testValue);
        } else {
            $('#cookie-test').html('❌ jQuery Cookie test failed');
        }
    } else {
        $('#cookie-test').html('❌ jQuery Cookie is not available');
    }
    
    // Test Vue.js
    if (typeof Vue !== 'undefined') {
        $('#vue-test').html('✅ Vue.js is working! Version: ' + Vue.version);
    } else {
        $('#vue-test').html('❌ Vue.js is not available');
    }
    
    // Test Buefy
    if (typeof Buefy !== 'undefined') {
        $('#buefy-test').html('✅ Buefy is working!');
    } else {
        $('#buefy-test').html('❌ Buefy is not available');
    }
});

// Test color function
function testColorFunction() {
    const resultDiv = document.getElementById('color-test-result');
    
    if (typeof window.color === 'function') {
        resultDiv.innerHTML = '✅ Color function is available';
        
        // Test the AJAX call
        $.post('/api/rendering/change-color.php', {
            indicator: 1,
            part: 'head'
        }).done(function(data) {
            resultDiv.innerHTML += '<br>✅ AJAX call successful: ' + data;
        }).fail(function(xhr, status, error) {
            resultDiv.innerHTML += '<br>❌ AJAX call failed: ' + error;
        });
    } else {
        resultDiv.innerHTML = '❌ Color function is not available';
    }
}

// Log all loaded scripts
console.log('=== Loaded Scripts ===');
document.querySelectorAll('script').forEach(script => {
    if (script.src) {
        console.log('Script:', script.src);
    }
});

// Check for conflicts
console.log('=== Global Variables ===');
console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'Not loaded');
console.log('Vue version:', typeof Vue !== 'undefined' ? Vue.version : 'Not loaded');
console.log('Buefy:', typeof Buefy !== 'undefined' ? 'Loaded' : 'Not loaded');
console.log('Color function:', typeof window.color === 'function' ? 'Available' : 'Not available');
</script>

<?php
$app->footer();
?> 