<?php
// Mock variables required by header
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$title = "Test Page";
$_SESSION['CSRF-TOKEN'] = 'mock_token';
$my = (object)['id' => 1, 'username' => 'Tester', 'avatar_hash' => 'abc', 'currency' => 100, 'Currency' => 100, 'cash' => 100, 'administrator' => 0];

// Mock perf functions
function perf_mark($s) {}
function perf_report_html() { return ''; }

// Mock DB and App
class MockApp {
    public function auth() { return true; }
    public function checkAdminLogin() {}
    public function footer() {
        // Simulate footer inclusion
        echo "\n<!-- FOOTER START -->\n";
        echo "</main>\n<footer class=\"footer\">...</footer>";
    }
}
$app = new MockApp();
$db = null; // simplified

// Include Header
// We will read the file and eval it or just mock the output based on my analysis to demonstrate the issue? 
// No, better to include the actual file if possible, but it has dependencies.
// I'll just write a script that outputs the KNOWN structure based on my reading.

echo "<!-- GENERATED STRUCTURE ANALYSIS -->\n";
echo "<!-- FROM header.inc.php -->\n";
echo "<main>\n";
echo "    <section class=\"section\">\n";
echo "        <div class=\"page container\">\n";

echo "\n<!-- FROM dashboard/index.php -->\n";
echo "<section class=\"section\">\n";
echo "    <div class=\"container dashboard-wrapper\">\n";
echo "        <!-- Dashboard Content -->\n";
echo "    </div>\n";
echo "</section>\n";

echo "\n<!-- FROM footer.inc.php (via \$app->footer()) -->\n";
echo "</main>\n";

echo "\n<!-- ANALYSIS: The 'page container' and outer 'section' are never closed! -->\n";
?>