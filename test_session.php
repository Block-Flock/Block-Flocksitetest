<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>Session Test</h1>";

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Authentication Status</h2>";
echo "AUTH: " . ($AUTH ? 'true' : 'false') . "<br>";
echo "Session ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'Not set') . "<br>";

echo "<h2>Test AJAX Call</h2>";
echo "<button onclick='testAjax()'>Test AJAX Call</button>";
echo "<div id='ajax-result'></div>";

?>

<script>
function testAjax() {
    $.post('/api/rendering/change-color.php', {
        indicator: 1,
        part: 'head'
    }).done(function(data) {
        $('#ajax-result').html('✅ Success: ' + data);
    }).fail(function(xhr, status, error) {
        $('#ajax-result').html('❌ Failed: ' + error + '<br>Response: ' + xhr.responseText);
    });
}
</script> 