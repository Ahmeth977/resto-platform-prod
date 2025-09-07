<?php
// test_server.php
echo "<h1>Configuration du serveur</h1>";

echo "<h2>PHP</h2>";
echo "<p>Version: " . phpversion() . "</p>";
echo "<p>Extensions chargées:</p>";
echo "<ul>";
foreach (get_loaded_extensions() as $ext) {
    echo "<li>" . $ext . "</li>";
}
echo "</ul>";

echo "<h2>Serveur Web</h2>";
echo "<p>" . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<h2>Variables importantes</h2>";
echo "<ul>";
echo "<li>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li>SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "</li>";
echo "</ul>";