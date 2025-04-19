<?php
// Simple script to execute all PHPUnit tests

echo "Running all PHPUnit tests...\n";
passthru('vendor/bin/phpunit', $exitCode);
exit($exitCode);
