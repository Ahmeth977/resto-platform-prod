<?php
// test_functions.php
require_once __DIR__.'/includes/config.php';

echo "<h1>Test des fonctions</h1>";

echo "<h2>Test de sanitize()</h2>";
$tests = [
    'string' => ['input' => 'Test<script>alert(1)</script>', 'expected' => 'Test&lt;script&gt;alert(1)&lt;/script&gt;'],
    'email' => ['input' => '  TEST@Example.COM  ', 'expected' => DEV_MODE ? 'TEST@Example.COM' : 'test@example.com'],
    'int' => ['input' => '123abc', 'expected' => 123],
    'float' => ['input' => '45.67abc', 'expected' => 45.67]
];

foreach ($tests as $type => $test) {
    $result = sanitize($test['input'], $type);
    $passed = $result === $test['expected'] ? '✅' : '❌';
    echo "<p><strong>$type:</strong> $passed ";
    echo "Input: '" . htmlspecialchars($test['input']) . "' → ";
    echo "Output: '" . htmlspecialchars($result) . "' ";
    echo "Expected: '" . htmlspecialchars($test['expected']) . "'</p>";
}

echo "<h2>Test de isAdmin()</h2>";
echo "<p>DEV_MODE: " . (DEV_MODE ? 'ON' : 'OFF') . "</p>";
echo "<p>isAdmin(): " . (isAdmin() ? '✅ Oui' : '❌ Non') . "</p>";

echo "<h2>Test de session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";